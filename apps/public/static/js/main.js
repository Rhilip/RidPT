;layui.use(['layer', 'form','element','laypage','jquery'], function(){
    let $=layui.jquery;

    // Add favour action
    $('.torrent-favour > i').click(function () {
        let star = $(this);
        // TODO Do ajax to api, if success then change the star
        let old_is_stared = star.hasClass('fas');
        star.toggleClass('fas',!old_is_stared).toggleClass('far',old_is_stared);
        new NoticeJs({
            text: 'Torrent add/remove from your favour',
            position: 'bottomRight',
        }).show();
        // TODO Notice user
    })
});
