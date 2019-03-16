<?php
/**
 *
 * Common function to render torrent page
 *
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/11
 * Time: 16:09
 *
 */

/**
 * @param \apps\models\Torrent $torrent
 * @return string
 */
function get_torrent_uploader_id(\apps\models\Torrent $torrent)
{
    if ($torrent->getUplver() == 'yes' and app()->user->getClass(true) < app()->config->get('authority.see_anonymous_uploader')) {
        return 0;
    } else {
        return $torrent->getOwnerId();
    }
}

/**
 * @param \apps\models\Torrent $torrent
 * @return string
 */
function get_torrent_uploader(\apps\models\Torrent $torrent)
{
    $owner_id = get_torrent_uploader_id($torrent);
    if ($owner_id == 0) {
        return '<i>Anonymous</i>';
    } else {
        return "<a class=\"text-default\" href=\"/user/panel?id={$torrent->getOwnerId()}\" data-toggle=\"tooltip\" title=\"User\">{$torrent->getOwner()->getUsername()}</a>";
    }
}
