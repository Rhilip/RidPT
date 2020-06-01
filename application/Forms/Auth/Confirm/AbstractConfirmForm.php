<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Forms\Auth\Confirm;

use Symfony\Component\Validator\Constraints as Assert;
use Rid\Validators\AbstractValidator;

abstract class AbstractConfirmForm extends AbstractValidator
{

    protected string $action = '';
    protected string $msg = '';

    protected ?array $record;

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'secret' => new Assert\NotBlank(),
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['validConfirmSecret'];
    }

    /**
     * Verity The confirm secret and action exist in table `user_confirm` or not
     *
     * @noinspection PhpUnused
     */
    protected function validConfirmSecret()
    {
        $record = container()->get('pdo')->prepare(
            'SELECT `user_confirm`.`id`,`user_confirm`.`uid`,`users`.`status` as `user_status`,`users`.`username`,`users`.`email` FROM `user_confirm`
                  LEFT JOIN `users` ON `users`.`id` = `user_confirm`.`uid`
                  WHERE `secret` = :secret AND `action` = :action AND used = 0 LIMIT 1;'
        )->bindParams([
            'secret' => $this->getInput('secret'), 'action' => $this->action
        ])->queryOne();

        if ($record == false) {  // It means this confirm key is not exist
            $this->buildCallbackFailMsg('confirm key', 'This confirm key is not exist');  // FIXME msg
            return;
        }

        $this->record = $record;
    }

    protected function update_confirm_status()
    {
        container()->get('pdo')->prepare('UPDATE `user_confirm` SET `used` = 1 WHERE id = :id')->bindParams([
            'id' => $this->record['id']
        ])->execute();
    }

    public function getConfirmMsg() {
        return $this->msg;
    }
}
