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


layui.use(['layer', 'form', 'element', 'laypage', 'jquery'], function () {
    let $ = layui.jquery;
    let layer = layui.layer;
    let api_point = '/api/v1';

    // Convert ubbcode blcok text to html
    $(".ubbcode-block").html(function (index, oldhtml) {
        /*
        Because:
            1. Cloudflare or other CDN may clean the newline characters like `\n`
            2. our backend use nl2br() to Inserts HTML line breaks before all newlines in a string,
        It's needed to to remove the exist `\n` (if case 1 not happened), and change the html tag `<br />` to `\n`,
        then feed to our XBBCODE converter for safety output.
         */
        oldhtml = oldhtml.trim()
            .replace(/\n/ig, '')
            .replace(/<br ?\/?>/ig, '\n');
        return XBBCODE.process({text: oldhtml}).html;
    });
    // TODO Add [hide] support

    // Add/Remove favour action
    $('.torrent-favour').click(function () {
        let that = $(this);
        let tid = that.attr('data-tid');
        let star = that.find(' > i');

        $.post(api_point + '/torrent/bookmark', {'tid': tid}, function (res) {
            if (res.success) {
                let old_is_stared = star.hasClass('fas');
                star.toggleClass('fas', !old_is_stared).toggleClass('far', old_is_stared);
                layer.msg(`Torrent(${tid}) ${res.result} from your favour successfully`, {
                    icon: 6,
                    offset: 'rb',
                });
            } else {
                layer.alert(res.errors.join(', '), {icon: 2});
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
                    ret += "<li " + (par === "" ? "" : "style='display:none' data-par = \"" + par + "\" ") + ">";
                    ret += `<a href="javascript:" class="folder" data-folder-name="${k}"><i class="fa fa-folder fa-fw"></i> ${k}</a>`;
                    ret += `<ul>${list_worker(v, par + "/" + k)}</ul>`;
                    ret += '</li>';
                } else {
                    ret += `<li ` + (par === "" ? "" : "style='display:none' data-par = \"" + par + "\" ") + `><i class="fa fa-file fa-fw"></i> ${k} (<span class="file-size" data-size="${v}">${humanFileSize(v)}</span>)</li>`;
                }
            }
            return ret;
        }

        $.get(api_point + '/torrent/filelist', {'tid': tid}, function (res) {
            if (res.success) {
                let file_list = res.result;

                layer.open({
                    btn: [],
                    anim: 5,
                    shadeClose: true, //开启遮罩关闭
                    area: ['700px','500px'],
                    content: "<ul id='torrent-filelist'>" + list_worker(file_list) + "</ul>",
                    success: function (layero, index) {
                        $('#torrent-filelist a').click(function () {
                            let that = $(this);
                            let icon = that.find(' > i');
                            let parent = that.parents('li:eq(0)');

                            let old_is_open = icon.hasClass('fa-folder');
                            let par = parent.attr('data-par');
                            let expand = (par ? par : "") + "/" + that.attr('data-folder-name');

                            icon.toggleClass('fa-folder', !old_is_open).toggleClass('fa-folder-open', old_is_open);
                            $('#torrent-filelist li[data-par^="' + expand + '/"]').hide();  // 首先隐藏所有对应子项
                            $('#torrent-filelist li[data-par$="' + expand + '"]').toggle();  // 然后对当前项可见性进行切换
                        });
                    }
                });
            } else {
                layer.alert(res.errors.join(', '), {icon: 2});
            }
        })
    });

    // For torrents structure page
    if ($('#torrent-structure').length) {
        $('#torrent-structure div.dictionary,div.list').click(function () {
            $(this).next('ul').toggle();
        });
    }
});
