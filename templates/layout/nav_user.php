<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/21/2019
 * Time: 8:19 AM
 *
 * @var League\Plates\Template\Template $this
 */

$user = app()->auth->getCurUser();

use App\Entity\User\UserRole;

?>

<!-- TODO fix nav miss in sm -->
<nav id="nav" class="navbar navbar-default navbar-static-top navbar-custom" role="navigation">
    <div class="container">
        <div class="collapse navbar-collapse navbar-collapse-custom">
            <ul class="nav navbar-nav nav-justified">
                <li><a href="/"><?= __('nav.index') ?></a></li>
                <li><a href="/forums"><?= __('nav.forums') ?></a></li> <!-- TODO  -->
                <li><a href="/collections"><?= __('nav.collections') ?></a></li> <!-- TODO  -->
                <li><a href="/torrents"><?= __('nav.torrents') ?></a></li>
                <li><a href="/torrent/upload"><?= __('nav.upload') ?></a></li>
                <li><a href="/torrents/request"><?= __('nav.requests') ?></a></li> <!-- TODO  -->
                <li><a href="/subtitles"><?= __('nav.subtitles') ?></a></li> <!-- TODO  -->
                <li><a href="/site/rules"><?= __('nav.rules') ?></a></li> <!-- TODO  -->
                <li><a href="/site/staff"><?= __('nav.staff') ?></a></li> <!-- TODO  -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= __('nav.more') ?> <b class="caret"></b></a>
                    <ul class="dropdown-menu" role="menu"><!-- FIXME class="active" in dropdown-munu -->
                        <li><a href="/site/topten"><?= __('nav.topten') ?></a></li> <!-- TODO  -->
                        <li class="divider"></li>
                        <li><a href="/site/stats"><?= __('nav.stats') ?></a></li> <!-- TODO  -->
                        <li><a href="/site/logs"><?= __('nav.log') ?></a></li>
                    </ul>
                </li>
            </ul> <!-- END .navbar-nav -->
        </div><!-- END .navbar-collapse -->
    </div>
</nav> <!-- END /nav -->
<div class="clearfix"></div>

<div id="info_block">
    <div id="info_block_line_1">
        Welcome Back, <?= $this->insert('helper/username', ['user' => $user, 'show_badge' => true]) ?>
        <span data-item="logout"><!--suppress HtmlUnknownTarget -->[<a href="/auth/logout">Logout</a>]</span>
        <?php if ($user->getClass() > UserRole::FORUM_MODERATOR): ?>
            <span><!--suppress HtmlUnknownTarget -->[<a href="/admin">Admin Panel</a>]</span>
        <?php endif; ?>
        <span data-item="favour"><!--suppress HtmlUnknownTarget -->[<a href="/torrents/favour">Favour</a>]</span>
        <span data-item="invite" data-invite="<?= $user->getInvites() ?>" data-temp-invite="<?= $user->getTempInvitesSum() ?>">
            <span class="color-invite">Invite [<a href="/user/invite">Send</a>]: </span> <?= $user->getInvites() ?>
            <?php if ($user->getTempInvitesSum() > 0): ?>
                <span data-toggle="tooltip" data-placement="bottom" title="Temporarily Invites" class="text-primary">(+<?= $user->getTempInvitesSum() ?>)</span>
            <?php endif; ?>
        </span>
        <span data-item="bonus" data-bonus="<?= $this->e($user->getBonus()); ?>">
            <span class="color-bonus">Bonus: </span> <a href="#"><?= number_format($user->getBonus()); ?></a>
        </span>
        <span data-item="bet">[<a href="#"><span class="color-bet">Bet(22)</span></a>]</span> <!-- TODO  -->
        <span data-item="blackjack">[<a href="#"><span class="color-blackjack">Blackjack</span></a>]</span> <!-- TODO  -->
        <div class="pull-right">
            <span data-item="chang_lang">
                <a href="#"><span class="flag flag-chn"></span></a>&nbsp;
                <a href="#"><span class="flag flag-hkg"></span></a>&nbsp;
                <a href="#"><span class="flag flag-gbr"></span></a>&nbsp;
            </span> <!-- TODO -->
            Now: <?= date('H:i (P)') ?>
        </div>
    </div>
    <div id="info_block_line_2">
        <span data-item="ratio" data-ratio="<?= $this->e($user->getRatio()) ?>">
            <span class="color-ratio">Ratio:</span> <?= is_string($user->getRatio()) ? $user->getRatio() : round($user->getRatio(), 3) ?>
        </span>&nbsp;
        <span data-item="uploaded" data-uploaded="<?= $this->e($user->getUploaded()) ?>">
            <span class="color-seeding">Uploaded:</span> <?= $this->e($user->getUploaded(), 'format_bytes') ?>
        </span>&nbsp;
        <span data-item="download" data-downloaded="<?= $this->e($user->getDownloaded()) ?>">
            <span class="color-leeching">Downloaded:</span> <?= $this->e($user->getDownloaded(), 'format_bytes') ?>
        </span>&nbsp;
        <span data-item="bt_activity">
            BT Activity:
            <span class="fas fa-arrow-up fa-fw color-seeding" data-seeding="<?= $user->getActiveSeed() ?>">&nbsp;<?= $user->getActiveSeed() ?></span>&nbsp;&nbsp;
            <span class="fas fa-arrow-down fa-fw color-leeching" data-leeching="<?= $user->getActiveLeech() ?>">&nbsp;<?= $user->getActiveLeech() ?></span>&nbsp;&nbsp;
            <?php if ($user->getActivePartial()): ?>
            <span class="fas fa-minus fa-fw color-partial" data-partial="<?= $user->getActivePartial() ?>">&nbsp;<?= $user->getActivePartial() ?></span>&nbsp;&nbsp;
            <?php endif; ?>
        </span>
        <span data-item="connectable" data-connectable="IPv4/IPv6">
            <span class="color-connectable">Connectable:</span>
            IPv4/IPv6 <!-- TODO -->
        </span>&nbsp;
        <div class="pull-right">
            <a href="/user/message"><span class="fas fa-envelope fa-fw<?= $user->getUnreadMessageCount() > 0 ? ' red' : '' ?>"></span>Message Box<?= $user->getUnreadMessageCount() > 0 ? ' (' . $user->getUnreadMessageCount() . ')' : '' ?></a> <!-- TODO -->
            <a href="#"><span class="fas fa-user-friends fa-fw color-friends"></span></a> <!-- TODO -->
            <a href="#"><span class="fas fa-rss-square fa-fw color-rss"></span></a>  <!-- TODO -->
        </div>
    </div>
</div> <!-- END /info_block -->
