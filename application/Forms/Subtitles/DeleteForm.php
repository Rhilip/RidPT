<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 9:47 AM
 */

declare(strict_types=1);

namespace App\Forms\Subtitles;

use App\Libraries\Constant;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class DeleteForm extends ExistForm
{
    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveInt(),
            'reason' => new Assert\NotBlank()
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isValidSubtitle', 'canCurUserManagerSubs'];
    }

    /** @noinspection PhpUnused */
    protected function canCurUserManagerSubs()
    {
        $curuser = container()->get('auth')->getCurUser();
        if (!$curuser->isPrivilege('manage_subtitles') ||  // not submanage_class
            $this->getSubtitle()['uppd_by'] != $curuser->getId()  // not Subtitle 'owner'
        ) {
            $this->buildCallbackFailMsg('Privilege', 'You can\'t Modify Subtitle.');
        }
    }

    public function flush(): void
    {
        $subtitle = $this->getSubtitle();

        container()->get('pdo')->prepare('DELETE FROM subtitles WHERE id = :sid')->bindParams([
            'sid' => $subtitle['id']
        ])->execute();
        unlink($this->getSubtitleLoc());

        // TODO Delete uploader bonus

        if ($subtitle['uppd_by'] != container()->get('auth')->getCurUser()->getId()) {
            container()->get('site')->sendPM(0, $subtitle['uppd_by'], 'msg_your_sub_deleted', 'msg_deleted_your_sub');
        }

        // TODO add user detail
        container()->get('site')->writeLog('Subtitle \'' . $subtitle['title'] . '\'(' . $subtitle['id'] .') was deleted by ' . container()->get('auth')->getCurUser()->getUsername());
        container()->get('redis')->del(Constant::siteSubtitleSize);
    }

    public function getSubtitleId(): int
    {
        return (int)$this->getInput('id');
    }
}
