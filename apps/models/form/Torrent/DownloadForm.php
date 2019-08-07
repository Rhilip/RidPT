<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 11:35 PM
 */

namespace apps\models\form\Torrent;

use Rid\Bencode\Bencode;

class DownloadForm extends StructureForm
{
    public $https;

    public static function inputRules()
    {
        return [
            'id' => 'required | Integer',
            //'passkey' // TODO add support
        ];
    }

    public function setRespHeaders() {
        $filename = '[' . config('base.site_name') . ']' . $this->torrent->getTorrentName() . '.torrent';

        app()->response->setHeader('Content-Type', 'application/x-bittorrent');
        if (strpos(app()->request->header('user-agent'), 'IE')) {
            app()->response->setHeader('Content-Disposition', 'attachment; filename=' . str_replace('+', '%20', rawurlencode($filename)));
        } else {
            app()->response->setHeader('Content-Disposition', "attachment; filename=\"$filename\" ; charset=utf-8");
        }
    }

    public function getDownloadDict() {
        $dict = $this->getTorrentFileContentDict();

        $scheme = 'http://';
        if (filter_var($this->https, FILTER_VALIDATE_BOOLEAN))
            $scheme = 'https://';
        else if (filter_var($this->https, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))
            $scheme = 'http://';
        else if (app()->request->isSecure())
            $scheme = 'https://';

        $announce_suffix = '/announce?passkey=' . app()->site->getCurUser()->getPasskey();
        $dict['announce'] = $scheme . config('base.site_tracker_url') . $announce_suffix;

        /** BEP 0012 Multitracker Metadata Extension
         * @see http://www.bittorrent.org/beps/bep_0012.html
         * @see https://web.archive.org/web/20190724110959/https://blog.rhilip.info/archives/1108/
         *      which discuss about multitracker behaviour on common bittorrent client ( Chinese Version )
         */
        if ($multi_trackers = config('base.site_multi_tracker_url')) {
            // Add our main tracker into multi_tracker_list to avoid lost....
            $multi_trackers = config('base.site_tracker_url') . ',' . $multi_trackers;
            $multi_trackers_list = explode(',', $multi_trackers);
            $multi_trackers_list = array_unique($multi_trackers_list);  // use array_unique to remove dupe tracker
            // fulfill each tracker with scheme and suffix about user identity
            $multi_trackers_list = array_map(function ($uri) use ($scheme, $announce_suffix) {
                return $scheme . $uri . $announce_suffix;
            }, $multi_trackers_list);

            if (config('base.site_multi_tracker_behaviour') == 'separate') {
                /** d['announce-list'] = [ [tracker1], [backup1], [backup2] ] */
                foreach ($multi_trackers_list as $tracker) {  // separate each tracker to different tier
                    $dict["announce-list"][] = [$tracker];  // Make each tracker as tier
                }
            } else {  // config('base.site_multi_tracker_behaviour') ==  'union'
                /** d['announce-list'] = [[ tracker1, tracker2, tracker3 ]] */
                $dict["announce-list"][] = $multi_trackers_list;
            }
        }

        return Bencode::encode($dict);
    }
}
