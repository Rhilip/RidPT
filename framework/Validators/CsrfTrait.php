<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/28/2019
 * Time: 3:37 PM
 */

namespace Rid\Validators;

trait CsrfTrait
{
    public $csrf;

    /** @noinspection PhpUnused */
    protected function validateCsrf()
    {
        $csrfInput = $this->getInput('csrf');
        $csrfText = app()->session->pop('csrfText');
        if (strcasecmp($csrfInput, $csrfText) != 0) {
            $this->buildCallbackFailMsg('csrf', 'csrf verification failed.');
            return;
        }
    }
}
