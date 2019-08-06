<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/5/31
 * Time: 10:11
 *
 * @var League\Plates\Template\Template $this
 * @var \apps\models\form\News\SearchForm $pager
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>News<?php $this->end();?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel">
            <div class="panel-heading">Searching Box of Site News</div>
            <div class="panel-body">
                <form method="get">
                    <div class="form-inline text-center">
                        <label>
                            <input type="text" class="form-control" name="search" style="width:450px" value="<?= $pager->search ?>">
                        </label>&nbsp;&nbsp;
                        <label> Range:
                            <select name="query" class="form-control">
                                <option value="title"<?= $pager->query == 'title' ? ' selected': '' ?>>Title</option>
                                <option value="body"<?= $pager->query == 'body' ? ' selected': '' ?>>Body</option>
                                <option value="both"<?= $pager->query == 'both' ? ' selected': '' ?>>Both</option>
                            </select>
                        </label>&nbsp;&nbsp;
                        <input type="submit" class="btn btn-primary" value="给我搜">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <?php if (app()->site->getCurUser()->isPrivilege('manage_news')): ?>
        <div style="margin-bottom: 5px">
            <div class="pull-right">
                <a class="btn btn-primary" href="/news/new">Add new Site News</a>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php endif; ?>

        <?php if (empty($pager->getPagerData())): ?>
            <div class="panel">
                <div class="panel-body">
                    <div class="text">Not find any news.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="panel-group">
                <?php foreach ($pager->getPagerData() as $i=> $new): ?>
                    <div class="panel" id="new_<?= $new['id'] ?>">
                        <div class="panel-heading">
                            <div class="pull-right">
                                <?php if ($new['create_at'] == $new['edit_at']): ?>
                                Post at: <span class="text-muted"><?= $new['create_at'] ?></span>
                                <?php else: ?>
                                Last Edit at: <span class="text-muted" data-toggle="tooltip" data-placement="bottom" title="Create at <?= $this->e($new['create_at']) ?>"><?= $new['edit_at'] ?></span>
                                <?php endif; ?>
                            </div>
                            <h4>
                                <a href="#new_<?= $new['id'] ?>"><?= $new['title'] ?></a>
                                <?php if (empty($search) && $i == 0): ?>
                                    <span class="label label-info">New</span>
                                <?php endif; ?>
                                <?php if ($new['force_read']): ?>
                                    <span class="label label-warning">Important</span>
                                <?php endif; ?>
                            </h4>
                        </div>
                        <div class="panel-body">
                            <div class="text"><?= $this->batch($new['body'], 'format_ubbcode'); ?></div>
                        </div>
                        <?php if (app()->site->getCurUser()->isPrivilege('manage_news')): ?>
                            <div class="panel-footer">
                                <a href="/news/edit?id=<?= $new['id'] ?>"><i class="icon-pencil"></i> Edit</a> &nbsp;
                                <a href="/news/delete?id=<?= $new['id'] ?>"><i class="icon-remove"></i> Delete</a> &nbsp;
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <ul class="pager" data-ride="remote_pager" data-rec-total="<?= $pager->getTotal() ?>"  data-rec-per-page="<?= $pager->getLimit() ?>"></ul>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->end();?>
