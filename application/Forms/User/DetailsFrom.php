<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 10:05 PM
 */

declare(strict_types=1);

namespace App\Forms\User;

use App\Forms\Traits\isValidUserTrait;
use Rid\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;

class DetailsFrom extends AbstractValidator
{
    use isValidUserTrait;

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isValidUser'];
    }

    public function flush(): void
    {
    }

    public function getUserId(): int
    {
        return container()->get('auth')->getCurUser()->getId();
    }
}
