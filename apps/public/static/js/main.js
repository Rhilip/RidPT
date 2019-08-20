;
if (!String.prototype.format) {
    String.prototype.format = function () {
        let args = arguments;
        if (args.length === 1 && args[0] !== null && typeof args[0] === 'object') {
            args = args[0];
        }
        return this.replace(/{([^}]*)}/g, function(match, key) {
            return (typeof args[key] !== "undefined" ? args[key] : match);
        });
    };
}

// Declare Const
const api_point = '/api/v1';
const _location_search = new URLSearchParams(window.location.search);  // Short and parse location.search

const paswordStrengthText = {
    0: "Worst",  // too guessable: risky password. (guesses < 10^3)
    1: "Bad",    // too guessable: risky password. (guesses < 10^3)
    2: "Weak",   // somewhat guessable: protection from unthrottled online attacks. (guesses < 10^8)
    3: "Good",   // safely unguessable: moderate protection from offline slow-hash scenario. (guesses < 10^10)
    4: "Strong"  // very unguessable: strong protection from offline slow-hash scenario. (guesses >= 10^10)
};

const wysibbSetting = {
    buttons: "bold,italic,underline,strike,sup,sub,|,img,link,|,bullist,numlist,smilebox,|,fontcolor,fontsize,fontfamily,|,justifyleft,justifycenter,justifyright,|,quote,code,table,removeFormat",
    allButtons: {},
    smileList: [
        /* {img: '<img src="/static/pic/smilies/1.gif" class="sm">', bbcode:"[em]1[/em]"}, */
    ],
};

