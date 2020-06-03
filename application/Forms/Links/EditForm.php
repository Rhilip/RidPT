<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/2/2020
 * Time: 3:50 PM
 */

declare(strict_types=1);

namespace App\Forms\Links;

use App\Enums\Links\Status;
use App\Enums\Site\LogLevel;
use Symfony\Component\Validator\Constraints as Assert;

class EditForm extends ApplyForm
{
    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new Assert\PositiveOrZero(),
            'name' => new Assert\NotBlank(),
            'url' => new Assert\Url(),
            'status' => new Assert\Choice(Status::values()),
            'admin' => new Assert\NotBlank(),
            'email' => new Assert\Email(),
            'reason' => new Assert\NotBlank(),
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        $rules = [];
        if ($this->getInput('id') > 0) {
            $rules[] = 'isExistLinkId';
        }

        return $rules;
    }

    public function flush()
    {
        if ($this->getInput('id') == 0) {  // Create new links by edit method
            parent::flush();
        } else {
            container()->get('pdo')->prepare('UPDATE `links` SET name = :name, url = :url, title = :title, status = :status, administrator = :admin, email = :email, reason = :reason WHERE id = :id')->bindParams([
                'name' => $this->getInput('name'), 'url' => $this->getInput('url'), 'title' => $this->getInput('title'),
                'status' => $this->getInput('status'), 'admin' => $this->getInput('admin'), 'email' => $this->getInput('email'),
                'reason' => $this->getInput('reason'), 'id' => $this->getInput('id')
            ])->execute();

            $cur_user = container()->get('auth')->getCurUser();
            container()->get('site')->writeLog('The data of links ' . $this->getInput('id') . ' is update by ' .
                $cur_user->getUsername() . '(' . $cur_user->getId() . ').', LogLevel::MOD);
            container()->get('redis')->del('Site:links');
        }
    }

    /** @noinspection PhpUnused */
    protected function isExistLinkId()
    {
        $link = container()->get('pdo')->prepare('SELECT `id` FROM `links` WHERE id = :id')->bindParams([
            'id' => $this->getInput('id')
        ])->queryScalar();
        if ($link === false) {
            $this->buildCallbackFailMsg('links', 'the link data not found in our database');
            return;
        }
    }
}
