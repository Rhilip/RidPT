<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 11:22 PM
 */

namespace App\Models\Form\Subtitles;

use App\Libraries\Constant;
use App\Models\Form\Traits\isValidSubtitleTrait;

use Rid\Validators\Validator;

class DeleteForm extends Validator
{
    use isValidSubtitleTrait;

    public $reason;

    protected $_autoload = true;
    protected $_autoload_from = ['post'];

    public static function inputRules(): array
    {
        return [
            'id' => 'required | Integer',
            'reason' => 'required'
        ];
    }

    public static function callbackRules(): array
    {
        return ['isValidSubtitle', 'canCurUserManagerSubs'];
    }

    /** @noinspection PhpUnused */
    protected function canCurUserManagerSubs()
    {
        $curuser = app()->auth->getCurUser();
        if (!$curuser->isPrivilege('manage_subtitles') ||  // not submanage_class
            $this->subtitle['uppd_by'] != $curuser->getId()  // not Subtitle 'owner'
        ) {
            $this->buildCallbackFailMsg('Privilege', 'You can\'t Modify Subtitle.');
        }
    }

    public function flush()
    {
        $filename = $this->id . '.' . $this->subtitle['ext'];
        $file_loc = app()->getStoragePath('subs') . DIRECTORY_SEPARATOR . $filename;

        app()->pdo->prepare('DELETE FROM subtitles WHERE id = :sid')->bindParams([
            'sid' => $this->subtitle['id']
        ])->execute();
        unlink($file_loc);

        // TODO Delete uploader bonus

        if ($this->subtitle['uppd_by'] != app()->auth->getCurUser()->getId()) {
            app()->site->sendPM(0, $this->subtitle['uppd_by'], 'msg_your_sub_deleted', 'msg_deleted_your_sub');
        }

        // TODO add user detail
        app()->site->writeLog('Subtitle \'' . $this->subtitle['title'] . '\'(' . $this->subtitle['id'] .') was deleted by ' . app()->auth->getCurUser()->getUsername());
        app()->redis->del(Constant::siteSubtitleSize);
    }
}
