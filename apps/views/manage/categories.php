<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/15/2019
 * Time: 10:40 PM
 * @var League\Plates\Template\Template $this
 * @var int $parent_id
 * @var array $parent_category
 * @var array $categories
 */

$css_tag = env('APP_DEBUG') ? time() : config('base.site_css_update_date');
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Manage Categories<?php $this->end(); ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-12">
        <h2>
            <?php if ($parent_id == 0) : ?>
                Manage Category
            <?php else : ?>
                Manage Sub Category of <kbd><?= $parent_category['name'] ?></kbd>
            <?php endif; ?>
        </h2>
    </div>
    <div class="col-md-12">
        <div class="pull-right">
            <button class="btn btn-primary" id="cat_add" data-target="#cat_modal" data-toggle="modal">Add</button>
        </div>
        <table class="table table-hover" id="cat_manager_table">
            <thead>
            <tr>
                <td>Id</td>
                <td>Sort Index</td>
                <td>Name</td>
                <td>Image</td>
                <td>Class Name</td>
                <td>Sub Category</td>
                <td>Enabled</td>
                <td>Action</td>
            </tr>
            </thead>
            <tbody>
            <?php if (count($categories) > 0): ?>
                <?php foreach ($categories as $category): ?>
                    <tr class="<?= $category['enabled'] ? '':'warning' ?>"
                        data-id="<?= $category['id'] ?>"
                        data-parent_id="<?= $category['parent_id'] ?>"
                        data-enabled="<?= $category['enabled'] ?>"
                        data-sort_index="<?= $category['sort_index'] ?>"
                        data-name="<?= $category['name'] ?>"
                        data-image="<?= $category['image'] ?>"
                        data-class_name="<?= $category['class_name'] ?>"
                    >
                        <td><?= $category['id'] ?></td>
                        <td><?= $category['sort_index'] ?></td>
                        <td><?= $category['name'] ?></td>
                        <td><?= $category['image'] ?></td>
                        <td><?= $category['class_name'] ?></td>
                        <td>
                            <a href="<?= '?parent_id=' . $category['id'] ?>"><?= $category['child_count'] > 0 ? "${category['child_count']} SubCategories" : 'No SubCategory' ?></a>
                        </td>
                        <td><?= $category['enabled'] ? '<i class="far fa-fw fa-check-square"></i>':'<i class="far fa-fw fa-square"></i>' ?></td>
                        <td><a href="javascript:" class="cat-edit" data-id="<?= $category['id'] ?>">Edit</a> |
                            <a href="javascript:" class="cat-remove" data-id="<?= $category['id'] ?>">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No Category Exist.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('body');?>
<form method="post" id="cat_remove_form" class="hidden">
    <label><input type="text" name="action" value="cat_delete"></label>
    <label><input type="number" name="cat_id" value=""></label>
</form>

<div class="modal fade" id="cat_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Add/Edit Category</h4>
            </div>
            <form method="post" class="form-horizontal" id="cat_edit_form" data-toggle="validator" role="form">
                <div class="modal-body">
                    <label class="hidden"><input type="text" name="action" value="cat_edit"></label>
                    <label class="hidden"><input type="number" id="cat_id" name="cat_id" value="0"></label>
                    <label class="hidden"><input type="number" id="cat_parent_id" name="cat_parent_id" value="<?= $this->e($parent_id) ?>"></label>
                    <div class="form-group">
                        <label for="cat_name" class="required">Name</label>
                        <input type="text" class="form-control" id="cat_name" name="cat_name" required>
                        <div class="help-block">Don't use long name. Recommend less than 10 letters.</div>
                    </div>
                    <div class="form-group">
                        <div class="switch text-left">
                            <input type="checkbox" id="cat_enabled" name="cat_enabled" value="1" checked>
                            <label for="cat_enabled">Enabled</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="cat_sort_index">Sort Index</label>
                        <input type="number" class="form-control" id="cat_sort_index" name="cat_sort_index" value="0">
                        <div class="help-block">Ascendantly, i.e. '0' comes first.</div>
                    </div>
                    <div class="form-group">
                        <label for="cat_image">Image</label>
                        <input type="text" class="form-control" id="cat_image" name="cat_image"
                               pattern="^[a-z0-9_./]*$">
                        <div class="help-block">The name of image file.Leave it blank to show Text Only. <code>Allowed Characters: [a-z] (in lower case), [0-9], [_./].</code></div>
                    </div>
                    <div class="form-group">
                        <label for="cat_class_name">Class Name</label>
                        <input type="text" class="form-control" id="cat_class_name" name="cat_class_name"
                               pattern="^[a-z][a-z0-9_\-]*?$">
                        <div class="help-block">The value of 'class' attribute of the image. Leave it blank if none. <code>Allowed Characters: [a-z] (in lower case), [0-9], [-_], and the first letter must be in [a-z].</code></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="cat_modal_close" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="cat_modal_save">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('script'); ?>
    <script src="/static/js/manage_categories.js?<?= $css_tag ?>"></script>
<?php $this->end(); ?>
