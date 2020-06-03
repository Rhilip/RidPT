<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 5:13 PM
 */

declare(strict_types=1);

namespace App\Forms\Blogs;


use Rid\Validators\AbstractValidator;
use Rid\Validators\Compound as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ExistForm extends AbstractValidator
{
    protected ?array $blog;

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\Id(),
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isExistBlog'];
    }

    public function flush()
    {
    }

    /** @noinspection PhpUnused */
    protected function isExistBlog()
    {
        $record = container()->get('pdo')->prepare('SELECT * FROM blogs WHERE id = :id LIMIT 1')->bindParams([
            'id' => $this->getInput('id')
        ])->queryOne();
        if (false === $record) {
            $this->buildCallbackFailMsg('Exist', 'The blog id ' . $this->getInput('id') . 'is not exist');
        }

        $this->blog = $record;
    }

    /**
     * @return array
     */
    public function getBlog(): array
    {
        return $this->blog;
    }
}
