<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Forms\Links;

use App\Enums\Links\Status;
use App\Forms\Traits\CaptchaTrait;
use Rid\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;

class ApplyForm extends AbstractValidator
{
    use CaptchaTrait;

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'name' => [new Assert\NotBlank(), new Assert\Length(['max' => 30])],
            'url' => new Assert\Url(['relativeProtocol' => true]),
            'title' => new Assert\Length(['max' => 50]),
            'admin' => new Assert\NotBlank(),
            'email' => new Assert\Email(),
            'reason' => new Assert\NotBlank(),
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['validateCaptcha', 'checkExistLinksByUrl'];
    }

    public function flush()
    {
        container()->get('pdo')->prepare('INSERT INTO `links`(`name`, `url`, `title`, `status`, `administrator`, `email`, `reason`) VALUES (:name,:url,:title,:status,:admin,:email,:reason)')->bindParams([
            'name' => $this->getInput('name'), 'url' => $this->getInput('url'), 'title' => $this->getInput('title'),
            'status' => Status::PENDING, 'admin' => $this->getInput('admin'), 'email' => $this->getInput('email'),
            'reason' => $this->getInput('reason')
        ])->execute();
        container()->get('redis')->del('Site:links');
        // TODO Send system PM to site group
    }

    /** @noinspection PhpUnused */
    protected function checkExistLinksByUrl()
    {
        $count = container()->get('pdo')->prepare('SELECT COUNT(`id`) FROM `links` WHERE url = :url')->bindParams([
            'url' => $this->getInput('url')
        ])->queryScalar();
        if ($count > 0) {
            $this->buildCallbackFailMsg('Link:exist', 'This link is exist in our site, Please don\'t report it again and again');
        }
    }
}
