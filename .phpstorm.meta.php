<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 12/9/2019
 * Time: 2019
 */

namespace PHPSTORM_META {

    // TODO
    registerArgumentsSet('config.authority',
        'authority.apply_for_links',
        'authority.bypass_maintenance',
        'authority.invite_manual_confirm',
        'authority.invite_recycle_other_pending',
        'authority.invite_recycle_self_pending',
        'authority.manage_links',
        'authority.manage_news',
        'authority.manage_subtitles',
        'authority.manage_torrents',
        'authority.pass_invite_interval_check',
        'authority.pass_tracker_upspeed_check',
        'authority.see_anonymous_info',
        'authority.see_banned_torrent',
        'authority.see_extend_debug_log',
        'authority.see_pending_torrent',
        'authority.see_site_log_leader',
        'authority.see_site_log_mod',
        'authority.upload_flag_anonymous',
        'authority.upload_flag_hr',
        'authority.upload_nfo_file',
    );

    // TODO
    registerArgumentsSet('config.base',
        'base.enable_extend_debug',
        'base.enable_invite_system',
        'base.enable_register_system',
        'base.enable_tracker_system',
        'base.maintenance',
        'base.max_news_sum',
        'base.max_per_user_session',
        'base.max_user',
        'base.prevent_anonymous',
        'base.site_author',
        'base.site_copyright',
        'base.site_css_update_date',
        'base.site_description',
        'base.site_email',
        'base.site_generator',
        'base.site_keywords',
        'base.site_multi_tracker_behaviour',
        'base.site_name',
        'base.site_url',

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
         * @var json
         * @example "["ipv6.pt.rhilip.info/tracker/announce","ridpt.top/tracker/announce"]"
         */
        'base.site_multi_tracker_url'
    );

    // TODO
    registerArgumentsSet('config.buff',
        'buff.enable_large',
        'buff.enable_magic',
        'buff.enable_mod',
        'buff.enable_random',
        'buff.large_size',
        'buff.large_type',
        'buff.random_percent_2x',
        'buff.random_percent_2x50%',
        'buff.random_percent_2xfree',
        'buff.random_percent_30%',
        'buff.random_percent_50%',
        'buff.random_percent_free',
    );

    /** Config - gravatar
     * If you set config('user.avatar_provider') == 'gravatar' , then you should edit this section
     * Otherwise this section will not work
     *
     * @link https://en.gravatar.com/site/implement/images/
     */
    registerArgumentsSet('config.gravatar',
        /**
         * The base domain of Gravatar , by default it's offical domain
         * But you can also use other mirror domain like:
         *   - https://secure.gravatar.com/avatar/
         *   - https://cn.gravatar.com/avatar/
         *   - https://cdn.v2ex.com/gravatar/
         *   - https://gravatar.loli.net/
         *   - https://grv.luotianyi.vc/avatar/
         *   - https://gravatar.cat.net/avatar/
         *   - https://v2ex.assets.uxengine.net/gravatar/
         *
         * @var string
         * @example 'https://www.gravatar.com/avatar/'
         */
        'gravatar.base_url',

        /**
         * Default Image
         * if you want to use your own default image, the URL should be URL-encoded to ensure that it carries
         * across correctly since we not urlencode this value
         * Or you can use offical built in options ,
         * like:
         *   - https://example.com/images/avatar.jpg
         *   - 404: do not load any image if none is associated with the email hash, instead return an HTTP 404 (File Not Found) response
         *   - mp: (mystery-person) a simple, cartoon-style silhouetted outline of a person (does not vary by email hash)
         *   - identicon: a geometric pattern based on an email hash
         *   - monsterid: a generated 'monster' with different colors, faces, etc
         *   - wavatar: generated faces with differing features and backgrounds
         *   - retro: awesome generated, 8-bit arcade-style pixelated faces
         *   - robohash: a generated robot with different colors, faces, etc
         *   - blank: a transparent PNG image (border added to HTML below for demonstration purposes)
         *
         * @var string
         * @example 'identicon'
         */
        'gravatar.default_fallback',

        /**
         * Rating
         *
         *   - g: suitable for display on all websites with any audience type.
         *   - pg: may contain rude gestures, provocatively dressed individuals, the lesser swear words, or mild violence.
         *   - r: may contain such things as harsh profanity, intense violence, nudity, or hard drug use.
         *   - x: may contain hardcore sexual imagery or extremely disturbing violence.
         *
         * @var string
         * @example 'g'
         */
        'gravatar.maximum_rating',
    );

