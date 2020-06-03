<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/4
 * Time: 20:18
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Forms\Admin\Service\RedisForm $redis
 * @var array $info
 */

$info = $redis->getInfo();
?>

<?= $this->layout('admin/layout') ?>

<?php $this->start('title') ?>Redis Server Status<?php $this->end(); ?>

<?php $this->start('panel') ?>
<h1>Redis Server Status</h1>

<p><strong><?= $redis->getDbSize() ?> Keys available.</strong> Used Memory: <?= $info['used_memory_human'] ?> , peak: <?= $info['used_memory_peak_human'] ?></p>

<div class="panel-group">
    <div class="panel panel-info">
        <div class="panel-heading"> Overall Status </div>
        <div class="panel-body">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($info as $key => $value): ?>
                    <tr>
                        <td><?= $key ?></td>
                        <td><code><?= $value ?></code></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel panel-info">
        <div class="panel-heading"> Command Statistics </div>
        <div class="panel-body">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Command</th>
                    <th class="text-right">Calls</th>
                    <th class="text-right">Call Share</th>
                    <th class="text-right">Duration (Microseconds)</th>
                    <th class="text-right">Duration/Call</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($redis->getCmdStat() as $key => $value): ?>
                    <tr>
                        <td><code><?= str_replace('cmdstat_', '', $key) ?> </code></td>
                        <td class="text-right"><?= $value['calls'] ?></td>
                        <td class="text-right"><?= sprintf('%.2f', $value["calls"] / $info["total_commands_processed"] * 100) ?> %</td>
                        <td class="text-right"><?= $value['usec'] ?></td>
                        <td class="text-right"><?= sprintf('%.1f', $value['usec_per_call']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->end(); ?>

