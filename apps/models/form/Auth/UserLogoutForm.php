<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/11/2019
 * Time: 2019
 */

namespace apps\models\form\Auth;


use apps\libraries\Constant;
use Rid\Helpers\JWTHelper;
use Rid\Validators\CsrfTrait;
use Rid\Validators\Validator;

class UserLogoutForm extends Validator
{
    use CsrfTrait;

    public $sure;

    private $sid;

    protected $_autoload_data = true;
    protected $_autoload_data_from = ['get'];

    public static function inputRules()
    {
        $ret = [
        // TODO    'csrf' => 'Required'  // Use &csrf=
        ];

        // TODO When prevent_anonymous model enabled, we should notice user to recheck
        // if (config('base.prevent_anonymous')) $ret['sure'] = 'Required | Equal(value=1)';

        return $ret;
    }

    public static function callbackRules()
    {
        return [/* TODO 'validateCsrf', */ 'getUserSessionId'];
    }

    /** @noinspection PhpUnused */
    protected function getUserSessionId() {
        $session = app()->request->cookie(Constant::cookie_name);
        if (is_null($session)) {
            $this->buildCallbackFailMsg('session','How can you post data without cookies?');
            return;
        }

        $payload = JWTHelper::decode($session);
        if ($payload === false || !isset($payload['jti'])) {
            $this->buildCallbackFailMsg('jwt','Fail to get $jti information');
            return;
        }

        $this->sid = $payload['jti'];
    }

    public function flush()
    {
        $this->invalidSession();
    }

    private function invalidSession()
    {
        // Set this session is expired
        app()->pdo->createCommand('UPDATE `user_session_log` SET `expired` = 1 WHERE sid = :sid')->bindParams([
            'sid' => $this->sid
        ])->execute();

        // Clean cookie
        app()->cookie->delete(Constant::cookie_name);

        // Mark this invalid
        app()->redis->sAdd(Constant::invalidUserSessionSet, $this->sid);
    }
}
