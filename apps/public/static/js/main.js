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
});
