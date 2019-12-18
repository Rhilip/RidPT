<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 12/9/2019
 * Time: 2019
 */

namespace PHPSTORM_META {

    registerArgumentsSet('config_base',
        /**
         * The uri of site's tracker, without `https?://` at before and `/` at end
         * Left it blank unless you know what you are doing.
         * Because It will automatically generate to `%config(base_site_url)%/tracker/announce`
         *
         * If you want to edited it , Must include key `announce` in uri
         *
         * @var string
         * @example "ridpt.top/tracker/announce"
         */
        'base.site_tracker_url',

        /**
         * The tracker list which will set in torrent->{announce-list}
         * Left it blank unless you know what you are doing.
         *
         * If you should edited it , Following those suggestion:
         *  - First enabled `%config(base_enabled_tracker_list)`
         *  - The list should be encode with Separator `,`
         *  - Each item MUST include key `announce` in uri
         *
         * @var array
         * @example "["ipv6.pt.rhilip.info/tracker/announce","ridpt.top/tracker/announce"]"
         */
        'base.site_multi_tracker_url'
    );

    expectedArguments(\config(), 0, argumentsSet('config_base'));

}
