<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 11:38
 *
 * @var League\Plates\Template\Template $this
 * @var array $news
 * @var array $links
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Index<?php $this->end();?>

<?php $this->start('container') ?>
<div class="panel" id="news_panel">
    <?php $can_manage_news = app()->auth->getCurUser()->isPrivilege('manage_news'); ?>
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
                        <h4>
                            <a href="#new_<?= $new['id'] ?>" data-toggle="collapse"><?= $new['title'] ?></a>
                            <?php if ($index == 0): ?>
                                <span class="label label-info">New</span>
                            <?php endif; ?>
                            <?php if ($new['force_read']): ?>
                                <span class="label label-warning">Important</span>
                            <?php endif; ?>
                        </h4>
                    </div>
                    <div class="item-content collapse<?= $index === 0 ? ' in' : '' ?>" id="new_<?= $new['id'] ?>">
                        <div class="text"><?= $this->batch($new['body'], 'format_ubbcode'); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="panel" id="links_panel">
    <div class="panel-heading">
        Links
        <?php if (app()->auth->getCurUser()->isPrivilege('apply_for_links')): ?>
        - [<a href="/links/apply" target="_blank">Apply for Links</a>]
        <?php endif; ?>
        <?php if (app()->auth->getCurUser()->isPrivilege('manage_links')): ?>
        [<a href="/links/manage" target="_blank">Manage Links</a>]
        <?php endif; ?>
    </div>
    <div class="panel-body">
        <?php foreach ($links as $link): ?>
            <a href="<?= $this->e($link['url']) ?>" target="_blank" title="<?= $this->e($link['title']) ?>">
                <img src="https://www.google.com/s2/favicons?domain=<?= parse_url($link['url'])['host'] ?>" alt="">
                <?= $this->e($link['name']) ?>
            </a>&nbsp;&nbsp;
        <?php endforeach; ?>
    </div>
</div>

<div class="panel" id="disclaimer_panel">
    <div class="panel-heading">Disclaimer</div>
    <div class="panel-body">
        None of the files shown here are actually hosted on this server. The tracker only manages connections, it does
        not have any knowledge of the contents of the files being distributed. The links are provided solely by this
        site's users. The administrator of this site cannot be held responsible for what its users post, or any other
        actions of its users. You may not use this site to distribute or download any material when you do not have the
        legal rights to do so. It is your own responsibility to adhere to these terms.
    </div>
</div>

<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
    <h1>Navbar <?= __('greeting'); ?></h1>
    <p><strong>I'm sorry for broken page since I'm building now and this work is not finishing. </strong></p>
</div>
<?php $this->stop() ?>
