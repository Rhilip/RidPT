<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 10:05 PM
 */

declare(strict_types=1);

namespace App\Forms\User\Sessions;

use App\Forms\Traits\isValidUserTrait;
use Rid\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;

class RevokeForm extends AbstractValidator
{
    use isValidUserTrait;

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new Assert\NotBlank()
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isValidUser'];
    }

    public function flush(): void
    {
        container()->get('pdo')->prepare('UPDATE `sessions` SET `expired` = 1 WHERE `uid` = :uid AND `session` = :sid')->bindParams([
            'uid' => $this->getUserId(), 'sid' => $this->getInput('id')
        ])->execute();
        container()->get('redis')->zRem(container()->get('auth')->getCurUser()->sessionSaveKey, $this->getInput('id'));
    }

    public function getUserId(): int
    {
        return container()->get('auth')->getCurUser()->getId();
    }
}
