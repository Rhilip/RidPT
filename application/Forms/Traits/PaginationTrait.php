<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 11:34 AM
 */

declare(strict_types=1);

namespace App\Forms\Traits;

trait PaginationTrait
{
    private ?int $pagination_page;
    private ?int $pagination_limit;

    private ?int $pagination_total;
    private ?array $pagination_data;

    /**
     * @return int|null
     */
    public function getPaginationPage(): ?int
    {
        return $this->pagination_page;
    }

    /**
     * @param int $pagination_page
     */
    protected function setPaginationPage($pagination_page): void
    {
        $this->pagination_page = (int)$pagination_page;
    }

    /**
     * @return int|null
     */
    public function getPaginationLimit(): ?int
    {
        return $this->pagination_limit;
    }

    /**
     * @param int|null $pagination_limit
     */
    protected function setPaginationLimit($pagination_limit): void
    {
        $this->pagination_limit = (int)$pagination_limit;
    }

    /**
     * @return int|null
     */
    public function getPaginationTotal(): ?int
    {
        return $this->pagination_total;
    }

    /**
     * @param int|null $pagination_total
     */
    protected function setPaginationTotal(?int $pagination_total): void
    {
        $this->pagination_total = $pagination_total;
    }

    /**
     * @return array|null
     */
    public function getPaginationData(): ?array
    {
        return $this->pagination_data;
    }

    /**
     * @param array|null $pagination_data
     */
    protected function setPaginationData(?array $pagination_data): void
    {
        $this->pagination_data = $pagination_data;
    }

    /**
     * @return int|null
     */
    public function getPaginationOffset(): int
    {
        return (int)max(($this->getPaginationPage() - 1) * $this->getPaginationLimit(), 0);
    }
}
