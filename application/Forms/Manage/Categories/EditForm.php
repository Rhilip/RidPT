<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 8:52 PM
 */

declare(strict_types=1);

namespace App\Forms\Manage\Categories;

use Rid\Utils\Arr;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class EditForm extends AbstractValidator
{
    public function __construct()
    {
        $this->setInput(['id' => 0]);  // Work as create
    }

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveOrZeroInt(),
            'name' => new Assert\Length(['max' => 30]),
            'enabled' => new AcmeAssert\looseChoice([0, 1]),
            'image' => new Assert\Regex('/^[a-z0-9_.\/]*$/'),
            'class_name' => new Assert\Regex('/^[a-z][a-z0-9_\-]*?$/'),
            'sort_index' => new AcmeAssert\PositiveOrZeroInt(),
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return [];
    }

    public function flush(): void
    {
        if ($this->getInput('id') > 0) {
            $exist = container()->get('pdo')->prepare('SELECT COUNT(id) FROM categories WHERE id = :id')->bindParams([
                'id' => $this->getInput('id')
            ])->queryScalar();
        } else {
            $exist = false;
        }

        if ($exist) {  // UPDATE
            container()->get('pdo')->prepare('UPDATE `categories` SET name = :name, enabled = :enabled, image = :image, class_name = :class_name, sort_index = :sort_index WHERE id = :id')->bindParams(
                Arr::only($this->getInput(), ['id', 'name', 'enabled', 'image', 'class_name', 'sort_index'])
            )->execute();
        } else {  // INSERT
            container()->get('pdo')->prepare('INSERT INTO `categories` (name, enabled, image, class_name, sort_index) VALUES (:name,:enabled,:image,:class_name,:sort_index)')->bindParams(
                Arr::only($this->getInput(), ['name', 'enabled', 'image', 'class_name', 'sort_index'])
            )->execute();
        }
    }
}
