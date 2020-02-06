<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace Rid\Database;

use Rid\Base\Component;

/**
 * BasePdo组件
 */
class BasePDOConnection extends Component
{

    // 数据源格式
    public string $dsn = '';

    // 数据库用户名
    public string $username = 'root';

    // 数据库密码
    public string $password = '';

    // 驱动连接选项
    public array $driverOptions = [];

    // PDO Class
    protected ?\PDO $_pdo;
    protected ?\PDOStatement $_pdoStatement;

    // sql片段
    protected array $_sqlFragments = [];

    // sql
    protected string $_sql = '';

    // params
    protected array $_params = [];

    // values
    protected array $_values = [];

    // sql原始数据
    protected array $_sqlPrepareData = [];

    protected bool $_recordData = true;
    protected array $_sqlExecuteData = [];

    // 默认驱动连接选项
    protected array $_defaultDriverOptions = [
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ];

    // 驱动连接选项
    protected array $_driverOptions = [];

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        $this->_driverOptions = $this->driverOptions + $this->_defaultDriverOptions;  // 设置驱动连接选项
    }

    public function onRequestBefore()
    {
        parent::onRequestBefore();
        $this->_sqlExecuteData = [];
    }

    // 创建连接
    protected function createConnection()
    {
        return new \PDO($this->dsn, $this->username, $this->password, $this->_driverOptions);
    }

    // 连接
    protected function connect()
    {
        $this->_pdo = $this->createConnection();
    }

    // 关闭连接
    public function disconnect()
    {
        $this->_pdoStatement = null;
        $this->_pdo          = null;
    }

    // 查询构建
    public function queryBuilder($item)
    {
        if (isset($item['if']) && $item['if'] == false) {
            return $this;
        }
        if (isset($item['params'])) {
            $this->bindParams($item['params']);
        }
        $this->_sqlFragments[] = array_shift($item);
        return $this;
    }

    // 创建命令
    public function prepare($sql = null)
    {
        // 清扫数据
        $this->_sql    = '';
        $this->_params = [];
        $this->_values = [];
        // 字符串构建
        if (is_string($sql)) {
            $this->_sql = $sql;
        }
        // 数组构建
        if (is_array($sql)) {
            foreach ($sql as $item) {
                $this->queryBuilder($item);
            }
            $this->_sql = implode(' ', $this->_sqlFragments);
        }
        if (is_null($sql)) {
            $this->_sql = implode(' ', $this->_sqlFragments);
        }
        // 清扫数据
        $this->_sqlFragments = [];
        // 保存SQL
        $this->_sqlPrepareData = [$this->_sql];
        // 返回
        return $this;
    }

    // 绑定参数
    public function bindParams($data)
    {
        $this->_params += $data;
        return $this;
    }

    // 绑定值
    protected function bindValues($data)
    {
        $this->_values += $data;
        return $this;
    }

    // 绑定数组参数
    protected static function bindArrayParams($sql, $params)
    {
        foreach ($params as $key => $item) {
            if (is_array($item)) {
                unset($params[$key]);
                $key = substr($key, 0, 1) == ':' ? $key : ":{$key}";
                $sql = str_replace($key, implode(', ', self::quotes($item)), $sql);
            }
        }
        return [$sql, $params];
    }

    // 清扫预处理数据
    protected function clearPrepare()
    {
        if ($this->_recordData) {
            $this->_sqlExecuteData[] = $this->getRawSql();
        }
        $this->_sql    = '';
        $this->_params = [];
        $this->_values = [];
    }

    // 自动连接
    protected function autoConnect()
    {
        if (!isset($this->_pdo)) {
            $this->connect();
        }
    }

    // 预处理
    protected function build()
    {
        // 自动连接
        $this->autoConnect();
        // 准备与参数绑定
        if (!empty($this->_params)) {
            // 有参数
            list($sql, $params) = self::bindArrayParams($this->_sql, $this->_params);
            $this->_pdoStatement   = $this->_pdo->prepare($sql);
            $this->_sqlPrepareData = [$sql, $params, []]; // 必须在 bindParam 前，才能避免类型被转换
            foreach ($params as $key => &$value) {
                $this->_pdoStatement->bindParam($key, $value);
            }
        } elseif (!empty($this->_values)) {
            // 批量插入
            $this->_pdoStatement   = $this->_pdo->prepare($this->_sql);
            $this->_sqlPrepareData = [$this->_sql, [], $this->_values];
            foreach ($this->_values as $key => $value) {
                $this->_pdoStatement->bindValue($key + 1, $value);
            }
        } else {
            // 无参数
            $this->_pdoStatement   = $this->_pdo->prepare($this->_sql);
            $this->_sqlPrepareData = [$this->_sql];
        }
    }

    /**
     * 返回结果集
     * @return \PDOStatement
     */
    public function query()
    {
        $this->build();
        $this->_pdoStatement->execute();
        $this->clearPrepare();
        return $this->_pdoStatement;
    }

    // 返回一行
    public function queryOne()
    {
        $this->build();
        $this->_pdoStatement->execute();
        $this->clearPrepare();
        return $this->_pdoStatement->fetch($this->_driverOptions[\PDO::ATTR_DEFAULT_FETCH_MODE]);
    }

    // 返回多行
    public function queryAll()
    {
        $this->build();
        $this->_pdoStatement->execute();
        $this->clearPrepare();
        return $this->_pdoStatement->fetchAll();
    }

    // 返回一列 (第一列)
    public function queryColumn($columnNumber = 0)
    {
        $this->build();
        $this->_pdoStatement->execute();
        $this->clearPrepare();
        $column = [];
        while ($row = $this->_pdoStatement->fetchColumn($columnNumber)) {
            $column[] = $row;
        }
        return $column;
    }

    // 返回一个标量值
    public function queryScalar()
    {
        $this->build();
        $this->_pdoStatement->execute();
        $this->clearPrepare();
        return $this->_pdoStatement->fetchColumn();
    }

    // 执行SQL语句
    public function execute()
    {
        $this->build();
        $success = $this->_pdoStatement->execute();
        $this->clearPrepare();
        return $success;
    }

    // 返回最后插入行的ID或序列值
    public function getLastInsertId()
    {
        return $this->_pdo->lastInsertId();
    }

    // 返回受上一个 SQL 语句影响的行数
    public function getRowCount()
    {
        return $this->_pdoStatement->rowCount();
    }

    // 插入
    public function insert($table, $data)
    {
        $keys   = array_keys($data);
        $fields = array_map(function ($key) {
            return ":{$key}";
        }, $keys);
        $sql    = "INSERT INTO `{$table}` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $fields) . ")";
        $this->prepare($sql);
        $this->bindParams($data);
        return $this;
    }

    // 批量插入
    public function batchInsert($table, $data)
    {
        $keys   = array_keys($data[0]);
        $sql    = "INSERT INTO `{$table}` (`" . implode('`, `', $keys) . "`) VALUES ";
        $fields = [];
        for ($i = 0; $i < count($keys); $i++) {
            $fields[] = '?';
        }
        $values    = [];
        $valuesSql = [];
        foreach ($data as $item) {
            foreach ($item as $value) {
                $values[] = $value;
            }
            $valuesSql[] = "(" . implode(', ', $fields) . ")";
        }
        $sql .= implode(', ', $valuesSql);
        $this->prepare($sql);
        $this->bindValues($values);
        return $this;
    }

    // 更新
    public function update($table, $data, $where)
    {
        $setSql = [];
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                list($operator, $value) = $item;
                $setSql[]   = "`{$key}` =  `{$key}` {$operator} :{$key}";
                $data[$key] = $value;
                continue;
            }
            $setSql[] = "`{$key}` = :{$key}";
        }
        $whereSql    = [];
        $whereParams = [];
        foreach ($where as $key => $value) {
            $whereSql[$key]                   = "`{$value[0]}` {$value[1]} :where_{$value[0]}";
            $whereParams["where_{$value[0]}"] = $value[2];
        }
        $sql = "UPDATE `{$table}` SET " . implode(', ', $setSql) . " WHERE " . implode(' AND ', $whereSql);
        $this->prepare($sql);
        $this->bindParams($data);
        $this->bindParams($whereParams);
        return $this;
    }

    // 删除
    public function delete($table, $where)
    {
        $whereParams = [];
        foreach ($where as $key => $value) {
            $where[$key]                = "`{$value[0]}` {$value[1]} :{$value[0]}";
            $whereParams["{$value[0]}"] = $value[2];
        }
        $sql = "DELETE FROM `{$table}` WHERE " . implode(' AND ', $where);
        $this->prepare($sql);
        $this->bindParams($whereParams);
        return $this;
    }

    // 自动事务
    public function transaction($closure)
    {
        $this->beginTransaction();
        try {
            $closure();
            // 提交事务
            $this->commit();
        } catch (\Throwable $e) {
            // 回滚事务
            $this->rollBack();
            throw $e;
        }
    }

    // 开始事务
    public function beginTransaction()
    {
        // 自动连接
        $this->autoConnect();
        // 开始事务
        return $this->_pdo->beginTransaction();
    }

    // 提交事务
    public function commit()
    {
        return $this->_pdo->commit();
    }

    // 回滚事务
    public function rollback()
    {
        return $this->_pdo->rollBack();
    }

    // 给字符串加单引号
    protected static function quotes($var)
    {
        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::quotes($v);
            }
            return $var;
        }
        return is_string($var) ? "'{$var}'" : $var;
    }

    // 返回原生SQL语句
    public function getRawSql()
    {
        $sqlPrepareData = $this->_sqlPrepareData;
        if (count($sqlPrepareData) > 1) {
            list($sql, $params, $values) = $sqlPrepareData;
            $values = self::quotes($values);
            $params = self::quotes($params);
            // 先处理 values，避免 params 中包含 ? 号污染结果
            $sql = vsprintf(str_replace('?', '%s', $sql), $values);
            // 处理 params
            foreach ($params as $key => $value) {
                $key = substr($key, 0, 1) == ':' ? $key : ":{$key}";
                $sql = str_replace($key, $value, $sql);
            }
            return $sql;
        }
        return array_shift($sqlPrepareData);
    }

    public function getExecuteData()
    {
        return $this->_sqlExecuteData;
    }

    /**
     * @param bool $recordData
     */
    public function setRecordData(bool $recordData): void
    {
        $this->_recordData = $recordData;
    }
}
