<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/15
 * Time: 19:57
 *
 * @var League\Plates\Template\Template $this
 * @var array $links
 */

$css_tag = env('APP_DEBUG') ? time() : config('base.site_css_update_date');
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Links Manager<?php $this->end();?>

<?php $this->start('container') ?>
<div class="row">
    <div class="text-center"><h2>Links Manager</h2></div>
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-heading">
                <div class="pull-right"><button class="btn btn-primary btn-sm" id="links_quick_add" data-target="#links_modal" data-toggle="modal">Add</button></div>
                All Links
            </div>
            <div class="panel-body">
                <table class="table" id="links_manager_table">
                    <thead>
                    <tr>
                        <td>Id</td>
                        <td>Link</td>
                        <td>Status</td>
                        <td>Contact</td>
                        <td>Reason</td>
                        <td>Modify</td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($links as $link):?>
                    <tr data-id="<?= $this->e($link['id']) ?>"
                        data-name="<?= $this->e($link['name']) ?>"
                        data-url="<?= $this->e($link['url']) ?>"
                        data-title="<?= $this->e($link['title']) ?>"
                        data-status="<?= $this->e($link['status']) ?>"
                        data-admin="<?= $this->e($link['administrator']) ?>"
                        data-email="<?= $this->e($link['email']) ?>"
                        data-reason="<?= $this->e($link['reason']) ?>"
                    >
                        <td><?= $this->e($link['id']) ?></td>
                        <td><nobr><a href="<?= $this->e($link['url']) ?>" target="_blank" data-toggle="tooltip" data-placement="right" title="<?= $this->e($link['title']) ?>"><?= $this->e($link['name']) ?></a></nobr></td>
                        <td><nobr>
                            <?php $label_style = ['pending' => ' label-info','enabled' => ' label-success','disabled' => ''][$link['status']]  ?>
                            <span class="label<?= $label_style ?>"><?= $this->e($link['status']) ?></span>
                            </nobr>
                        </td>
                        <td>
                            <nobr>
                                <span><?= $this->e($link['administrator']) ?> : <a href="mailto:<?= $this->e($link['email']) ?>"><?= $this->e($link['email']) ?></a></span>
                            </nobr>
                        </td>
                        <td><?= $this->e($link['reason']) ?></td>
                        <td><nobr><a href="javascript:" class="link-edit" data-id="<?= $this->e($link['id']) ?>">Edit</a> | <a href="javascript:" class="link-remove" data-id="<?= $this->e($link['id']) ?>">Remove</a></nobr></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $this->end();?>


<?php $this->start('body'); ?>
<form method="post" id="link_remove_form" class="hidden">
    <label><input type="text" name="action" value="link_delete"></label>
    <label><input type="number" name="link_id" value=""></label>
</form>

<div class="modal fade" id="links_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Add/Edit Links</h4>
            </div>
            <form method="post" class="form-horizontal" id="link_edit_form" data-toggle="validator" role="form">
            <div class="modal-body">
                <label class="hidden"><input type="text" name="action" value="link_edit"></label>
                <label class="hidden"><input type="number" id="link_id" name="link_id" value="0"></label>
                <div class="form-group">
                    <label for="link_name" class="col-sm-2 required">Link Name</label>
                    <div class="col-md-8 col-sm-10">
                        <input type="text" class="form-control" id="link_name" name="link_name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="link_url" class="col-sm-2 required">URL</label>
                    <div class="col-md-8 col-sm-10">
                        <input type="url" class="form-control" id="link_url" name="link_url" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="link_title" class="col-sm-2">Title</label>
                    <div class="col-md-8 col-sm-10">
                        <input type="text" class="form-control" id="link_title" name="link_title">
                    </div>
                </div>
                <div class="form-group">
                    <label for="link_status" class="col-sm-2 required">Status</label>
                    <div class="col-md-8 col-sm-10">
                        <select class="form-control" id="link_status" name="link_status" required>
                            <option value="pending">Pending</option>
                            <option value="enabled" selected>Enabled</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="link_admin" class="col-sm-2">Administrator</label>
                    <div class="col-md-8 col-sm-10">
                        <input type="text" class="form-control" id="link_admin" name="link_admin">
                    </div>
                </div>
                <div class="form-group">
                    <label for="link_email" class="col-sm-2">Email</label>
                    <div class="col-md-8 col-sm-10">
                        <input type="email" class="form-control" id="link_email" name="link_email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="link_reason" class="col-sm-2">Reason</label>
                    <div class="col-md-10 col-sm-10">
                        <textarea class="form-control" id="link_reason" name="link_reason" rows="10" placeholder=""></textarea>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="link_modal_close" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="link_modal_save">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('script'); ?>
<script src="/static/js/links_manage.js?<?= $css_tag ?>"></script>
<?php $this->end(); ?>
