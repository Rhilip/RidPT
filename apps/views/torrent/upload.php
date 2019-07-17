<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/6
 * Time: 22:05
 *
 * @var League\Plates\Template\Template $this
 * @var array $categories
 *
 * TODO Add notice for users which can't directly upload torrent (in pending status)
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Upload Torrent<?php $this->end(); ?>

<?php $this->start('container') ?>
<h3>Upload Torrent</h3>

<form id="torrent_upload" class="form form-horizontal" method="post" enctype="multipart/form-data">
    <table id="torrent_upload_table" class="table table-bordered table-striped">
        <tbody>
        <tr>
            <td class="nowrap"><label for="category" class="required">Category</label></td>
            <td>
                <div class="row">
                    <div class="col-md-3">
                        <select id="category" name="category" class="form-control">
                            <option value="0" selected>Please choose one Category</option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?= $category['id'] ?>"><?= $category['full_path'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td class="nowrap"><label for="file" class="required">Torrent File</label></td>
            <td>
                <input id="torrent_file" name="file" class="form-control" type="file"
                       accept=".torrent" required="required"
                       data-toggle="tooltip"
                       data-original-title="可直接上传从其它PT站下载的torrent文件"> <!-- FIXME original-title -->
                <span id="torrent_file_name"></span>
        </tr>
        <tr>
            <td class="nowrap"><label for="title" class="required">Title</td>
            <td><input id="title" name="title" class="form-control" type="text"
                       placeholder="The main title of Your upload torrent"
                       required="required">
                <small>You should obey our upload rules. **LINK**</small>
            </td> <!-- FIXME link url -->
        </tr>
        <tr>
            <td class="nowrap"><label for="subtitle">Sub Title</label></td>
            <td><input id="subtitle" name="subtitle" class="form-control" type="text"
                       placeholder="The subtitle of Your upload torrent">
                <small>You should obey our upload rules. **LINK**</small>
            </td> <!-- FIXME link url -->
        </tr>

        <tr>
            <td class="nowrap"><label for="descr" class="required">Description</label></td>
            <td>
                    <textarea id="descr" name="descr" class="form-control" style="width: 99%"
                              cols="100" rows="10" required="required"></textarea>
            </td>
        </tr>
        <tr>
            <td class="nowrap"><label for="descr">Other</label></td>
            <td>
                <div class="checkbox-primary">
                    <input type="checkbox" id="uplver" name="uplver" value="yes"
                        <?= app()->user->getClass(true) > config('authority.upload_anonymous') ? '' : ' disabled' ?>
                    ><label for="uplver">Anonymous Upload</label>
                </div>
                <div class="checkbox-primary">
                    <input type="checkbox" id="hr" name="hr" value="yes"
                        <?= app()->user->getClass(true) > config('authority.upload_anonymous') ? '' : ' disabled' // FIXME  ?>
                    ><label for="hr">H&R</label>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="text-center" style="margin-bottom:30px;">
        <button type="submit" value="Upload" class="btn btn-success">Upload</button>
        <button type="reset" value="Reset" class="btn btn-danger">Reset</button>
    </div>
</form>
<?php $this->end(); ?>
