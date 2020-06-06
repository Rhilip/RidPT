<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 9:21 PM
 */

declare(strict_types=1);

namespace App\Forms\Manage\Categories;

use Rid\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;

class IndexForm extends AbstractValidator
{
    private ?array $categories;

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([]);
    }

    protected function loadCallbackMetaData(): array
    {
        return [];
    }

    public function flush()
    {
        $this->categories = container()->get('pdo')->prepare('SELECT * FROM categories ORDER BY `sort_index`,`id`')->queryAll();
    }

    /**
     * @return array|null
     */
    public function getCategories(): ?array
    {
        return $this->categories;
    }
}
