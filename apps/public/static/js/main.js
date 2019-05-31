;

const _location_search = new URLSearchParams(window.location.search);
/**
 * The icon map for file extension
 * Notice: The map key should has the fontawesome icon like `fa-file-${v}`
 */
const ext2Icon = {
    audio: ["flac", "aac", "wav", "mp3"],
    video: ["mkv", "mka", "mp4"],
    image: ["jpg", "bmp", "jpeg", "webp"],
    alt: ["txt", "log", "cue", "ass"],
    archive: ["rar", "zip", "7z"],
    word: ["doc", "docx", "docm", "dotx", "dotm", "dot", "odt"],
    powerpoint: ["ppt", "pptx", "pptm", "potx", "potm", "pot", "ppsx", "ppsm", "pps", "ppam", "ppa", "odp"],
    excel: ["xlsx", "xlsm", "xlsb", "xls", "xltx", "xltm", "xlt", "xlam", "xla", "ods"],
    pdf: ["pdf"],
    csv: ["csv"],
    code: [],
    contract: []
};

function humanFileSize(bytes, fix, si) {
    let thresh = si ? 1000 : 1024;
    if (Math.abs(bytes) < thresh) {
        return bytes + ' B';
    }
    let units = si
        ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
        : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
    let u = -1;
    do {
        bytes /= thresh;
        ++u;
    } while (Math.abs(bytes) >= thresh && u < units.length - 1);
    return bytes.toFixed(fix ? fix : 2) + ' ' + units[u];
}

function location_search_replace(new_params) {
    let search = _location_search;
    for (let i in new_params) {
        search.set(i,new_params[i]);
    }
    return '?' + search.toString();
}

function get_ext_icon (ext) {
    for (let type in ext2Icon) {
        if (ext2Icon[type].indexOf(ext) >= 0) {
            return 'fa-file-' + type;
        }
    }
    return "fa-file";
}

jQuery(document).ready(function() {
    // Drop all support of IE 6-11
    if ($.zui.browser.ie) {
        $.zui.browser.tip();
    }

    // Declare Const
    const api_point = '/api/v1';

    // Active tooltop
    $('[data-toggle="tooltip"]').tooltip();

    // Active Pager which source from remote
    $('ul[data-ride="remote_pager"]').pager({
        page: _location_search.get('page') || 0,
        maxNavCount: 8,
        elements: ['first_icon', 'prev_icon', 'pages', 'next_icon', 'last_icon'],
        linkCreator: function(page, pager) {
            return location_search_replace({
                'page': page,
                'limit': pager.recPerPage
            });
        }
    });

    // TODO Add Scroll to TOP fixbar



    // Common Function
    function create_error_notice(text,option) {
        option = $.extend({
            icon: 'exclamation-sign',
            type: 'danger',
            placement: 'top-right'
        },option);
        return new $.zui.Messager(text, option).show();
    }

    // Torrent favour Add/Remove action
    $('.torrent-favour').click(function () {
        let that = $(this);
        let tid = that.attr('data-tid');
        let star = that.find(' > i');

        $.post(api_point + '/torrent/bookmark', {'tid': tid}, function (res) {
            if (res.success) {
                let old_is_stared = star.hasClass('fas');
                star.toggleClass('fas', !old_is_stared).toggleClass('far', old_is_stared);
                new $.zui.Messager(`Torrent(${tid}) ${res.result} from your favour successfully`, {
                    icon: 'ok-sign',
                    type: 'success',
                    placement: 'top-right'
                }).show();
            } else {
                create_error_notice(res.errors.join(', '));
            }
        });
    });

    // View Torrent File list
    $('.torrent-files').click(function () {
        let that = $(this);
        let tid = that.attr('data-tid');

        function list_worker(tree, par = '') {
            let ret = '';
            let size = 0;
            for (let k in tree) {
                let v = tree[k];
                if (typeof v == 'object') {
                    let [in_ret, in_size] = list_worker(v, par + "/" + k);
                    ret += `<li${par === '' ? ' class="open"' : ''}><a href="#">${k} (<span class="file-size" data-size="${v}">${humanFileSize(in_size)}</span>)</a><ul>${in_ret}</ul></li>`;
                    size += in_size;
                } else {
                    let ext = k.substr(k.lastIndexOf('.') + 1).toLowerCase();

                    ret += `<li><i class="fa ${get_ext_icon(ext)} fa-fw"></i> ${k} (<span class="file-size" data-size="${v}">${humanFileSize(v)}</span>)</li>`;
                    size += v;
                }
            }
            return [ret, size];
        }

        // TODO Add Client Cache ( innodb )
        $.get(api_point + '/torrent/filelist', {'tid': tid}, function (res) {
            if (res.success) {
                let file_list = res.result;
                (new $.zui.ModalTrigger({
                    name: 'torrent_filelist_model',
                    showHeader: false,
                    size: 'lg',
                    //width: '700px',
                    moveable: true,
                    custom: "<ul  class='tree tree-lines tree-folders' data-ride='tree' id='torrent_filelist'>" + list_worker(file_list)[0] + "</ul>"
                })).show({
                    shown:function () {
                        $('#torrent_filelist').tree();
                    }
                });
            } else {
                create_error_notice(res.errors.join(', '));
            }
        });
    });

    // For torrents structure page
    if ($('#torrent_structure').length) {
        $('#torrent_structure div.dictionary,div.list').click(function () {
            $(this).next('ul').toggle();
        });
    };

    // Show Extend debug info of Database sql execute and Redis key hit
    if (typeof _extend_debug_info !== 'undefined' && _extend_debug_info) {
        $('#extend_debug_info').modalTrigger({
            size: 'lg',
            custom: function () {
                let ret = '';
                let parsed_sql_data = JSON.parse(_sql_data || '[]');
                let parsed_redis_data = JSON.parse(_redis_data || '{}');
                ret += '<b>SQL query list:</b><ul>';
                $.each(parsed_sql_data,function (i,v) {
                    ret += `<li><code>${v}</code></li>`;
                });
                ret += '</ul>';
                ret += '<b>Redis keys hit: (Some keys hit may not appear here)</b><ul>';
                $.each(parsed_redis_data,function (k, v) {
                    ret += '<li><code>' + k + "</code> : " + v + '</li>';
                });
                ret += '</ul>';
                return ret;
            }});
    }
});
