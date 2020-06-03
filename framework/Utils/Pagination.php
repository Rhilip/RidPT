<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/31/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Utils;

class Pagination
{
    /** @var int 当前页码 */
    private int $page;

    /** @var int 每页显示数量 */
    private int $perPage;

    /** @var int 偏移量 */
    private ?int $limitOffset;

    /** @var int 结束的偏移量（limitOffset + count - 1） */
    private ?int $limitEndOffset;

    public function __construct($page = 0, $count = 50)
    {
        $this->page = (int)$page;
        $this->perPage = (int)$count;
        $this->calc();
    }

    /**
     * Get 当前页码
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set 当前页码
     *
     * @param int $page 当前页码
     * @return self
     */
    public function setPage(int $page)
    {
        $this->page = $page;

        $this->calc();
        return $this;
    }

    /**
     * Get 每页显示数量
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * Set 每页显示数量
     *
     * @param int $perPage 每页显示数量
     * @return self
     */
    public function setPerPage(int $perPage)
    {
        $this->perPage = $perPage;

        $this->calc();
        return $this;
    }

    /**
     * 计算
     *
     * @return void
     */
    private function calc()
    {
        $count = $this->perPage;
        $this->limitOffset = max((int)(($this->page - 1) * $count), 0);
        $this->limitEndOffset = $this->limitOffset + $count - 1;
    }

    /**
     * Get 偏移量
     *
     * @return int
     */
    public function getLimitOffset()
    {
        return $this->limitOffset;
    }

    /**
     * Get 结束的偏移量（limitOffset + count - 1）
     *
     * @return int
     */
    public function getLimitEndOffset()
    {
        return $this->limitEndOffset;
    }

    /**
     * 根据记录数计算总页数
     *
     * @param int $records
     * @return int
     */
    public function calcPageCount(int $records)
    {
        $count = $this->perPage;
        if (0 === $records % $count) {
            return $records / $count;
        } else {
            return ((int)($records / $count)) + 1;
        }
    }
}
