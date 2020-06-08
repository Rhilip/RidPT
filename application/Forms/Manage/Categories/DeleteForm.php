<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 9:18 PM
 */

declare(strict_types=1);

namespace App\Forms\Manage\Categories;

use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class DeleteForm extends AbstractValidator
{
    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveInt(),
            'move_to' => new AcmeAssert\PositiveInt(),
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isExistCategoryId'];
    }

    public function flush(): void
    {
        // Move Category's torrent from this to it's parent
        container()->get('dbal')->prepare('UPDATE `torrents` SET `category` = :new WHERE `category` = :old ')->bindParams([
            'new' => $this->getInput('move_to'), 'old' => $this->getInput('id')
        ])->execute();

        // Delete it~
        container()->get('dbal')->prepare('DELETE FROM `categories` WHERE id = :id')->bindParams([
            'id' => $this->getInput('id')
        ])->execute();

        // TODO flush Redis cache
        container()->get('redis')->del('site:enabled_torrent_category');
    }

    protected function isExistCategoryId()
    {
        foreach (['id', 'move_to'] as $value) {
            $exist = container()->get('dbal')->prepare('SELECT id FROM categories WHERE id = :id')->bindParams([
                'id' => $this->getInput($value)
            ])->fetchScalar();
            if ($exist === false) {
                $this->buildCallbackFailMsg($value, 'The category is not exist');
            }
        }
    }
}