    // TODO
    registerArgumentsSet('config.invite',
        'invite.force_interval',
        'invite.interval',
        'invite.recycle_invite_lifetime',
        'invite.recycle_return_invite',
        'invite.timeout',
    );

    // TODO
    registerArgumentsSet('config.register',
        'register.by_green',
        'register.by_invite',
        'register.by_open',
        'register.check_email_blacklist',
        'register.check_email_whitelist',
        'register.check_max_ip',
        'register.check_max_user',
        'register.email_black_list',
        'register.email_white_list',
        'register.per_ip_user',
        'register.user_confirm_way',
        'register.user_default_bonus',
        'register.user_default_class',
        'register.user_default_downloaded',
        'register.user_default_downloadpos',
        'register.user_default_invites',
        'register.user_default_leechtime',
        'register.user_default_seedtime',
        'register.user_default_status',
        'register.user_default_uploaded',
        'register.user_default_uploadpos',
    );

    // TODO
    registerArgumentsSet('config.route',
        'route.admin_index',
        'route.admin_service',
    );

    // TODO
    registerArgumentsSet('config.security',
        'security.auto_logout',
        'security.max_login_attempts',
        'security.secure_login',
        'security.ssl_login'
    );

    // TODO
    registerArgumentsSet('config.torrent_upload',
        'torrent_upload.allow_new_custom_tags',
        'torrent_upload.enable_anonymous',
        'torrent_upload.enable_hr',
        'torrent_upload.enable_quality_audio',
        'torrent_upload.enable_quality_codec',
        'torrent_upload.enable_quality_medium',
        'torrent_upload.enable_quality_resolution',
        'torrent_upload.enable_subtitle',
        'torrent_upload.enable_tags',
        'torrent_upload.enable_teams',
        'torrent_upload.enable_upload_nfo',
        'torrent_upload.rewrite_commit_to',
        'torrent_upload.rewrite_createdby_to',
        'torrent_upload.rewrite_source_to',
    );

    // TODO
    registerArgumentsSet('config.tracker',
        'tracker.cheater_check',
        'tracker.enable_announce',
        'tracker.enable_maxdlsystem',
        'tracker.enable_scrape',
        'tracker.enable_upspeed_check',
        'tracker.enable_waitsystem',
        'tracker.force_compact_model',
        'tracker.force_no_peer_id_model',
        'tracker.interval',
        'tracker.max_numwant',
        'tracker.min_interval',
        'tracker.retry_interval',
        'tracker.user_max_leech',
        'tracker.user_max_seed',
    );

    // TODO
    registerArgumentsSet('config.upload',
        'upload.max_nfo_file_size',
        'upload.max_subtitle_file_size',
        'upload.max_torrent_file_size',
    );

    // TODO
    registerArgumentsSet('config.user',
        'user.avatar_provider',
    );

    expectedArguments(\config(), 0,
        argumentsSet('config.authority'),
        argumentsSet('config.base'),
        argumentsSet('config.buff'),
        argumentsSet('config.gravatar'),
        argumentsSet('config.invite'),
        argumentsSet('config.register'),
        argumentsSet('config.route'),
        argumentsSet('config.security'),
        argumentsSet('config.torrent_upload'),
        argumentsSet('config.tracker'),
        argumentsSet('config.upload'),
        argumentsSet('config.user'),
    );

    registerArgumentsSet('view.function',
        'format_bytes',
        'format_bytes_compact',
        'format_bytes_loose',
        'format_ubbcode',
        'sec2hms'
    );

    expectedArguments(\League\Plates\Template\Template::escape(), 1, argumentsSet('view.function'));
    expectedArguments(\League\Plates\Template\Template::e(), 1, argumentsSet('view.function'));

}
