<?php
/**
 * @link http://www.bittorrent.org/beps/bep_0036.html
 *
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/10
 * Time: 17:24
 *
 * @var \App\Models\Form\Rss\FeedForm $feed
 */

$url = (app()->request->isSecure() ? 'https://' : 'http://') . config('base.site_url');
$site_name = config('base.site_name');
$site_email = config('base.site_email');
$yearfounded = 2019; // FIXME get it from dynamic config
$copyright = "Copyright (c) " . $site_name . " " . (date("Y") != $yearfounded ? $yearfounded . "-" : "") . date("Y") . ", all rights reserved";
?>
<?= '<?xml version="1.0" encoding="utf-8"?>' ?>

<rss version="2.0">
    <channel>
        <title><?= addslashes($site_name . ' Torrents') ?></title>
        <link><?= $url ?></link>
        <description><![CDATA[<?= addslashes('Latest torrents from ' . $site_name) ?>]]></description>
        <language>en</language>
        <copyright><?= $copyright ?></copyright>
        <managingEditor><?= $site_email . "(" . $site_name . " Admin)" ?></managingEditor>
        <webMaster><?= $site_email . "(" . $site_name . " Webmaster)" ?></webMaster>
        <pubDate><?= date('r') ?></pubDate>
        <generator><?= config('base.site_generator') ?> RSS Generator</generator>
        <docs><![CDATA[http://www.rssboard.org/rss-specification]]></docs>
        <ttl>120</ttl>
        <image>
            <url><![CDATA[<?= $url . '/favicon.ico' ?>]]></url>
            <title><?= addslashes($site_name . 'Torrents') ?></title>
            <link><![CDATA[<?= $url  ?><]]></link>
            <width>100</width>
            <height>100</height>
            <description><?= addslashes($site_name . ' Torrents') ?></description>
        </image>
        <?php foreach ($feed->getPagerData() as $torrent): ?>
        <item>
            <title><![CDATA[<?= $torrent->getTitle() ?>]]></title>
            <link><?= $url.'/torrent/details?id=' . $torrent->getId() ?></link>
            <description><?= $torrent->getDescr() ?></description>
            <author><?= ($torrent->getUplver() ? 'Anonymous' : $torrent->getOwner()->getUsername()) . '@' . $site_name ?></author>
            <category domain="<?= $url . '/torrents?cat='.$torrent->getCategoryId()?>">Movie</category>
            <comments><![CDATA[<?= $url. '/torrent/details?id=' . $torrent->getId() . '&cmtpage=0#startcomments' ?>]]></comments>
            <enclosure url="<?= $url . '/torrent/download?id=' . $torrent->getId() . ('') ?>" length="<?= $torrent->getTorrentSize() ?>" type="application/x-bittorrent" />
            <guid isPermaLink="false"><?= $torrent->getInfoHash() ?></guid>
            <pubDate><?= date('r', strtotime($torrent->getAddedAt())) ?></pubDate>
        </item>
        <?php endforeach; ?>
    </channel>
</rss>
