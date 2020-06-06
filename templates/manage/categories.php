<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/15/2019
 * Time: 10:40 PM
 * @var League\Plates\Template\Template $this
 * @var \App\Forms\Manage\Categories\IndexForm $form
 */

$categories = $form->getCategories();
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Manage Categories<?php $this->end(); ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-12">
        <h2>Manage Category</h2>
    </div>
    <div class="col-md-12">
        <div class="pull-right">
            <button class="btn btn-primary" id="cat_add" data-target="#cat_modal" data-toggle="modal">Add</button>
        </div>
        <table class="table table-hover" id="cat_manager_table">
            <thead>
            <tr>
                <th width="15px"></th>
                <th>Id</th>
                <th>Name</th>
                <th>Image</th>
                <th>Class Name</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($categories) > 0): ?>
                <?php foreach ($categories as $category): ?>
                    <tr data-id="<?= $category['id'] ?>"
                        data-enabled="<?= $category['enabled'] ?>"
                        data-name="<?= $category['name'] ?>"
                        data-image="<?= $category['image'] ?>"
                        data-class_name="<?= $category['class_name'] ?>"
                        data-sort_index="<?= $category['sort_index'] ?>"
                    >
                        <td><?= $category['enabled'] ? '<i class="far fa-fw fa-check-square"></i>':'<i class="far fa-fw fa-square"></i>' ?></td>
                        <td><?= $category['id'] ?></td>
                        <td><b><?= $category['name'] ?></b></td>
                        <td><?= $category['image'] ?></td>
                        <td><?= $category['class_name'] ?></td>
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
<div class="modal fade" id="cat_delete_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Add/Edit Category</h4>
            </div>
            <form action="/manage/categories/delete" method="post" class="form-horizontal" id="cat_remove_form" data-toggle="validator" role="form">
                <div class="modal-body">
                <label class="hidden"><input type="number" id="id" name="id" value="0"></label>
                    <div class="form-group">
                        <label for="move_to" class="required">Move To</label>
                        <select class="form-control" id="move_to" name="move_to">
                            <option value="">[Choose One Category]</option>
                            <?php foreach (container()->get('site')->ruleCategory() as $id => $category): ?>
                                <option value="<?= $id ?>"><?= $category['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="help-block">Since you delete this Category, You MUST set another category for
                            torrent to update.
                        </div>
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



<div class="modal fade" id="cat_edit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Add/Edit Category</h4>
            </div>
            <form action="/manage/categories/edit" method="post" class="form-horizontal" id="cat_edit_form" data-toggle="validator" role="form">
                <div class="modal-body">
                    <label class="hidden"><input type="number" id="id" name="id" value="0"></label>
                    <div class="form-group">
                        <label for="name" class="required">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="help-block">Don't use long name. Recommend less than 10 letters.</div>
                    </div>
                    <div class="form-group">
                        <div class="switch text-left">
                            <input type="checkbox" id="enabled" name="enabled" value="1" checked>
                            <label for="enabled">Enabled</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="text" class="form-control" id="image" name="image"
                               pattern="^[a-z0-9_./]*$">
                        <div class="help-block">The name of image file.Leave it blank to show Text Only. Allowed Characters: <code>[a-z] (in lower case), [0-9], [_./].</code></div>
                    </div>
                    <div class="form-group">
                        <label for="class_name">Class Name</label>
                        <input type="text" class="form-control" id="class_name" name="class_name"
                               pattern="^[a-z][a-z0-9_\-]*?$">
                        <div class="help-block">The value of 'class' attribute of the image. Leave it blank if none. Allowed Characters: <code>[a-z] (in lower case), [0-9], [-_], and the first letter must be in [a-z].</code></div>
                    </div>
                    <div class="form-group">
                        <label for="sort_index">Sort Index</label>
                        <input type="number" class="form-control" id="sort_index" name="sort_index" value="0">
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
