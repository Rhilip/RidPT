<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 8:15 AM
 */

declare(strict_types=1);

namespace App\Forms\Torrents;

use App\Forms\Traits\sendFileTrait;
use Rhilip\Bencode\Bencode;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class DownloadForm extends StructureForm
{
    use sendFileTrait;

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveInt(),
            'https' => new Assert\Optional(new Assert\AtLeastOneOf([
                new Assert\IsTrue(), new Assert\IsFalse()
            ]))
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isExistTorrent', 'checkDownloadPos'];
    }

    public function flush()
    {
        // TODO: Implement flush() method.
    }

    protected function checkDownloadPos()
    {
        if (!container()->get('auth')->getCurUser()->getDownloadpos()) {
            $this->buildCallbackFailMsg('pos', 'your download pos is disabled');
        }
    }


    public function sendFileContentToClient()
    {
        $dict = $this->getTorrentFileContentDict();

        $scheme = 'http://';
        if ($this->hasInput('https')) {
            $scheme = $this->getInput('https') ? 'https://' : 'http://';
        } elseif (container()->get('request')->isSecure()) {
            $scheme = 'https://';
        }

        $announce_suffix = '/announce?passkey=' . container()->get('auth')->getCurUser()->getPasskey();
        $dict['announce'] = $scheme . config('base.site_tracker_url') . $announce_suffix;

        /**
         * BEP 0012 Multitracker Metadata Extension
         *
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

        $content = Bencode::encode($dict);

        $filename = '[' . config('base.site_name') . '].' . $this->getTorrent()->getTorrentName() . '.torrent';
        container()->get('response')->setDynamicFile($content, 'application/x-bittorrent', $filename, true);
    }
}
