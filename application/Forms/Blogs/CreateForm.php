<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 12:29 PM
 */

declare(strict_types=1);

namespace App\Forms\Blogs;

use Rid\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;

class CreateForm extends AbstractValidator
{
    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'title' => new Assert\Length(['max' => 255]),
            'body' => new Assert\NotBlank(),
            'notify' => new Assert\Optional(new Assert\IsTrue()),
            'force_read' => new Assert\Optional(new Assert\IsTrue())
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return [];
    }

    public function flush(): void
    {
        $userid = container()->get('auth')->getCurUser()->getId();
        container()->get('pdo')->prepare('INSERT INTO blogs (user_id, create_at, title, body, notify, force_read) VALUES (:uid, CURRENT_TIMESTAMP, :title, :body, :notify, :fread);')->bindParams([
            'uid' => $userid, 'title' => $this->getInput('title'), 'body' => $this->getInput('body'),
            'notify' => $this->getInput('notify', 0), 'fread' => $this->getInput('force_read', 0)
        ])->execute();

        // Clean News Cache
        container()->get('redis')->del('Site:recent_news');
    }
}
