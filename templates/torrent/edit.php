<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 10/2/2019
 * Time: 2019
 *
 * @var League\Plates\Template\Template $this
 * @var App\Models\Form\Torrent\EditForm $edit
 */

$torrent = $edit->getTorrent();

use App\Entity\Torrent\TorrentStatus;

?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Edit Torrent<?php $this->end(); ?>

<?php $this->start('container') ?>
<h3>Edit Torrent</h3>

<form id="torrent_edit" class="form form-horizontal" method="post" enctype="multipart/form-data">
    <table id="torrent_edit_table" class="table table-bordered table-striped">
        <tbody>
        <tr>
            <td class="nowrap"><label for="category" class="required">Category</label></td>
            <td>
                <div class="row">
                    <div class="col-md-3">
                        <select id="category" name="category" class="form-control">
                            <option value="0" selected>[Select a category]</option>
                            <?php foreach (app()->site->ruleCategory() as $category) : ?>
                                <option value="<?= $category['id'] ?>" <?= $torrent->getCategoryId() == $category['id'] ? 'selected' : ''?>><?= $category['full_path'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td class="nowrap"><label for="title" class="required">Title</td>
            <td><input id="title" name="title" class="form-control" type="text"
                       required="required" value="<?= $torrent->getTitle() ?>">
            </td>
        </tr>
        <?php if (config('torrent_upload.enable_subtitle')): ?>
            <tr>
                <td class="nowrap"><label for="subtitle">Sub Title</label></td>
                <td><input id="subtitle" name="subtitle" class="form-control" type="text"
                           value="<?= $torrent->getSubtitle() ?>">
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <td class="nowrap"><label>Quality</label></td>
            <td>
                <div class="row">
                    <?php foreach (app()->site->getQualityTableList() as $quality => $title): ?>
                        <?php if (config('torrent_upload.enable_quality_' . $quality)) : ?>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-addon"><label for="<?= $quality ?>"><?= $title ?></label></span>
                                    <select class="form-control" id="<?= $quality ?>" name="<?= $quality ?>">
                                        <option value="0">[Choose One]</option>
                                        <?php foreach (app()->site->ruleQuality($quality) as $q): ?>
                                            <option value="<?= $q['id']; ?>"
                                                <?= $torrent->getQualityId($quality) ==$q['id'] ? 'selected' : '' ?>
                                            ><?= $q['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </td> <!-- FIXME link url -->
        </tr>
        <tr>
            <td class="nowrap"><label>Content</label></td>
            <td>
                <div class="row">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon"><label for="team">Group</label></span>
                            <select id="team" name="team" class="form-control">
                                <option value="0" selected>[Choose One]</option>
                                <?php foreach (app()->site->ruleTeam() as $team) : ?>
                                    <?php if (app()->auth->getCurUser()->getClass() >= $team['class_require']): ?>
                                        <option value="<?= $team['id'] ?>" <?= $torrent->getTeamId() == $team['id'] ? 'selected' : '' ?>><?= $team['name'] ?></option>
                                    <?php endif ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </td> <!-- FIXME link url -->
        </tr>
        <?php if (
            config('torrent_upload.enable_upload_nfo')
            && app()->auth->getCurUser()->isPrivilege('upload_nfo_file')
        ): ?>
        <!-- TODO fix  -->
            <tr>
                <td class="nowrap"><label for="nfo">NFO File</label></td>
                <td>
                    <div class="row" id="torrent_nfo_edit">
                        <div class="col">
                            <label class="radio-inline">
                                <input type="radio" name="nfo_action" value="keep" checked> Keep Old
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="nfo_action" value="remove"> Remove
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="nfo_action" value="update"> Update
                            </label>
                        </div>

                        <div class="col-md-3">
                            <input id="torrent_nfo" name="nfo" class="form-control" type="file" style="display: none"
                                   accept=".nfo, .txt"> <!-- FIXME original-title -->
                        </div>
                    </div>
            </tr>
        <?php endif ?>
        <tr>
            <td class="nowrap"><label for="links">Links</label></td>
            <td>
                <textarea id="links" name="links" class="form-control" style="width: 99%"
                          cols="100" rows="1" data-autoresize><!-- TODO --></textarea>
            </td>
        </tr>
        <tr>
            <td class="nowrap"><label for="descr" class="required">Description</label></td>
            <td>
                <textarea id="descr" name="descr" class="form-control to-load-editor" style="width: 99%"
                          cols="100" rows="10"><?= $torrent->getDescr() ?></textarea>
            </td>
        </tr>
        <?php if (config('torrent_upload.enable_tags')):?>
            <tr>
                <td class="nowrap"><label for="tags">Tags</label></td>
                <td>
                    <input id="tags" name="tags" class="form-control" type="text" value="<?= implode(' ', $torrent->getTags()) ?>">
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <td class="nowrap"><label for="descr">Flags</label></td>
            <td>
                <div class="row">
                    <div class="col-md-3">
                        <div class="switch<?= app()->auth->getCurUser()->isPrivilege('upload_flag_anonymous') ? '' : ' disabled' ?>">
                            <input type="checkbox" id="anonymous" name="anonymous" value="1" <?= $torrent->getUplver() ? ' checked' : '' ?>><label for="anonymous">Anonymous Upload</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="switch<?= app()->auth->getCurUser()->isPrivilege('upload_flag_hr') ? '' : ' disabled'  // FIXME Config key?>">
                            <input type="checkbox" id="hr" name="hr" value="1" <?= $torrent->getHr() ? ' checked' : '' ?>><label for="hr">H&R</label>
                        </div>
                    </div>
                </div>

            </td>
        </tr>
        <?php if (app()->auth->getCurUser()->isPrivilege('manage_torrents')): ?>
        <tr>
            <td class="nowrap"><label>Manage</label></td>
            <td>
                <div class="row">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon"><label for="status">Status</label></span>
                            <select id="status" name="status" class="form-control">
                                <?php foreach (TorrentStatus::TORRENT_STATUSES as $status) : ?>
                                    <option value="<?= $status ?>" <?= $torrent->getStatus() == $status ? 'selected' : '' ?>><?= $status ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </td> <!-- FIXME link url -->
        </tr>
        <?php endif; ?>
        </tbody>
    </table>
    <div class="text-center" style="margin-bottom:30px;">
        <button type="submit" value="Edit" class="btn btn-success">Edit</button>
        <button type="reset" value="Reset" class="btn btn-danger">Reset</button>
    </div>
</form>
<?php $this->end(); ?>

<?php $this->push('script'); ?>
<script>
    jQuery(document).ready(function () {
        $('#torrent_nfo_edit input[type=radio][name=nfo_action]').on('change', function () {
            let val = $(this).val();
            console.log(val);
            if (val === 'update') {
                $('#torrent_nfo').show()
            }else {
                $('#torrent_nfo').hide()
            }
        })
    });
</script>
<?php $this->end();?>

