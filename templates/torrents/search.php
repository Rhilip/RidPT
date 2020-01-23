<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/4
 * Time: 20:40
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Models\Form\Torrents\SearchForm $search
 */

$time_now = time();

?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Torrents List<?php $this->end();?>

<?php $this->start('container') ?>
<!-- TODO insert container head : For example, Search toolbox..... -->
<div class="row">
    <form class="form form-horizontal col-md-12" id="search_form">
        <div class="panel" id="search_panel">
            <div class="panel-heading text-center"><a href="#full_search_box" data-toggle="collapse">Search Box</a></div>
            <div class="panel-body collapse" id="full_search_box">
                <div class="row">
                    <div class="col-md-9">
                        <!-- TODO category -->
                        <?php foreach (app()->site->getQualityTableList() as $quality => $title): ?>
                            <?php if (config('torrent_upload.enable_quality_' . $quality)) : ?>
                            <div class="form-group" data-quality="<?= $quality ?>">
                                <b><?= $title ?></b>
                                <br />
                                <div class="row" style="margin-left: 15px">
                                <?php foreach (app()->site->ruleQuality($quality) as $q): ?>
                                <?php $req_quality = input2array(app()->request->query->get($quality, [])); ?>
                                    <label class="col-md-2">
                                        <input type="checkbox" name="<?= $quality ?>[]" value="<?= $q['id'] ?>"<?= in_array($q['id'], $req_quality) ? ' checked': '' ?>> <?= $q['name'] ?>
                                    </label>
                                <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?><!-- End quality -->
                        <div>
                            <div class="form-group">
                                <b>Group: </b>
                                <br />
                                <div class="row" style="margin-left: 15px">
                                <?php foreach (app()->site->ruleTeam() as $team) : ?>
                                <?php $req_team = input2array(app()->request->query->get('team', [])); ?>
                                    <label class="col-md-2">
                                        <input type="checkbox" name="team[]" value="<?= $team['id'] ?>"<?= in_array($team['id'], $req_team) ? ' checked': '' ?>> <?= $team['name'] ?>
                                    </label>
                                <?php endforeach; ?>
                                </div>
                            </div>
                        </div><!-- End team -->
                    </div>
                    <div class="col-md-3">

                    </div>
                </div>
            </div>
            <div class="row text-center" id="small_search_box">
                <div class="col-md-offset-3 col-md-6">
                    <div class="input-group">
                        <div class="input-control search-box search-box-circle has-icon-left has-icon-right">
                            <input type="search" class="form-control search-input" name="search" id="search" placeholder="<?= __('search') ?>" value="<?= $this->e(app()->request->query->get('search')) ?>">
                            <label for="search" class="input-control-icon-left search-icon"><i class="icon icon-search"></i></label>
                        </div>
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="submit"><?= __('search') ?></button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="col-md-12" id="torrents_list">
        <?= $this->insert('helper/torrent_table', ['search' => $search]) ?>
    </div>
</div>
<?php $this->end();?>
