<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/8
 * Time: 18:58
 */

namespace Rid\Validators;


use Symfony\Component\Validator\Context\ExecutionContextInterface;

Trait CaptchaTrait
{
    public $captcha;

    public function validateCaptcha(ExecutionContextInterface $context, $payload)
    {
        $captchaText = app()->session->get('captchaText');
        if (strcasecmp($this->captcha, $captchaText) != 0) {
            $context->buildViolation("CAPTCHA verification failed")->addViolation();
        }
    }
}
