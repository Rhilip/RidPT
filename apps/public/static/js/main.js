;layui.use(['layer', 'form','element','laypage','jquery'], function(){
    let $ = layui.jquery;
    let layer = layui.layer;
    let api_point = '/api/v1';

    // Add/Remove favour action
    $('.torrent-favour').click(function () {
        let that = $(this);
        let tid = that.attr('data-tid');
        let star = that.find(' > i');

        $.post(api_point + '/torrents/bookmark', {'tid': tid}, function (res) {
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
});
