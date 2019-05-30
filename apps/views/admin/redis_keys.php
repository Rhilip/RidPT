<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/27
 * Time: 23:18
 * @var League\Plates\Template\Template $this
 *
 * @var array $keys
 * @var int $offset
 * @var int $perpage
 * @var array $types
 */

$type_dict = [
    \Redis::REDIS_STRING => 'String',
    \Redis::REDIS_LIST => 'List',
    \Redis::REDIS_HASH => 'Hash',
    \Redis::REDIS_SET => 'Set',
    \Redis::REDIS_ZSET => 'Zset'
];
?>

<?= $this->layout('admin/layout') ?>

<?php $this->start('title') ?>Redis Keys<?php $this->end(); ?>

<?php $this->start('panel') ?>
<h1>Redis Keys Status</h1>
<p>Please input the search pattern of keys, or your can use the search suggest</p>
<div class="row">
    <form id="search_redis" class="form-inline" method="get" action="/admin/service">
        <label><input name="provider" type="text" class="form-control" value="redis" style="display: none"></label>
        <label><input name="panel" type="text" class="form-control" value="keys" style="display: none"></label>
        <?php $pattern = $pattern ?? ''; ?>
        <div class="input-group" style="width: 600px;">
            <span class="input-group-addon">Search Keys</span>
            <label for="pattern"></label>
            <input id="pattern" name="pattern" type="text" class="form-control"<?= $pattern ? " placeholder=\"$pattern\" value=\"$pattern\"" : '' ?>>
            <span class="input-group-btn">
                <button type="submit" class="btn btn-default"><i class="fas fa-search fa-fw"></i> Search</button>
                <button type="reset" class="btn btn-danger"><i class="fas fa-times fa-fw"></i> Reset</button>
            </span>
        </div>
    </form>
    <?php $suggent_pattern = ['*', 'SESSION:*', 'TORRENT:*', 'TRACKER:*', 'USER:*'];  // FIXME fix this pattern group ?>
    <div id="suggest_pattern" style="margin-top: 5px">Suggest Pattern :
        <?php foreach ($suggent_pattern as $pat): ?>
            <a href="javascript:void(0);" data-pat="<?= $pat ?>"><span class="label label-badge label-primary label-outline"><?= $pat ?></span></a>&nbsp;&nbsp;
        <?php endforeach; ?>
    </div>
</div>

<?php if ($pattern != ''): ?>
    <hr>
    <div>
        Keys matching <code><?= $pattern ?></code>
        <div class="pull-right">
            (<strong><?= $num_keys ?? 0 ?></strong> out of <strong><?= $dbsize ?? 0 ?></strong> matched)
        </div>
        <div class="clearfix"></div>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th class="text-right" style="width: 5%">#</th>
            <th class="text-center" style="width: 10%">Type</th>
            <th class="text-left">Key</th>
            <th class="" style="width: 5%"></th>
        </tr>
        </thead>
        <tbody>
        <?php $index = 0; ?>
        <?php foreach ($keys as $key): ?>
            <tr>
                <td class="text-right"><?= $index + ($offset * $perpage) ?></td>
                <td class="text-center"><?= $type_dict[$types[$key]] ?></td>
                <td class="text-left">
                    <a href="/admin/service?provider=redis&panel=key&key=<?= $this->e($key) ?>"><?= $key ?></a>
                </td>
                <td class="text-right">
                    <form method="post">
                        <input type="hidden" name="action" value="delkey"/>
                        <input type="hidden" name="key" value="<?= $key ?>"/>
                        <button class="layui-btn layui-btn-sm layui-btn-danger" type="submit"
                                onclick="return confirm('Are you sure you want to delete this key? <?= $key ?>');">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php $this->end(); ?>

<?php $this->push('script') ?>
<script>
    $('#suggest_pattern a').click(function () {
        let pat = $(this).attr('data-pat');
        $('input[name="pattern"]').val(pat);
        $('#search_redis').submit();
    })
</script>
<?php $this->end() ?>
