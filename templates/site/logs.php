<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/17/2019
 * Time: 2019
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Models\Form\Site\Logs $logs
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Site Logs<?php $this->end(); ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-heading">Search Daily Log</div>
            <div class="panel-body">
                <form class="form form-inline" method="get">
                    <div class="form-group">
                        <label for="query"></label>
                        <input id="query" class="form-control col-md-6" type="text" name="query" value="<?= $this->e(app()->request->query->get('query')) ?>" style="width: 500px">
                    </div>
                    <div class="form-group">
                        <label for="level"> In </label>
                        <select id="level" class="form-control" name="level">
                            <option value="all"<?= app()->request->query->get('level') == 'all' ? ' selected' : '' ?>>all</option>
                            <option value="normal"<?= app()->request->query->get('level') == 'normal' ? ' selected' : '' ?>>normal</option>
                            <?php if (app()->auth->getCurUser()->isPrivilege('see_site_log_mod')): ?>
                                <option value="mod"<?= app()->request->query->get('level') == 'mod' ? ' selected' : '' ?>>mod</option>
                            <?php endif; ?>
                            <?php if (app()->auth->getCurUser()->isPrivilege('see_site_log_leader')): ?>
                                <option value="leader"<?= app()->request->query->get('level') == 'leader' ? ' selected' : '' ?>>leader</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">Other rules</div>
            <div class="panel-body">
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Time</th>
                        <th style="width: 99%;">Event</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs->getPagerData() as $log) : ?>
                    <tr>
                        <td><span class="nowrap"><?= $log['create_at'] ?></span></td>
                        <td><?= $this->e($log['msg']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="text-center">
                    <ul class="pager pager-unset-margin" data-ride="remote_pager" data-rec-total="<?= $logs->getTotal() ?>" data-rec-per-page="<?= $logs->getLimit() ?>"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>

