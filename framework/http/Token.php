<?php

namespace mix\http;

use mix\base\Component;
use mix\helpers\StringHelper;

/**
 * Token组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Token extends Component
{

    // 保存处理者
    public $saveHandler;

    // 保存的Key前缀
    public $saveKeyPrefix = 'TOKEN:';

    // 有效期
    public $expiresIn = 604800;

    // session名
    public $name = 'access_token';

    // TokenKey
    protected $_tokenKey;

    // TokenID
    protected $_tokenId;

    // TokenID长度
    protected $_tokenIdLength = 32;

    // 请求前置事件
    public function onRequestBefore()
    {
        parent::onRequestBefore();
        // 载入TokenID
        $this->loadTokenId();
    }

    // 请求后置事件
    public function onRequestAfter()
    {
        parent::onRequestAfter();
        // 关闭连接
        $this->saveHandler->disconnect();
    }

    // 载入TokenID
    public function loadTokenId()
    {
        $this->_tokenId = \Mix::app()->request->get($this->name) or
        $this->_tokenId = \Mix::app()->request->header($this->name) or
        $this->_tokenId = \Mix::app()->request->post($this->name);
        $this->_tokenKey = $this->saveKeyPrefix . $this->_tokenId;
    }

    // 创建TokenID
    public function createTokenId()
    {
        do {
            $this->_tokenId  = StringHelper::getRandomString($this->_tokenIdLength);
            $this->_tokenKey = $this->saveKeyPrefix . $this->_tokenId;
        } while ($this->saveHandler->exists($this->_tokenKey));
    }

    // 设置唯一索引
    public function setUniqueIndex($uniqueId, $uniqueIndexPrefix = 'client_credentials:')
    {
        $uniqueKey = $this->saveKeyPrefix . $uniqueIndexPrefix . $uniqueId;
        // 删除旧token数据
        $oldTokenId = $this->saveHandler->get($uniqueKey);
        if (!empty($oldTokenId)) {
            $oldTokenkey = $this->saveKeyPrefix . $oldTokenId;
            $this->saveHandler->del($oldTokenkey);
        }
        // 更新唯一索引
        $this->saveHandler->setex($uniqueKey, $this->expiresIn, $this->_tokenId);
        // 在数据中加入索引信息
        $this->saveHandler->hmset($this->_tokenKey, ['__uidx__' => $uniqueId]);
    }

    // 赋值
    public function set($name, $value)
    {
        $success = $this->saveHandler->hmset($this->_tokenKey, [$name => serialize($value)]);
        $this->saveHandler->expire($this->_tokenKey, $this->expiresIn);
        return $success ? true : false;
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            $result = $this->saveHandler->hgetall($this->_tokenKey);
            unset($result['__uidx__']);
            foreach ($result as $key => $item) {
                $result[$key] = unserialize($item);
            }
            return $result ?: [];
        }
        $value = $this->saveHandler->hget($this->_tokenKey, $name);
        return $value === false ? null : unserialize($value);
    }

    // 判断是否存在
    public function has($name)
    {
        $exist = $this->saveHandler->hexists($this->_tokenKey, $name);
        return $exist ? true : false;
    }

    // 删除
    public function delete($name)
    {
        $success = $this->saveHandler->hdel($this->_tokenKey, $name);
        return $success ? true : false;
    }

    // 清除token
    public function clear()
    {
        $success = $this->saveHandler->del($this->_tokenKey);
        return $success ? true : false;
    }

    // 获取TokenId
    public function getTokenId()
    {
        return $this->_tokenId;
    }

    // 刷新token
    public function refresh($uniqueIndexPrefix = 'client_credentials:')
    {
        // 判断 token 是否存在
        $tokenData = $this->saveHandler->hgetall($this->_tokenKey);
        if (empty($tokenData)) {
            return false;
        }
        // 定义变量
        $oldData     = $tokenData;
        $oldTokenKey = $this->_tokenKey;
        $newTokenId  = StringHelper::getRandomString($this->_tokenIdLength);
        $newTokenKey = $this->saveKeyPrefix . $newTokenId;
        $uniqueKey   = $this->saveKeyPrefix . $uniqueIndexPrefix . $oldData['__uidx__'];
        // 判断索引是否正确
        $exists = $this->saveHandler->exists($uniqueKey);
        if (empty($exists)) {
            return false;
        }
        // 删除旧数据
        $this->saveHandler->del($oldTokenKey);
        // 生成新数据
        $this->saveHandler->hmset($newTokenKey, $oldData);
        $this->saveHandler->expire($newTokenKey, $this->expiresIn);
        // 更新索引信息
        $this->saveHandler->set($uniqueKey, $newTokenId);
        $this->saveHandler->expire($uniqueKey, $this->expiresIn);
        // 新 token 赋值到属性
        $this->_tokenId  = $newTokenId;
        $this->_tokenKey = $newTokenKey;
        // 返回
        return true;
    }

}
