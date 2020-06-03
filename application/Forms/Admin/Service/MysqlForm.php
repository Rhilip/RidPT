<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 6:26 PM
 */

declare(strict_types=1);

namespace App\Forms\Admin\Service;


class MysqlForm
{

    private array $server_status;
    private float $start_at;
    private array $query_stats;


    public function __construct()
    {
        $res = container()->get('pdo')->prepare('SHOW GLOBAL STATUS')->queryAll();
        $serverStatus = array_column($res, 'Value', 'Variable_name');
        $startAt = container()->get('pdo')->prepare('SELECT UNIX_TIMESTAMP() - :uptime')->bindParams([
            'uptime' => $serverStatus['Uptime']
        ])->queryScalar();
        $queryStats = [];
        $tmp_array = $serverStatus;
        foreach ($tmp_array as $name => $value) {
            if (substr($name, 0, 4) == 'Com_') {
                $queryStats[substr($name, 4)] = $value;
                unset($serverStatus[$name]);
            }
        }

        $this->server_status = $serverStatus;
        $this->start_at = $startAt;
        $this->query_stats = $queryStats;
    }

    /**
     * @return array
     */
    public function getServerStatus(): array
    {
        return $this->server_status;
    }

    /**
     * @return float
     */
    public function getStartAt(): float
    {
        return $this->start_at;
    }

    /**
     * @return array
     */
    public function getQueryStats(): array
    {
        return $this->query_stats;
    }
}
