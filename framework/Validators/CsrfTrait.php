<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/28/2019
 * Time: 3:37 PM
 */

namespace Rid\Validators;

/**
 * Trait CsrfTrait
 * @package Rid\Validators
 * @property-read string $csrf
 */
trait CsrfTrait
{
    /** @noinspection PhpUnused */
    protected function validateCsrf()
    {
        $csrfText = \Rid\Helpers\ContainerHelper::getContainer()->get('session')->pop('csrfText');
        if (strcasecmp($this->csrf, $csrfText) != 0) {
            $this->buildCallbackFailMsg('csrf', 'csrf verification failed.');
            return;
        }
    }
}