const external_info_format = {
    douban: `<div>{format}</div>`,
    imdb: `<div>{format}</div>`,
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

function randomString(length = 16, charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
    let result = '';
    let charactersLength = charset.length;
    for (let i = 0; i < length; i++) {
        result += charset.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

function locationSearchReplace(new_params) {
    let search = _location_search;
    for (let i in new_params) {
        search.set(i, new_params[i]);
    }
    return '?' + search.toString();
}

function checkCookie() {
    let cookieEnabled = navigator.cookieEnabled;
    if (!cookieEnabled) {
        document.cookie = "cookiebar";
        cookieEnabled = document.cookie.indexOf("cookiebar") !== -1;
    }
    return cookieEnabled;
}

jQuery(document).ready(function () {
    // Cache Field
    const cache_torrent_files = localforage.createInstance({name: 'torrent_files'});
    const cache_torrent_nfo = localforage.createInstance({name: 'torrent_nfo'});
    const cache_external_info = localforage.createInstance({name: 'external_info'});

    // Other Global const
    const $body = $('html, body');

    // Init Page
    if ($.zui.browser.ie) $.zui.browser.tip();  // Drop all support of IE 6-11
    if (!checkCookie()) $.zui.browser.tip('Cookie support are required for visit our site.');

    $('[data-toggle="tooltip"]').tooltip();  // Active tooltip
    $('[data-toggle="popover"]').popover();  // Active popover
    $('textarea[data-autoresize]').autoresize();  // Active autoresize of textarea

    // Active Pager which source from remote
    $('ul[data-ride="remote_pager"]').pager({
        page: _location_search.get('page') || 0,
        maxNavCount: 8,
        elements: ['first_icon', 'prev_icon', 'pages', 'next_icon', 'last_icon'],
        linkCreator: function (page, pager) {
            return locationSearchReplace({
                'page': page,
                'limit': pager.recPerPage
            });
        }
    });

    // Active Nav `active` class by location.pathname
    if ($('nav#nav').length) {
        let pathname_split = window.location.pathname.split('/');
        for (let i = pathname_split.length; i > 0; i--) {
            let test_pathname = pathname_split.slice(0, i).join('/');
            let test_nav_li = $(`nav#nav li > a[href="${test_pathname}"]`);
            if (test_nav_li.length) {
                test_nav_li.parent('li').addClass('active');
                break;
            }
        }
    }

    // Active Editor
    $('textarea').click(function () {
        let that = $(this);
        if (that.hasClass('to-load-editor')) {
            that.removeClass('to-load-editor').wysibb(wysibbSetting).addClass('loaded-editor');
        }
    });

    // Captcha Img Re-flush
    let captcha_img_another = $('.captcha_img');
    captcha_img_another.on('click', function () {
        $(this).attr('src', `/captcha?t=${Date.now()}&r=${randomString(6)}`)  // Change src to get another captcha image
            .parent('.captcha_img_load').addClass('load-indicator loading');  // Add loading indicator in parent of img tag
    });
    captcha_img_another.on('load', function () {
        $(this).parent('.captcha_img_load').removeClass('load-indicator loading');
    });

    // Form submit loading animation
    $('form').on('submit', function () {
        $(this).addClass('load-indicator loading')
    });

    // Clean form data in a modal when it hidden
    $('.modal').on('hidden.zui.modal', function () {
        let that = $(this);
        if (that.find('form').length > 0) {
            let form = that.find('form');
            form[0].reset();
        }
    });

    // Close modal when click save button
    $('.modal button[id$=_modal_save]').click(function () {
        let that = $(this);
        let close_btn = that.parents('.modal').find('button[id$=_modal_close]');
        if (close_btn) close_btn.click();
    });

    // Add Scroll to TOP fixbar
    let scrollTop = $("#scroll_top");
    $(window).scroll(function() {
        let topPos = $(this).scrollTop();         // declare variable
        scrollTop.css("opacity", topPos > 100 ? 1 : 0);  // if user scrolls down - show scroll to top button
    }); // scroll END
    scrollTop.click(function() {
        $body.animate({
            scrollTop: 0
        }, 800);
        return false;
    });

    // Common Function
    function create_error_notice(text, option) {
        option = $.extend({
            icon: 'exclamation-sign',
            type: 'danger',
            placement: 'top-right'
        }, option);
        return new $.zui.Messager(text, option).show();
    }

    // Password strength checker
    let password_strength = $('#password_strength');
    if (password_strength.length > 0) {
        let strength_text = $('#password_strength_text');
        let strength_suggest = $('#password_strength_suggest');
        $('#password').on('input', function () {
            let val = $(this).val();
            if (val !== "") {
                try {
                    let result = zxcvbn(val);
                    password_strength.show();
                    strength_text.html(paswordStrengthText[result.score]);
                    let feedback = [];
                    if (result.feedback.warning !== "") {
                        feedback.push(result.feedback.warning);
                    }
                    feedback = feedback.concat(result.feedback.suggestions);
                    if (feedback.length > 0) {
                        strength_suggest.html('<ul><li>' + feedback.join('</li><li>') + '</li></ul>');
                    }
                } catch (e) {
                }
            } else {
                password_strength.hide();
                strength_suggest.text('');
            }
        })
    }

    $('#password_help_btn').click(function () {
        let password_input = $(this).prev('input[name="password"]');
        let help_info = $(this).children('i');
        let old_type_is_password = password_input.attr('type') === 'password';
        password_input.attr('type', old_type_is_password ? 'text' : 'password');
        help_info.toggleClass('fa-eye-slash', old_type_is_password).toggleClass('fa-eye', !old_type_is_password);
    });

    // Torrent favour Add/Remove action
    $('.torrent-favour').click(function () {
        let that = $(this);
        let tid = that.data('tid');
        let star = that.find(' > i');

        $.post(api_point + '/torrent/bookmark', {'id': tid}, function (res) {
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
    let ext2Icon = $.zui.store.get('rid_ext2Icon');
    if (!ext2Icon) {
        $.getJSON('/static/json/ext2Icon.json', function (data) {
            ext2Icon = data;
            $.zui.store.set('rid_ext2Icon', ext2Icon);
        })
    }

    function get_ext_icon(ext) {
        for (let type in ext2Icon) {
            if (ext2Icon[type].indexOf(ext.toLowerCase()) >= 0) {
                return 'fa-file-' + type;
            }
        }
        return "fa-file";
    }

    $('.torrent-files').click(function () {
        let that = $(this);
        let tid = that.data('tid');

        cache_torrent_files.getItem(tid, function (err, value) {
            function list_worker(tree, par = '') {
                let ret = '';
                let size = 0;
                for (let k in tree) {
                    let v = tree[k];
                    if (typeof v == 'object') {
                        let [in_ret, in_size] = list_worker(v, par + "/" + k);
                        ret += `<li${par === '' ? ' class="open"' : ''}><a href="#"><b>${k}</b> (<span class="file-size" data-size="${in_size}">${humanFileSize(in_size)}</span>)</a><ul>${in_ret}</ul></li>`;
                        size += in_size;
                    } else {
                        let ext = k.substr(k.lastIndexOf('.') + 1).toLowerCase();

                        ret += `<li><i class="fa ${get_ext_icon(ext)} fa-fw"></i><b>${k}</b> (<span class="file-size" data-size="${v}">${humanFileSize(v)}</span>)</li>`;
                        size += v;
                    }
                }
                return [ret, size];
            }

            function build_file_tree(res) {
                if (res.success) {
                    let file_list = res.result;

                    (new $.zui.ModalTrigger({
                        name: 'torrent_filelist_model',
                        showHeader: false,
                        size: 'lg',
                        moveable: true,
                        custom: "<ul  class='tree tree-lines tree-folders' data-ride='tree' id='torrent_filelist'>" + list_worker(file_list)[0] + "</ul>"
                    })).show({
                        shown: function () {
                            $('#torrent_filelist').tree();
                        }
                    });
                } else {
                    create_error_notice(res.errors.join(', '));
                }
            }

            if (value !== null) {
                build_file_tree(value);
            } else {
                $.getJSON(api_point + '/torrent/filelist', {'id': tid}, function (res) {
                    cache_torrent_files.setItem(tid, res);
                    build_file_tree(res);
                });
            }
        });
    });

    $('.torrent-nfo').click(function () {
        let that = $(this);
        let tid = that.data('tid');

        cache_torrent_nfo.getItem(tid, function (err, value) {
            function build_nfo_modal(res) {
                if (res.success) {
                    (new $.zui.ModalTrigger({
                        name: 'torrent_nfo_content_model',
                        showHeader: false,
                        size: 'lg',
                        moveable: true,
                        custom: `<pre>${res.result}</pre>`
                    })).show();
                } else {
                    create_error_notice(res.errors.join(', '));
                }
            }

            if (value !== null) {
                build_nfo_modal(value);
            } else {
                $.getJSON(api_point + '/torrent/nfofilecontent', {'id': tid}, function (res) {
                    cache_torrent_nfo.setItem(tid, res);
                    build_nfo_modal(res);
                });
            }
        });
    });

    // User Invite
    $('.invite-btn').click(function () {
        $('.invite-btn').removeAttr('disabled');
        $('#invite_form:hidden').show();

        $body.animate({
            scrollTop: $('#invite_form').position().top
        }, 500);

        let that = $(this);
        let invite_type = that.data('type');
        let invite_panel_notice = $('#invite_type');

        that.attr('disabled', 'disabled');
        $('#invite_create_form input[name=invite_type]').val(invite_type);
        if (invite_type === 'temporarily') {
            $('#invite_create_form input[name=temp_id]').val(that.data('temp-invite-id'));
            invite_panel_notice.text('(Using Temporarily Invite - ' + that.data('id') + ')');
        } else {
            invite_panel_notice.text('(Using Permanent Invite)');
        }
    });

    // Show Extend debug info of Database sql execute and Redis key hit
    if (typeof _extend_debug_info !== 'undefined' && _extend_debug_info) {
        $('#extend_debug_info').modalTrigger({
            size: 'lg',
            custom: function () {
                let ret = '';
                let parsed_sql_data = JSON.parse(_sql_data.replace(/\n/g, ' ').replace(/ {2,}/g, ' ') || '[]');
                let parsed_redis_data = JSON.parse(_redis_data || '{}');
                ret += '<b>SQL query list:</b><ul>';
                $.each(parsed_sql_data, function (i, v) {
                    ret += `<li><code>${v}</code></li>`;
                });
                ret += '</ul>';
                ret += '<b>Redis keys hit: (Some keys hit may not appear here)</b><ul>';
                $.each(parsed_redis_data, function (k, v) {
                    ret += '<li><code>' + k + "</code> : " + v + '</li>';
                });
                ret += '</ul>';
                return ret;
            }
        });
    }

    // Help to insert tags in `/torrents/upload`
    if ($('div.tag-help-block').length) {
        let tags_input = $('input[name="tags"]');
        $('a.add-tag').click(function (e) {
            e.preventDefault();
            let add_tag = $(this).text();
            let exist_tag_value = tags_input.val();
            let exist_tag_set = new Set(exist_tag_value.split(' '));
            if (exist_tag_set.has(add_tag)) {
                exist_tag_set.delete(add_tag);
            } else if (exist_tag_set.size < 10 /* Max tags size */) {
                exist_tag_set.add(add_tag);
            }
            tags_input.val(Array.from(exist_tag_set).join(' '));
        })
    }

    $('#external_info_label > a[data-toggle="collapse"][data-type][data-id]').click(function () {
        let that = $(this);
        let type = that.data('type');
        let id = that.data('id');
        let div = $(`#external_info div#info_${type}`);

        if (!div.hasClass('info-loading')) return;  // not cache or remote hit if html data exist

        let cache_key = `${type}:${id}`;
        cache_external_info.getItem(cache_key, function (err, value) {
            function build_external_info(value) {
                $('#external_info').show();

                // FIXME insert external info div
                let ret = (external_info_format[type] || '<div>{format}</div>').format(value).replace(/\n/ig, '<br>');
                div.html(ret).removeClass('info-loading');
            }

            if (value !== null) {
                build_external_info(value);
            } else {
                $.getJSON('https://ptgen.rhilip.workers.dev/', {'site': type, 'sid' : id}, function (res) {
                    cache_external_info.setItem(cache_key, res);
                    build_external_info(res);
                });
            }
        });
    });

    $('.link-edit').click(function () {
        let that = $(this);

        $('#links_modal').modal();

        // Get link data from <tr> and Fill link data to form
        let tr = $('#links_manager_table tr[data-id=' + that.data('id') + ']');
        let link_edit_form = $('#link_edit_form');
        for (let datum in tr.data()) {
            link_edit_form.find('[name="link_' + datum + '"]').val(tr.data(datum));
        }
    });

    $('.link-remove').click(function () {
        let that = $(this);
        if (confirm('Confirm to remove this links ?')) {
            let link_remove_form = $('#link_remove_form');
            link_remove_form.find('input[name=link_id]').val(that.data('id'));
            link_remove_form.submit();
        }
    });

    $('.cat-edit').click(function () {
        let that = $(this);

        $('#cat_modal').modal();

        // Get category data from <tr> and Fill data to form
        let tr = $('#cat_manager_table tr[data-id=' + that.data('id') + ']');
        let cat_edit_form = $('#cat_edit_form');
        for (let datum in tr.data()) {
            let input = cat_edit_form.find('[name="cat_' + datum + '"]');
            if (datum === 'enabled') {
                input.prop('checked', tr.data(datum) ? 'checked' : '');
            } else {
                input.val(tr.data(datum));
            }
        }
    });

    $('.cat-remove').click(function () {
        let that = $(this);
        if (confirm('Confirm to remove this Category ?')) {
            let cat_remove_form = $('#cat_remove_form');
            cat_remove_form.find('input[name=cat_id]').val(that.data('id'));
            cat_remove_form.submit();
        }
    });

    $('.subs_delete').click(function () {
        let that = $(this), subs_delete_form = $('#subs_delete_form');
        let sub_id = that.data('id');
        let reason = prompt("What's delete reason?");

        if (reason.length > 0 && subs_delete_form) {
            subs_delete_form.find("input[name='id']").val(sub_id);
            subs_delete_form.find("input[name='reason']").val(reason);
            subs_delete_form.submit();
        } else {
            alert('Empty delete reason or no subtitle delete form exist.')
        }
    })
});
