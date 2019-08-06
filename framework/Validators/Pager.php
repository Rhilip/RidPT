<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 6:51 PM
 */

namespace Rid\Validators;


class Pager extends Validator
{
    public $page;
    public $limit;  // pecPage
    public $data;

    protected $offset;
    protected $total;

    static $default_page = 1;
    static $default_limit = 50;
    static $max_limit = 50;
    static $data_source = 'remote';

    protected $pager_data_total;
    protected $pager_data;

    public static function defaultData(){
        return [
            'page' => static::$default_page,
            'limit' => static::$default_limit
        ];
    }

    public static function inputRules()
    {
        return [
            'page' => 'Integer', 'limit' => 'Integer'
        ];
    }

    public static function callbackRules()
    {
        return ['checkPager'];
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    protected function checkPager()
    {
        $limit = $this->getData('limit', static::$default_limit);
        if ($limit > static::$max_limit) $limit = static::$max_limit;
        $page = $this->getData('page', static::$default_page);

        $this->setData(['limit' => $limit, 'page' => $page]);

        $this->total = $this->getDataTotal();
        $this->offset = ($page - 1) * $limit;

        // Quick return empty array when offset is much bigger than total, So we needn't hit remote or local data
        if ($this->offset > $this->total) $this->pager_data = [];
    }

    final public function getDataTotal(): int
    {
        if (is_null($this->pager_data_total)) {
            if (static::$data_source == 'remote') $this->pager_data_total = $this->getRemoteTotal();
            else $this->pager_data_total = count($this->getData('data', []));
        }
        return $this->pager_data_total;
    }

    protected function getRemoteTotal()
    {
        throw new \RuntimeException('function "getRemoteTotal()" not implemented.');
    }

    final public function getPagerData()
    {
        if (is_null($this->pager_data)) {
            if (static::$data_source == 'remote') $this->pager_data = $this->getRemoteData();
            else $this->pager_data = array_slice($this->getData('data', []), $this->offset, $this->limit);
        }
        return $this->pager_data;
    }

    protected function getRemoteData()
    {
        throw new \RuntimeException('function "getRemoteData()" not implemented.');
    }
}
