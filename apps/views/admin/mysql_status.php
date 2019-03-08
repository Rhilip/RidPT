<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/27
 * Time: 21:10
 *
 * @var League\Plates\Template\Template $this
 * @var array $serverStatus
 * @var array $queryStats
 * @var int $startAt
 * @var array $rows
 */
?>

<?= $this->layout('admin/layout') ?>

<?php $this->start('title') ?>Mysql Server Status<?php $this->end(); ?>

<?php $this->start('panel') ?>
<h1>Mysql Server Status</h1>
<div>
    This MySQL server has been running for <code><?= $serverStatus['Uptime'] ?></code> Seconds.
    It started up on <code><?= date("M d, Y \\a\\t h:i A", $startAt) ?></code>
</div>
<br>
<div class="layui-collapse">
    <div class="layui-colla-item">
        <h2 class="layui-colla-title"> Server Traffic </h2>
        <div class="layui-colla-content layui-show">
            <small>These tables show the network traffic statistics of this MySQL server since its startup</small>
            <div class="layui-row">
                <div class="layui-col-md5">
                    <table class="layui-table" lay-even>
                        <thead>
                        <tr>
                            <th class="text-left">Traffic</th>
                            <th class="text-right">#</th>
                            <th class="text-right">ø Per Hour</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Received</td>
                            <td class="text-right"><?= $this->batch($serverStatus['Bytes_received'], 'format_bytes') ?></td>
                            <td class="text-right"><?= $this->batch($serverStatus['Bytes_received'] * 3600 / $serverStatus['Uptime'], 'format_bytes') ?></td>
                        </tr>
                        <tr>
                            <td>Sent</td>
                            <td class="text-right"><?= $this->batch($serverStatus['Bytes_sent'], 'format_bytes') ?></td>
                            <td class="text-right"><?= $this->batch($serverStatus['Bytes_sent'] * 3600 / $serverStatus['Uptime'], 'format_bytes') ?></td>
                        </tr>
                        <tr>
                            <td><b>Total</b></td>
                            <td class="text-right"><?= $this->batch($serverStatus['Bytes_received'] + $serverStatus['Bytes_sent'], 'format_bytes') ?></td>
                            <td class="text-right"><?= $this->batch(($serverStatus['Bytes_sent'] + $serverStatus['Bytes_sent']) * 3600 / $serverStatus['Uptime'], 'format_bytes') ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="layui-col-md7">
                    <table class="layui-table" lay-even>
                        <thead>
                        <tr>
                            <th class="text-left">Connections</th>
                            <th class="text-right">#</th>
                            <th class="text-right">ø Per Hour</th>
                            <th class="text-right">%</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Failed Attempts</td>
                            <td class="text-right"><?= number_format($serverStatus['Aborted_connects'], 0, '.', ',') ?></td>
                            <td class="text-right"><?= number_format($serverStatus['Aborted_connects'] * 3600 / $serverStatus['Uptime'], 2, '.', ',') ?></td>
                            <td class="text-right">
                                <?php if ($serverStatus['Connections'] > 0): ?>
                                    <?= number_format($serverStatus['Aborted_connects'] * 100 / $serverStatus['Connections'], 2, '.', ',') ?>
                                <?php else: ?> --- <?php endif ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Aborted Clients</td>
                            <td class="text-right"><?= number_format($serverStatus['Aborted_clients'], 0, '.', ',') ?></td>
                            <td class="text-right"><?= number_format($serverStatus['Aborted_clients'] * 3600 / $serverStatus['Uptime'], 2, '.', ',') ?></td>
                            <td class="text-right">
                                <?php if ($serverStatus['Connections'] > 0): ?>
                                    <?= number_format($serverStatus['Aborted_clients'] * 100 / $serverStatus['Connections'], 2, '.', ',') ?>
                                <?php else: ?> --- <?php endif ?>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Max. concurrent connections</b></td>
                            <td class="text-right"><?= number_format($serverStatus['Connections'], 0, '.', ',') ?></td>
                            <td class="text-right"><?= number_format($serverStatus['Connections'] * 3600 / $serverStatus['Uptime'], 2, '.', ',') ?></td>
                            <td class="text-right"><?= number_format(100, 2, '.', ',') ?> %</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-colla-item">
        <h2 class="layui-colla-title">Query Statistics</h2>
        <div class="layui-colla-content layui-show">
            <small>Since it's start up, <code><?= number_format($serverStatus['Questions'], 0, '.', ',') ?></code> queries
                have been sent to the server. <br>
                ø Per Hour : <code><?= number_format($serverStatus['Questions'] * 3600 / $serverStatus['Uptime'], 2, '.', ',') ?></code>
                ø Per Minute : <code><?= number_format($serverStatus['Questions'] * 60 / $serverStatus['Uptime'], 2, '.', ',') ?></code>
                ø Per Second : <code><?= number_format($serverStatus['Questions'] / $serverStatus['Uptime'], 2, '.', ',') ?></code>
            </small>
            <div class="layui-row">
                <?php foreach (array_chunk($queryStats, ceil(count($queryStats) / 2), true) as $rows): ?>
                    <div class="layui-col-md6">
                        <table class="layui-table" lay-even>
                            <thead>
                            <tr>
                                <th class="text-left">Query Type</th>
                                <th class="text-right">#</th>
                                <th class="text-right">ø Per Hour</th>
                                <th class="text-right">%</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows as $key => $value): ?>
                                <tr>
                                    <td><?= str_replace('_', ' ', $key) ?></td>
                                    <td class="text-right"><?= number_format($value, 0, '.', ',') ?></td>
                                    <td class="text-right"><?= number_format($value * 3600 / $serverStatus['Uptime'], 2, '.', ',') ?></td>
                                    <td class="text-right"><?= number_format(($value * 100 / ($serverStatus['Questions'] - $serverStatus['Connections'])), 2, '.', ',') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
    <div class="layui-colla-item">
        <h2 class="layui-colla-title">More status variables</h2>
        <div class="layui-colla-content layui-show">
            <div class="layui-row">
                <div class="layui-col-md12">
                    <table class="layui-table" lay-even>
                        <thead>
                        <tr>
                            <th width="70%" class="text-left">Variable</th>
                            <th class="text-left">Value</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($serverStatus as $key => $value): ?>
                            <tr>
                                <td><?= str_replace('_', ' ', $key) ?></td>
                                <td><?= $value ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->push('script') ?>
<script>
    layui.use(['element'],function () {

    });
</script>
<?php $this->end() ?>
