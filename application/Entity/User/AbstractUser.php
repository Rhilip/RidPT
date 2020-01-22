<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 1/22/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Entity\User;

use App\Libraries\Constant;
use Rid\Base\BaseObject;
use Rid\Utils\ClassValueCacheUtils;

class AbstractUser extends BaseObject implements AbstractUserInterface
{
    use ClassValueCacheUtils;

    /** @var int */
    protected $id;
    protected $username;
    protected $email;
    protected $status;
    protected $class = 0;

    protected $avatar;

    protected $info_cache_key;

    protected function getCacheNameSpace(): string
    {
        if (is_null($this->info_cache_key)) {
            $this->info_cache_key = Constant::userContent($this->id);
        }

        return $this->info_cache_key;
    }

    public function __construct($id = 0)
    {
        $this->id = $id;
        $self = app()->redis->hGetAll($this->info_cache_key);
        if (empty($self) || !isset($self['id'])) {
            if (app()->redis->zScore(Constant::invalidUserIdZset, $id) === false) {
                $self = app()->pdo->createCommand('SELECT id, username, email, status, class, passkey, uploadpos, downloadpos, uploaded, downloaded, seedtime, leechtime, avatar, bonus_seeding, bonus_other, lang, invites FROM `users` WHERE id = :id LIMIT 1;')->bindParams([
                    'id' => $id
                ])->queryOne();
                if (false === $self) {
                    app()->redis->zAdd(Constant::invalidUserIdZset, time() + 3600, $id);
                } else {
                    app()->redis->hMSet($this->info_cache_key, $self);
                    app()->redis->expire($this->info_cache_key, 15 * 60);  // Cache This User Detail for 15 minutes
                }
            } else {
                $self = false;  // It means this user id is invalid ( And already checked in last 60 minutes )
            }
        }

        if ($self === false) {
            return false;
        }

        $this->importAttributes($self);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getClass(): int
    {
        return $this->class;
    }

    public function getStatus(): string
    {
        return $this->status;
    }



    public function getAvatar(array $opts = []): string
    {
        if (config('user.avatar_provider') === 'gravatar') {
            /** Get a Gravatar URL for a specified email address.
             *
             * @param int|string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
             * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
             * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
             */
            $url = config('gravatar.base_url') . md5(strtolower(trim($this->email)));
            $url .= '?' . http_build_query([
                    's' => $opts['s'] ?? 80,
                    'd' => $opts['d'] ?? config('gravatar.default_fallback') ?? 'identicon',
                    'r' => $opts['r'] ?? config('gravatar.maximum_rating') ?? 'g'
                ], '', '&', PHP_QUERY_RFC3986);
            return $url;
        }/* elseif (config('user.avatar_provider') === 'remote') {
            // For example : another Image Hosting
        }*/ else {  // config('user.avatar_provider') === 'local'
            if ($this->avatar == '') {
                $this->avatar = '/static/avatar/default_avatar.jpg';
            }
        }

        return $this->avatar;
    }
}
