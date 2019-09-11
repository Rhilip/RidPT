<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/19/2019
 * Time: 7:39 PM
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Models\Form\Torrents\TagsForm $pager
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Tags List<?php $this->end();?>

<?php $this->start('container')?>
<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <form method="get">
                    <div class="input-group">
                        <div class="input-control search-box search-box-circle has-icon-left has-icon-righ"
                             id="tags_search_div">
                            <input id="tags_search_input" type="text" name="search" class="form-control search-input" value="<?= $pager->search ?? '' ?>">
                            <label for="tags_search_input" class="input-control-icon-left search-icon">
                                <i class="icon icon-search"></i>
                            </label>
                        </div>
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-10 col-md-offset-1">
        <div id="tags_list">
            <?php foreach ($pager->getPagerData() as $tag): ?>
                <a class="btn" href="/torrents/search?tags=<?= $tag['tag'] ?>"><?= $tag['tag'] ?> <span class="label label-badge <?= $tag['class_name'] ?>"><?= $tag['count'] ?></span></a>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <ul class="pager" data-ride="remote_pager" data-rec-total="<?= $pager->getTotal() ?>"  data-rec-per-page="<?= $pager->getLimit() ?>"></ul>
        </div>
    </div>
</div>
<?php $this->end();?>


