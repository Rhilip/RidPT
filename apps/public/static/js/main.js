;

// Declare Const
const api_point = '/api/v1';
const _location_search = new URLSearchParams(window.location.search);  // Short and parse location.search

const paswordStrengthText = {
    0: "Worst ☹",  // too guessable: risky password. (guesses < 10^3)
    1: "Bad ☹",    // too guessable: risky password. (guesses < 10^3)
    2: "Weak ☹",   // somewhat guessable: protection from unthrottled online attacks. (guesses < 10^8)
    3: "Good ☺",   // safely unguessable: moderate protection from offline slow-hash scenario. (guesses < 10^10)
    4: "Strong ☻"  // very unguessable: strong protection from offline slow-hash scenario. (guesses >= 10^10)
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
        search.set(i, new_params[i]);
    }
    return '?' + search.toString();
}

jQuery(document).ready(function () {
    // Drop all support of IE 6-11
    if ($.zui.browser.ie) {
        $.zui.browser.tip();
    }

    $('[data-toggle="tooltip"]').tooltip();  // Active tooltip
    $('[data-toggle="popover"]').popover();  // Active popover

    // Active Pager which source from remote
    $('ul[data-ride="remote_pager"]').pager({
        page: _location_search.get('page') || 0,
        maxNavCount: 8,
        elements: ['first_icon', 'prev_icon', 'pages', 'next_icon', 'last_icon'],
        linkCreator: function (page, pager) {
            return location_search_replace({
                'page': page,
                'limit': pager.recPerPage
            });
        }
    });

    // Captcha Img Re-flush
    let captcha_img_another = $('.captcha_img');
    captcha_img_another.on('click', function () {
        $(this).attr('src', '/captcha?t=' + Date.now())  // Change src to get another captcha image
            .parent('.captcha_img_load').addClass('load-indicator loading');  // Add loading indicator in parent of img tag
    });
    captcha_img_another.on('load', function () {
        $(this).parent('.captcha_img_load').removeClass('load-indicator loading');
    });

    // Form submit loading anime
    $('form').on('submit',function() {$(this).addClass('load-indicator loading')});

    // Clean form data in a modal
    $('.modal').on('hidden.zui.modal', function () {
        let that = $(this);
        if (that.find('form').length > 0) {
            let form = that.find('form');
            form[0].reset();
        }
    });

    $('.modal button[id$=_modal_save]').click(function () {
        $('.modal button[id$=_modal_close]').click();
        // By default , we don't need to clean the form data, Since the script in
        // main.js already have trigger to clean the form data when modal hidden.
    });

    // TODO Add Scroll to TOP fixbar

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
        if (old_type_is_password) {
            help_info.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            help_info.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

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
        const torrent_files_localforage = localforage.createInstance({name: 'torrent_files'});

        let that = $(this);
        let tid = that.attr('data-tid');

        torrent_files_localforage.getItem(tid, function (err, value) {
            function list_worker(tree, par = '') {
                let ret = '';
                let size = 0;
                for (let k in tree) {
                    let v = tree[k];
                    if (typeof v == 'object') {
                        let [in_ret, in_size] = list_worker(v, par + "/" + k);
                        ret += `<li${par === '' ? ' class="open"' : ''}><a href="#"><b>${k}</b> (<span class="file-size" data-size="${v}">${humanFileSize(in_size)}</span>)</a><ul>${in_ret}</ul></li>`;
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
                $.getJSON(api_point + '/torrent/filelist', {'tid': tid}, function (res) {
                    torrent_files_localforage.setItem(tid, res);
                    build_file_tree(res);
                });
            }
        });
    });

    // For torrents structure page
    if ($('#torrent_structure').length) {
        $('#torrent_structure div.dictionary,div.list').click(function () {
            $(this).next('ul').toggle();
        });
    }

    // User Invite
    $('.invite-btn').click(function () {
        $('.invite-btn').removeAttr('disabled');
        $('#invite_form:hidden').show();

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
                let parsed_sql_data = JSON.parse(_sql_data || '[]');
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
});
