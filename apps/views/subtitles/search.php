<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/8/2019
 * Time: 9:36 PM
 *
 * @var League\Plates\Template\Template $this
 * @var \apps\models\form\Subtitles\SearchForm $search
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Subtitle<?php $this->end(); ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel" id="subs_upload_panel">
            <div class="panel-heading text-center"><a href="#subs_upload_body" data-toggle="collapse">Upload Subtitles</a> - total uploaded <span id="total_subs_size"><?= $this->e($search->getSubsSizeSum(),'format_bytes') ?></span></div>
            <div class="panel-body collapse" id="subs_upload_body">
                <div class="col-md-12" id="subs_upload_rules">
                    <p>Rules:</p>
                    <ol>
                        <li>Before upload subtitle please search at subtitles page to confirm your subtitle is no exist, please do not upload duplicate subtitles!</li>
                        <li>Upload subtitle must be synchronized with the video (which requires that you have the video file and checked the subtitles)! Identified or membership report, 10% of the entire timeline subtitles portion subtitles in more than 0.3s (can clearly feel error) it will be rejected and be deleted.</li>
                        <li>The film collection of subtitles please upload packaged as zip or rar, do not separate upload.</li>
                        <li>Name of the rule: <br>
                            Example: One.Night.In.Taipei.2015.BluRay.720p.DD5.1.x264-HDBiger.chs <br>
                            Format:<br>
                            <ol>
                                <li>The name must correspond subtitles video file name within the same seed [in principle is not recommended for video name is Chinese, no English name only some video captions allow the use of Chinese].</li>
                                <li>different languages are required after the file name added language identifier, such as Simplified Chinese is .chs, Traditional Chinese is .cht, English .eng, Japanese .jp like. In this case, to select the corresponding language and subtitle types.</li>
                                <li>multilingual subtitles, such as Jane English subtitles, adding the language identifier .chs & eng, join the rest of similar identifier. In this case, the language type selected for Other</li>
                                <li>allowed to upload subtitle format ass / ssa / srt</li>
                                <li>If you previously uploaded subtitle timeline there is something wrong when you upload subtitles proper (correct version) or re-synced (recalibrate) the subtitles, please add at the end of the subtitle file [PROPER] or [R3]. Example: One.Night.In.Taipei.2015.BluRay.720p.DD5.1.x264-HDBiger.chs [PROPER]</li>
                            </ol>
                        </li>
                        <li>Upload subtitle must meet all the above requirements, otherwise it will be deleted, and the corresponding bonus will be take back. Upload failed for multiple subtitles (malicious upload) members or malicious prosecution subtitles others members will be given a warning, serious case account will be banned.</li>
                        <li>25 bonus for each subtitle uploaded, and we welcome and encourage any qualified subtitle.</li>
                    </ol>
                    <hr>
                </div>
                <div class="col-md-offset-2 col-md-8" id="subs_upload_form">
                    <div class="panel">
                        <div class="panel-body">
                            <form method="post" action="/subtitles/upload" enctype="multipart/form-data" class="form-horizontal" data-toggle="validator" role="form">
                                <div class="form-group">
                                    <label for="file" class="col-sm-2 required">Subs File</label>
                                    <div class="col-md-5 col-sm-10">
                                        <input type="file" class="form-control" id="file" name="file" required> <!-- TODO accept -->
                                    </div>
                                    <div class="help-block">(Maximum file size: <?= $this->e(config('upload.max_subtitle_file_size'),'format_bytes') ?>.)</div>
                                </div>
                                <div class="form-group">
                                    <label for="torrent_id" class="col-sm-2 required">Torrent ID</label>
                                    <div class="col-md-5 col-sm-10">
                                        <input type="text" class="form-control" id="torrent_id" name="torrent_id" pattern="^\d+$" required>
                                    </div>
                                    <div class="help-block">The number in the address bar when you go to the details page of the torrent</div>
                                </div>
                                <div class="form-group">
                                    <label for="title" class="col-sm-2">Title</label>
                                    <div class="col-md-5 col-sm-10">
                                        <input type="text" class="form-control" id="title" name="title">
                                    </div>
                                    <div class="help-block">(Optional, taken from file name if not specified.)</div>
                                </div>
                                <!-- TODO lang_id -->
                                <div class="form-group">
                                    <label class="col-sm-2">Anonymous</label>
                                    <div class="col-md-6 col-sm-10">
                                        <div class="switch">
                                            <input type="checkbox" name="anonymous" id="anonymous" value="1" title="">
                                            <label for="anonymous">Don't show my username in 'Upped By' field.</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group text-center">
                                    <button type="submit" value="Upload" class="btn btn-primary">Upload</button>
                                    <button type="reset" class="btn btn-warning">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel" id="subs_search_panel">
            <div class="panel-body">
                <div class="" id="subs_search"></div>
                <div class="" id="subs_list">
                    <?php if ($search->getTotal()): ?>
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <!-- TODO lang_id -->
                            <td width="100%">Title</td>
                            <td>Added at</td>
                            <td>Size</td>
                            <td>Hits</td>
                            <td>Uploader</td>
                            <td>Report</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($search->getPagerData() as $datum): ?>
                        <tr>
                            <td>
                                <div class="pull-right">
                                    <a href="/subtitles/download?id=<?= $datum['id'] ?>">[Download]</a>
                                </div>
                                <?= $this->e($datum['title']) ?>
                            </td>
                            <td><time class="nowrap"><?= $this->e($datum['added_at']) ?></time></td>
                            <td><span class="nowrap"><?= $this->e($datum['size'],'format_bytes') ?></span></td>
                            <td><span class="nowrap"><?= $this->e($datum['hits']) ?></span></td>
                            <td><span class="nowrap"><?= $this->insert('helper/username',['user' => app()->site->getUser($datum['uppd_by'])]) ?></span></td>
                            <td><a class="nowrap" href="#">Report</a></td>
                        </tr>

                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    No exist upload subtitles
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
<?php $this->end(); ?>
