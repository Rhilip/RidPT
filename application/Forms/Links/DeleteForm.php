<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/2/2020
 * Time: 8:20 PM
 */

declare(strict_types=1);

namespace App\Forms\Links;

use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class DeleteForm extends EditForm
{
    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveInt(),
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isExistLinkId'];
    }

    public function flush()
    {
        container()->get('pdo')->prepare('DELETE FROM `links` WHERE id = :id')->bindParams([
            'id' => $this->getInput('id')
        ])->execute();
        container()->get('redis')->del('Site:links');
    }
}
