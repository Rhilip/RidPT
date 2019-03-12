<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/8
 * Time: 18:58
 */

namespace Rid\Validators;

Trait CaptchaTrait
{
    public $captcha;

    protected function validateCaptcha()
    {
        $captchaText = app()->session->get('captchaText');
        if (strcasecmp($this->captcha, $captchaText) != 0) {
            $this->_errors['CAPTCHA'] = 'CAPTCHA verification failed.';
            $this->_success = false;
            return;
        }
    }
}
