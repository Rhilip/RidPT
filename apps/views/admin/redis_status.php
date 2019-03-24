<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/4
 * Time: 20:18
 *
 * @var League\Plates\Template\Template $this
 * @var array $info
 * @var array $cmdstat
 * @var int $dbsize
 */

?>

<?= $this->layout('admin/layout') ?>

<?php $this->start('title') ?>Redis Server Status<?php $this->end(); ?>

<?php $this->start('panel') ?>
<h1>Redis Server Status</h1>

<p>Used Memory: <?= $info['used_memory_human'] ?> , peak: <?= $info['used_memory_peak_human'] ?></p>

<p><strong><?= $dbsize ?> Keys available.</strong></p>

<div class="layui-collapse">
    <div class="layui-colla-item">
        <h2 class="layui-colla-title"> Overall Status </h2>
        <div class="layui-colla-content layui-show">
            <table class="layui-table">
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($info as $key => $value): ?>
                    <?php if (strpos($key, 'cmdstat_') === false): ?>
                        <tr>
                            <td><?= $key ?></td>
                            <td><code><?= $value ?></code></td>
                        </tr>
                    <?php endif ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="layui-colla-item">
        <h2 class="layui-colla-title"> Command Statistics </h2>
        <div class="layui-colla-content layui-show">
            <table class="layui-table">
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
                <?php foreach ($cmdstat as $key => $value): ?>
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

