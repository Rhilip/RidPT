<?php

namespace App\Entity;

use App\Entity\User\AbstractUser;

class User extends AbstractUser
{
    protected $lang;
    protected $passkey;

    protected $uploadpos;
    protected $downloadpos;

    protected $uploaded;
    protected $downloaded;
    protected $true_uploaded;
    protected $true_downloaded;
    protected $seedtime;
    protected $leechtime;

    protected $bonus_seeding;
    protected $bonus_other;

    protected $invites;

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getPasskey(): string
    {
        return $this->passkey;
    }

    public function getUploadpos(): bool
    {
        return (bool)$this->uploadpos;
    }

    public function getDownloadpos(): bool
    {
        return (bool)$this->downloadpos;
    }

    public function getUploaded(): int
    {
        return $this->uploaded;
    }

    public function getDownloaded(): int
    {
        return $this->downloaded;
    }

    private function getRealTransfer(): array
    {
        return $this->getCacheValue('true_transfer', function () {
            return app()->pdo->createCommand('SELECT SUM(`true_uploaded`) as `uploaded`, SUM(`true_downloaded`) as `download` FROM `snatched` WHERE `user_id` = :uid')->bindParams([
                    "uid" => $this->id
                ])->queryOne() ?? ['uploaded' => 0, 'download' => 0];
        });
    }

    public function getRealUploaded(): int
    {
        return (int)$this->getRealTransfer()['uploaded'];
    }

    public function getRealDownloaded(): int
    {
        return (int)$this->getRealTransfer()['download'];
    }

    public function getRatio()
    {
        $uploaded = $this->getUploaded();
        $download = $this->getDownloaded();
        if ($download == 0 && $uploaded == 0) {
            return '---';
        } elseif ($download == 0) {
            return 'Inf.';
        } else {
            return $uploaded / $download;
        }
    }

    public function getRealRatio()
    {
        $uploaded = $this->getRealUploaded();
        $download = $this->getRealDownloaded();
        if ($download == 0 && $uploaded == 0) {
            return '---';
        } elseif ($download == 0) {
            return 'Inf.';
        } else {
            return $uploaded / $download;
        }
    }

    public function getSeedtime()
    {
        return $this->seedtime;
    }

    public function getLeechtime()
    {
        return $this->leechtime;
    }

    public function getTimeRatio()
    {
        $seedtime = $this->seedtime;
        $leechtime = $this->leechtime;
        if ($leechtime == 0 && $seedtime == 0) {
            return '---';
        } elseif ($leechtime == 0) {
            return 'Inf.';
        } else {
            return $seedtime / $leechtime;
        }
    }

    private function getPeerStatus($seeder = null)
    {
        $peer_status = $this->getCacheValue('peer_count', function () {
            $peer_count = app()->pdo->createCommand("SELECT `seeder`, COUNT(id) FROM `peers` WHERE `user_id` = :uid GROUP BY seeder")->bindParams([
                'uid' => $this->id
            ])->queryAll() ?: [];
            return array_merge(['yes' => 0, 'no' => 0, 'partial' => 0], $peer_count);
        });

        return $seeder ? (int)$peer_status[$seeder] : $peer_status;
    }

    public function getActiveSeed()
    {
        return $this->getPeerStatus('yes');
    }

    public function getActiveLeech()
    {
        return $this->getPeerStatus('no');
    }

    public function getActivePartial()
    {
        return $this->getPeerStatus('partial');
    }

    public function getBonus(): float
    {
        return $this->bonus_seeding + $this->bonus_other;
    }

    /**
     * @return mixed
     */
    public function getInvites()
    {
        return $this->invites ?? 0;
    }

    /**
     * @return mixed
     */
    public function getTempInvitesSum()
    {
        return array_sum(array_map(function ($d) {
            return $d['total'] - $d['used'];
        }, $this->getTempInviteDetails()));
    }

    /**
     * @return array
     */
    public function getTempInviteDetails()
    {
        return $this->getCacheValue('temp_invites_details', function () {
            return app()->pdo->createCommand('SELECT * FROM `user_invitations` WHERE `user_id` = :uid AND (`total`-`used`) > 0 AND `expire_at` > NOW() ORDER BY `expire_at` ASC')->bindParams([
                    "uid" => app()->auth->getCurUser()->getId()
                ])->queryAll() ?: [];
        }) ?? [];
    }

    public function getPendingInvites()
    {
        return $this->getCacheValue('pending_invites', function () {
            return app()->pdo->createCommand('SELECT * FROM `invite` WHERE inviter_id = :uid AND expire_at > NOW() AND used = 0')->bindParams([
                'uid' => $this->id
            ])->queryAll();
        });
    }

    public function getInvitees()
    {
        return $this->getCacheValue('invitees', function () {
            return app()->pdo->createCommand('SELECT id,username,email,status,class,uploaded,downloaded FROM `users` WHERE `invite_by` = :uid')->bindParams([
                'uid' => $this->id
            ])->queryAll();
        });
    }

    public function getBookmarkList()
    {
        return $this->getCacheValue('bookmark_list', function () {
            return app()->pdo->createCommand('SELECT `tid` FROM `bookmarks` WHERE `uid` = :uid')->bindParams([
                'uid' => $this->id
            ])->queryColumn() ?: [];
        });
    }

    public function getUnreadMessageCount()
    {
        return $this->getCacheValue('unread_message_count', function () {
            return app()->pdo->createCommand("SELECT COUNT(`id`) FROM `messages` WHERE receiver = :uid AND unread = 'no'")->bindParams([
                'uid' => $this->id
            ])->queryScalar();
        });
    }

    public function getInboxMessageCount()
    {
        return $this->getCacheValue('inbox_count', function () {
            return app()->pdo->createCommand('SELECT COUNT(`id`) FROM `messages` WHERE `receiver` = :uid')->bindParams([
                'uid' => $this->id
            ])->queryScalar();
        });
    }

    public function getOutboxMessageCount()
    {
        return $this->getCacheValue('outbox_count', function () {
            return app()->pdo->createCommand('SELECT COUNT(`id`) FROM `messages` WHERE `sender` = :uid')->bindParams([
                'uid' => $this->id
            ])->queryScalar();
        });
    }

    public function inBookmarkList($tid = null)
    {
        return in_array($tid, $this->getBookmarkList());
    }

    public function isPrivilege($require_class)
    {
        if (is_string($require_class)) {
            $require_class = config('authority.' . $require_class) ?: 1;
        }

        return $this->class >= $require_class;
    }
}
