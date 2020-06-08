<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 4:34 PM
 */

declare(strict_types=1);

namespace App\Forms\Blogs;

use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class EditForm extends ExistForm
{
    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveInt(),
            'title' => new Assert\Length(['max' => 255]),
            'body' => new Assert\NotBlank(),
            'notify' => new Assert\Optional(new Assert\IsTrue()),
            'force_read' => new Assert\Optional(new Assert\IsTrue())
        ]);
    }

    public function flush(): void
    {
        $userid = container()->get('auth')->getCurUser()->getId();
        container()->get('dbal')->prepare('UPDATE blogs SET user_id = :uid, title = :title, body = :body, notify = :notify, force_read = :fread WHERE id=:id')->bindParams([
            'id' => $this->getInput('id'), 'uid' => $userid,
            'title' => $this->getInput('title'), 'body' => $this->getInput('body'),
            'notify' => $this->getInput('notify'), 'fread' => $this->getInput('force_read')
        ])->execute();

        // Clean News Cache
        container()->get('redis')->del('Site:recent_news');
    }
}
