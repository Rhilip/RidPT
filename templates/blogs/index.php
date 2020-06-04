<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/5/31
 * Time: 10:11
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Forms\Blogs\SearchForm $pager
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>News<?php $this->end();?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel">
            <div class="panel-heading">Searching Box of Site Blogs</div>
            <div class="panel-body">
                <form method="get">
                    <div class="form-inline text-center">
                        <label>
                            <input type="text" class="form-control" name="search" style="width:450px" value="<?= $pager->getInput('search') ?>">
                        </label>&nbsp;&nbsp;
                        <label> Range:
                            <select name="field" class="form-control">
                                <option value="title"<?= $pager->getInput('field') == 'title' ? ' selected': '' ?>>Title</option>
                                <option value="body"<?= $pager->getInput('field') == 'body' ? ' selected': '' ?>>Body</option>
                                <option value="both"<?= $pager->getInput('field') == 'both' ? ' selected': '' ?>>Both</option>
                            </select>
                        </label>&nbsp;&nbsp;
                        <input type="submit" class="btn btn-primary" value="Search">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <?php if (container()->get('auth')->getCurUser()->isPrivilege('manage_news')): ?>
        <div style="margin-bottom: 5px">
            <div class="pull-right">
                <a class="btn btn-primary" href="/blogs/create">Add new Site Blog</a>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php endif; ?>

        <?php if (empty($pager->getPaginationData())): ?>
            <div class="panel">
                <div class="panel-body">
                    <div class="text">Not find any Blogs.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="panel-group">
                <?php foreach ($pager->getPaginationData() as $i=> $blog): ?>
                    <div class="panel" id="blog_<?= $blog['id'] ?>">
                        <div class="panel-heading">
                            <div class="pull-right">
                                <?php if ($blog['create_at'] == $blog['edit_at']): ?>
                                Post at: <span class="text-muted"><?= $blog['create_at'] ?></span>
                                <?php else: ?>
                                Last Edit at: <span class="text-muted" data-toggle="tooltip" data-placement="bottom" title="Create at <?= $this->e($blog['create_at']) ?>"><?= $blog['edit_at'] ?></span>
                                <?php endif; ?>
                            </div>
                            <h4>
                                <a href="#blog_<?= $blog['id'] ?>"><?= $blog['title'] ?></a>
                                <?php if (empty($search) && $i == 0): ?>
                                    <span class="label label-info">New</span>
                                <?php endif; ?>
                                <?php if ($blog['force_read']): ?>
                                    <span class="label label-warning">Important</span>
                                <?php endif; ?>
                            </h4>
                        </div>
                        <div class="panel-body">
                            <div class="text"><?= $this->batch($blog['body'], 'format_ubbcode'); ?></div>
                        </div>
                        <?php if (container()->get('auth')->getCurUser()->isPrivilege('manage_news')): ?>
                            <div class="panel-footer">
                                <a href="/blogs/edit?id=<?= $blog['id'] ?>"><i class="icon-pencil"></i> Edit</a> &nbsp;
                                <a href="/blogs/delete?id=<?= $blog['id'] ?>"><i class="icon-remove"></i> Delete</a> &nbsp;
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <ul class="pager" data-ride="remote_pager" data-rec-total="<?= $pager->getPaginationTotal() ?>" data-rec-per-page="<?= $pager->getPaginationLimit() ?>"></ul>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->end();?>
