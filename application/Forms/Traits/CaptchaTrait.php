<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Forms\Traits;

/**
 * When use this Trait, Add input which name is `captcha` in your Form,
 * which you can simply call it by `<?= $this->insert('layout/captcha') ?>` in template
 * Add add callback function `validateCaptcha` in callbackRules()
 *
 * Trait CaptchaTrait
 * @package Rid\Validators
 */
trait CaptchaTrait
{
    /** @noinspection PhpUnused */
    protected function validateCaptcha()
    {
        // TODO captcha by config
        $captchaText = container()->get('session')->get('captchaText');
        if (strcasecmp($this->getInput('captcha'), $captchaText) != 0) {
            $this->buildCallbackFailMsg('CAPTCHA','verification failed.');
            return;
        }
    }
}
