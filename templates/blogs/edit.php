<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/5/31
 * Time: 10:12
 *
 * @var League\Plates\Template\Template $this
 * @var array $blog
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Site Blog Edit<?php $this->end();?>

<?php $this->start('container') ?>
<div class="row">
    <div class="text-center"><h2>New/Edit Site Blog</h2></div>
    <div class="col-md-8 col-md-offset-2">
        <form method="post">
            <?php if (isset($blog)): ?>
                <label>
                    <input name="id" value="<?= $blog['id'] ?>" style="display: none">
                </label>
            <?php endif; ?>
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="The title of Blog" value="<?= isset($blog) ? $blog['title'] : '' ?>">
            </div>
            <div class="form-group">
                <label for="body">Body</label>
                <textarea class="form-control" id="body" name="body" cols="100" rows="20" placeholder=""><?= isset($blog) ? $blog['body'] : '' ?></textarea>
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="notify" value="1"<?= isset($blog) && $blog['notify'] ? ' checked' : '' ?>> Notify All Member.
                    </label>
                </div><!-- TODO add support -->
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="force_read" value="1"<?= isset($blog) && $blog['force_read'] ? ' checked' : '' ?>> All Member <b>MUST</b> Read this Blog, Before they can do other things!!!
                    </label>
                </div><!-- TODO add support -->
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
<?php $this->end();?>
