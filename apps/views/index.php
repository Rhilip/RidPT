<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 11:38
 *
 * @var League\Plates\Template\Template $this
 * @var array $news
 */

$can_manage_news = app()->user->isPrivilege('manage_news');
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Index<?php $this->end();?>

<?php $this->start('container') ?>
<div class="panel" id="news_panel">
    <div class="panel-heading">Recent News - <small>[<a href="/news" target="_blank">All</a>]</small>
        <?= $can_manage_news ? '<small>[<a href="/news/new">New</a>]</small>' : '' ?>
    </div>
    <div class="panel-body">
        <?php if (empty($news)): ?>
        No any news.
        <?php else: ?>
        <div class="list list-condensed">
            <div class="items items-hover">
                <?php foreach ($news as $index => $new): ?>
                <div class="item">
                    <div class="item-heading">
                        <div class="pull-right">
                            <!-- TODO add delete Protect -->
                            <?= $can_manage_news ? "<a href=\"/news/edit?id={$new['id']}\"><i class=\"icon-pencil\"></i> Edit</a> &nbsp;<a href=\"/news/delete?id={$new['id']}\"><i class=\"icon-remove\"></i> Delete</a> &nbsp;" : '' ?>
                            <span class="text-muted"><?= $new['create_at'] ?></span>
                        </div>
                        <h4><a href="#new_<?= $new['id'] ?>" data-toggle="collapse"><?= $new['title'] ?></a></h4>
                    </div>
                    <div class="item-content collapse<?= $index === 0 ? ' in' : '' ?>" id="new_<?= $new['id'] ?>">
                        <div class="text"><?= $this->batch($new['body'],'format_ubbcode'); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
    <h1>Navbar <?= __('greeting', null); ?></h1>
    <p><strong>I'm sorry for broken page since I'm rebuilding. <?= __('greet') ?></strong></p>
</div>
<?php $this->stop() ?>
