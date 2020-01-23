<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 11:35 PM
 */

namespace App\Models\Form\Torrent;

use App\Models\Form\Traits\FileSentTrait;

use Rhilip\Bencode\Bencode;

class DownloadForm extends StructureForm
{
    use FileSentTrait;

    public $https;

    protected static $SEND_FILE_CONTENT_TYPE = 'application/x-bittorrent';
    protected static $SEND_FILE_CACHE_CONTROL = true;

    public static function callbackRules(): array
    {
        return ['checkDownloadPos', 'isExistTorrent', 'rateLimitCheck'];
    }

    protected function getSendFileName(): string
    {
        return '[' . config('base.site_name') . '].' . $this->torrent->getTorrentName() . '.torrent';
    }

    protected function getRateLimitRules(): array
    {
        return [
            /* ['key' => 'dl_60', 'period' => 60, 'max' => 1] */
        ];
    }

    protected function checkDownloadPos()
    {
        if (!app()->auth->getCurUser()->getDownloadpos()) {
            $this->buildCallbackFailMsg('pos', 'your download pos is disabled');
        }
    }

    public function getSendFileContent()
    {
        $dict = $this->getTorrentFileContentDict();

        $scheme = 'http://';
        if (isset($this->https)) {
            $scheme = filter_var($this->https, FILTER_VALIDATE_BOOLEAN) ? 'https://' : 'http://';
        } elseif (app()->request->isSecure()) {
            $scheme = 'https://';
        }

        $announce_suffix = '/announce?passkey=' . app()->auth->getCurUser()->getPasskey();
        $dict['announce'] = $scheme . config('base.site_tracker_url') . $announce_suffix;

        /** BEP 0012 Multitracker Metadata Extension
         * @see http://www.bittorrent.org/beps/bep_0012.html
         * @see https://web.archive.org/web/20190724110959/https://blog.rhilip.info/archives/1108/
         *      which discuss about multitracker behaviour on common bittorrent client ( Chinese Version )
         */
        $multi_trackers_list = config('base.site_multi_tracker_url');
        if (!empty($multi_trackers)) {
            // Add our main tracker into multi_tracker_list to avoid lost....
            array_unshift($multi_trackers_list, config('base.site_tracker_url'));
            $multi_trackers_list = array_unique($multi_trackers_list);  // use array_unique to remove dupe tracker

            $dict["announce-list"] = [];
            foreach ($multi_trackers_list as $uri) {
                $tracker = $scheme . $uri . $announce_suffix;  // fulfill each tracker with scheme and suffix about user identity
                if (config('base.site_multi_tracker_behaviour') == 'separate') {
                    /** d['announce-list'] = [ [tracker1], [backup1], [backup2] ] */
                    $dict["announce-list"][] = [$tracker];  // Make each tracker as tier
                } else {  // config('base.site_multi_tracker_behaviour') ==  'union'
                    /** d['announce-list'] = [[ tracker1, tracker2, tracker3 ]] */
                    $dict["announce-list"][0][] = $tracker;  // all tracker in tier 0
                }
            }
        }

        return Bencode::encode($dict);
    }
}
