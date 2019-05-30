;

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

jQuery(document).ready(function() {
    // Declare Const
    const api_point = '/api/v1';

    // Active tooltop
    $('[data-toggle="tooltip"]').tooltip();

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

            for (let k in tree) {
                let v = tree[k];
                if (typeof v == 'object') {
                    ret += `<li${par === '' ? ' class="open"':''}><a href="#">${k}</a><ul>${list_worker(v,par + "/" + k)}</ul></li>`;
                } else {
                    ret += `<li><i class="fa fa-file fa-fw"></i> ${k} (<span class="file-size" data-size="${v}">${humanFileSize(v)}</span>)</li>`;
                }
            }
            return ret;
        }

        $.get(api_point + '/torrent/filelist', {'tid': tid}, function (res) {
            if (res.success) {
                let file_list = res.result;
                (new $.zui.ModalTrigger({
                    name: 'torrent_filelist_model',
                    showHeader: false,
                    size: 'lg',
                    //width: '700px',
                    moveable: true,
                    custom: "<ul  class='tree tree-lines tree-folders' data-ride='tree' id='torrent_filelist'>" + list_worker(file_list) + "</ul>"
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
    }
});
