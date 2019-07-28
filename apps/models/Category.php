<?php
/** FIXME
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/27
 * Time: 14:03
 */

namespace apps\models;


use Rid\Exceptions\NotFoundException;

class Category
{

    protected $CacheKey = 'TORRENT:Category';

    private $id;
    private $name;

    private $categories;

    public function __construct()
    {
        $this->categories = app()->redis->hGetAll($this->CacheKey);
        if (empty($this->categories)) {
            $self = app()->pdo->createCommand("SELECT * FROM `categories`")->queryAll();
            $this->categories = array_column($self, 'name', 'id');
            app()->redis->hMset($this->CacheKey, $this->categories);
            app()->redis->expire($this->CacheKey, 3600);
        }
    }

    public function getAll() {
        return $this->categories;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $id
     * @return Category
     */
    public function setId($id)
    {
        $this->id = $id;
        $this->name = $this->categories[$id];

        if (is_null($this->name)) {
            throw new NotFoundException("The category id $id is not found.");
        }

        return $this;
    }
}
