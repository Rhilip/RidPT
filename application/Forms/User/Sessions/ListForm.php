<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 10:05 PM
 */

declare(strict_types=1);

namespace App\Forms\User\Sessions;

use App\Forms\Traits\isValidUserTrait;
use App\Forms\Traits\PaginationTrait;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ListForm extends AbstractValidator
{
    use PaginationTrait;
    use isValidUserTrait;

    public function __construct()
    {
        $this->setInput([
            'page' => 0,
            'limit' => 10,
            'expired' => [-1, 0] // Default not show expired session
        ]);
    }

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'page' => new AcmeAssert\PositiveOrZeroInt(),
            'limit' => new AcmeAssert\RangeInt(['min' => 0, 'max' => 50]),
            'expired' => new AcmeAssert\looseChoice([
                'choices' => [-1 /* Never Expired */, 0 /* Temporary */, 1 /* Expired */],
                'multiple' => true
            ])
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isValidUser'];
    }

    public function flush(): void
    {
        $where_pdo = [
            ['AND uid = :uid ', 'params' => ['uid' => $this->getUserId()]],
            ['AND `expired` IN (:expired)', 'params' => ['expired' => $this->getInput('expired')]],
        ];


        $total = container()->get('pdo')->prepare([
            ['SELECT COUNT(`id`) FROM sessions WHERE 1=1 '],
            ...$where_pdo
        ])->queryScalar();
        $this->setPaginationTotal($total);

        $this->setPaginationLimit($this->getInput('limit'));
        $this->setPaginationPage($this->getInput('page'));

        $data = container()->get('pdo')->prepare([
            ['SELECT `id`, session, `login_at`, `login_ip`, `expired` FROM sessions WHERE 1=1 '],
            ...$where_pdo,
            ['ORDER BY `expired`, `id` DESC'],
            ['LIMIT :o, :l', 'params' => ['o' => $this->getPaginationOffset(), 'l' => $this->getPaginationLimit()]]
        ])->queryAll();
        $this->setPaginationData($data);
    }

    // FIXME allow admin to see other user's detail
    public function getUserId(): int
    {
        return container()->get('auth')->getCurUser()->getId();
    }
}
