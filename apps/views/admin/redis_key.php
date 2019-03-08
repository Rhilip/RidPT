<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/27
 * Time: 21:51
 *
 * @var League\Plates\Template\Template $this
 * @var string $key
 * @var array $value
 * @var string $type
 * @var int $size
 * @var int $ttl
 * @var int $expiration
 */
?>

<?= $this->layout('admin/layout') ?>

<?php $this->start('title') ?>Redis Key: <?= $this->e($key) ?> Status<?php $this->end(); ?>

<?php $this->start('panel') ?>
<h1>Key: <code><?= $key ?></code></h1>
<table class="layui-table">
    <tbody>
    <tr>
        <th>Key</th>
        <td><input type="text" value="<?= $key ?>" readonly="readonly"
                   style="border: none; width: 100%; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace;"/>
        </td>
    </tr>
    <tr>
        <th>Type</th>
        <td><code><?= $type ?></code></td>
    </tr>
    <tr>
        <th>Size</th>
        <td><?= $size ?> Bytes (<?= $this->batch($size, 'format_bytes') ?>)
        </td>
    </tr>
    <tr>
        <th>Expiration</th>
        <td>
            <?php if ($ttl < 0): ?>
                <span class="label label-warning">No expiration set</span>
            <?php else: ?>
                <code><?= $ttl ?></code> Seconds from now (<code><?= date('Y-m-d h:i:s', $expiration) ?></code>)
            <?php endif; ?>
        </td>
    </tr>
    </tbody>
</table>
<?php if ($type == \Redis::REDIS_STRING): ?>
    <h2>String Value</h2>
    <figure>
        <pre><code><?= json_encode($value, JSON_PRETTY_PRINT) ?></code></pre>
    </figure>
<?php elseif ($type == \Redis::REDIS_LIST): ?>
    <h2>Values</h2>
    <ol>
        <?php foreach ($value as $item): ?>
            <li><code><?= $item ?></code></li>
        <?php endforeach; ?>
    </ol>
<?php elseif ($type == \Redis::REDIS_HASH): ?>
    <h2>Hash keys and values</h2>
    <table class="layui-table">
        <thead>
        <tr>
            <th></th>
            <th>Key</th>
            <th>Value</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($value as $k => $v): ?>
            <tr>
                <td></td>
                <td><code><?= $k ?></code></td>
                <td><code><?= $v ?></code></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif ($type == \Redis::REDIS_SET): ?>
    <h2>Values</h2>
    <ul>
        <?php foreach ($value as $item): ?>
            <li><code><?= $item ?></code></li>
        <?php endforeach; ?>
    </ul>
<?php elseif ($type == \Redis::REDIS_ZSET): ?>
    <h2>Sorted set entries</h2>
    <table class="layui-table">
        <thead>
        <tr>
            <th>Rank</th>
            <th>Score</th>
            <th>Value</th>
        </tr>
        </thead>
        <tbody>
        <?php $index = 0 ?>
        <?php foreach ($value as $k => $v): ?>
            <tr>
                <td><?= $index ?></td>
                <td><code><?= $v ?></code></td>
                <td><code><?= $k ?></code></td>
            </tr>
            <?php $index++ ?>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<?php $this->end(); ?>