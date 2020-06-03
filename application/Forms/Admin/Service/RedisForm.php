<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 6:17 PM
 */

declare(strict_types=1);

namespace App\Forms\Admin\Service;

class RedisForm
{
    private array $info;
    private int $db_size;
    private array $cmd_stat;

    public function __construct()
    {
        /** @var array $raw_info */
        $raw_info = container()->get('redis')->info();
        $this->info = array_filter($raw_info, function ($key) {
            return strpos($key, 'cmdstat_') === false;
        }, ARRAY_FILTER_USE_KEY);

        $this->db_size = container()->get('redis')->dbSize();

        /** @var array $cmdstat_raw */
        $cmdstat_raw = container()->get('redis')->info('commandstats');

        $this->cmd_stat = array_map(function ($v) {
            preg_match('/calls=(?P<calls>\d+),usec=(?P<usec>\d+),usec_per_call=(?P<usec_per_call>[\d\.]+)/', $v, $m);
            return $m;
        }, $cmdstat_raw);
    }

    /**
     * @return array
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * @return int
     */
    public function getDbSize(): int
    {
        return $this->db_size;
    }

    /**
     * @return array
     */
    public function getCmdStat(): array
    {
        return $this->cmd_stat;
    }
}
