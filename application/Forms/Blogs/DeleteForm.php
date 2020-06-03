<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 4:35 PM
 */

declare(strict_types=1);

namespace App\Forms\Blogs;


class DeleteForm extends ExistForm
{
    public function flush()
    {
        container()->get('pdo')->prepare('DELETE FROM blogs WHERE id = :id')->bindParams([
            'id' => $this->getInput('id')
        ])->execute();
    }
}
