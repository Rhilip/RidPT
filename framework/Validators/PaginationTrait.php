<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 7:37 PM
 */

namespace Rid\Validators;

trait PaginationTrait
{
    public $page;
    public $limit;  // pecPage
    public $data;

    protected $offset;
    protected $total;

    protected $pager_data_total;
    protected $pager_data;

    public static function getDefaultPage(): int
    {
        return static::$DEFAULT_PAGE ?? 1;
    }

    public static function getDefaultLimit(): int
    {
        return static::$DEFAULT_LIMIT ?? 50;
    }

    public static function getMinLimit(): int
    {
        return static::$MIN_LIMIT ?? 10;
    }

    public static function getMaxLimit(): int
    {
        return static::$MAX_LIMIT ?? 50;
    }

    public static function getDataSource(): string
    {
        return static::$DATA_SOURCE ?? 'remote';
    }

    public static function defaultData(): array
    {
        return [
            'page' => static::getDefaultPage(),
            'limit' => static::getDefaultLimit()
        ];
    }

    public static function inputRules(): array
    {
        return [
            'page' => 'Integer', 'limit' => 'Integer'
        ];
    }

    public static function callbackRules(): array
    {
        return ['checkPager'];
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /** @noinspection PhpUnused */
    protected function checkPager()
    {
        $limit = intval($this->getInput('limit', static::getDefaultLimit()));
        if ($limit < static::getMinLimit()) {
            $limit = static::getMinLimit();
        }
        if ($limit > static::getMaxLimit()) {
            $limit = static::getMaxLimit();
        }
        $page = intval($this->getInput('page', static::getDefaultPage()));

        $this->setInput(['limit' => $limit, 'page' => $page]);

        $this->total = $this->getDataTotal();
        $this->offset = ($page - 1) * $limit;

        // Quick return empty array when offset is much bigger than total, So we needn't hit remote or local data
        if ($this->offset > $this->total) {
            $this->pager_data = [];
        }
    }

    final private function getDataTotal(): int
    {
        if (is_null($this->pager_data_total)) {
            if (static::getDataSource() == 'remote') {
                $this->pager_data_total = $this->getRemoteTotal();
            } else {
                $this->pager_data_total = count($this->getInput('data', []));
            }
        }
        return $this->pager_data_total;
    }

    protected function getRemoteTotal(): int
    {
        throw new \RuntimeException('function "getRemoteTotal()" not implemented.');
    }

    final public function getPagerData(): array
    {
        if (is_null($this->pager_data)) {
            if (static::getDataSource() == 'remote') {
                $this->pager_data = $this->getRemoteData();
            } else {
                $this->pager_data = array_slice($this->getInput('data', []), $this->offset, $this->limit);
            }
        }
        return $this->pager_data;
    }

    protected function getRemoteData(): array
    {
        throw new \RuntimeException('function "getRemoteData()" not implemented.');
    }
}
