<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/6
 * Time: 22:05
 *
 * @var League\Plates\Template\Template $this
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Upload Torrent<?php $this->end(); ?>

<?php $this->start('container') ?>
<h1>Upload Torrent</h1>

<form class="layui-form" method="post" enctype="multipart/form-data">
    <div class="layui-form-item">
        <label class="layui-form-label" for="title">Title</label>
        <div class="layui-input-block">
            <input class="layui-input" id="title" name="title" placeholder="The main title of Your upload torrent" type="text"
                   required="required" lay-verify="required">
            <small>You should obey our upload rules.</small>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" for="subtitle">Sub Title</label>
        <div class="layui-input-block">
            <input class="layui-input" id="subtitle" name="subtitle" placeholder="The subtitle of Your upload torrent"
                   type="text">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" for="file">Torrent File</label>
        <div class="layui-input-block">
            <input class="layui-input" id="file" name="file" accept=".torrent" type="file" required="required">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" for="descr">Description</label>
        <div class="layui-input-block">
            <textarea class="layui-textarea" id="descr" name="descr" required="required" rows="10"></textarea>
        </div>
    </div>

    <?php if (app()->user->getClass(true) > app()->config->get('authority.upload_anonymous')) : ?>
        <div class="layui-form-item">
            <label class="layui-form-label" for="uplver"> Upload Anonymous</label>
            <div class="layui-input-block">
                <input class="layui-checkbox" id="uplver" type="checkbox" name="uplver" value="yes" lay-skin="switch">
            </div>
        </div>
    <?php endif; ?>

    <div class="layui-form-item text-center">
        <button type="submit" value="Upload" class="layui-btn layui-btn-radius layui-btn-normal">Upload</button>
        <button type="reset" value="Reset" class="layui-btn layui-btn-radius layui-btn-danger">Reset</button>
    </div>
</form>
<?php $this->end(); ?>
