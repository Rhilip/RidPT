;

// Declare Const
const api_point = '/api/v1';
const _location_search = new URLSearchParams(window.location.search);  // Short and parse location.search

/**
 * The icon map for file extension
 * Notice: The map key should has the fontawesome icon like `fa-file-${v}`
 * Knowledge:
 *  - file format of 'video','audio' are from MPC-HC : https://mpc-hc.org/
 *  - file format of 'image' are from arthurvr/image-extensions : https://github.com/arthurvr/image-extensions
 *  - file format of 'alt' are from sindresorhus/text-extensions: https://github.com/sindresorhus/text-extensions,
 *  And remove the duplicate element.
 *  - file format of 'archive are form Wikipedia : https://en.wikipedia.org/wiki/List_of_archive_formats
 *  - file format of 'word','powerpoint','excel' are from MicroSoft Office 365 ( Word, PowerPoint, Excel )
 */
const ext2Icon = {
    video: [
        "avi",     // AVI
        "mpg", "mpeg", "mpe", "m1v", "m2v", "mpv2", "mp2v", "pva", "evo", "m2p",  // MPEG
        "ts", "tp", "trp", "m2t", "m2ts", "mts", "rec", "ssif",   // MPEG-TS
        "vob", "ifo",   // DVD-Video
        "mkv", "mk3d",  // Matroska
        "webm",   // WebM
        "mp4", "m4v", "mp4v", "mpv4", "hdmov",  // MP4
        "mov",   // QuickTime-Video
        "3gp", "3gpp", "3ga",  // #GP
        "3g2", "3gp2",   // 3G2
        "flv", "f4v",   // Flash-Video
        "ogm", "ogv",   // Ogg
        "rm", "rmvb", "rt", "ram", "rpm", "rmm", "rp", "smi", "smil",  // Real Media with it's script
        "wmv", "wmp", "wm", "asf",   // Windows Media
        "smk", "bik",  // Smacker/Bink Video
        "fli", "flc", "flic",  // FLIC
        "dsm", "dsv", "dsa", "dss",  // DirectShow
        "divx", "amv",   // Other
        "asx", "m3u", "m3u8", "pls", "wvx", "wax", "wmx", "mpcpl",   // Play list
        "mpls", "bdmv"  // Blu-ray Play list
    ],
    audio: [
        "ac3",   // AC-3
        "dts", "dtshd", "dtsma",  // DTS/DTS-HD
        "aif", "aifc", "aiff",  // AIFF
        "alac",   // Apple Lossless
        "amr", // AMR
        "ape", "apl", // Monkey's Audio
        "au", "snd",  // AU/SND
        "cda",  // Audio Cd track
        "flac", // FLAC
        "m4a", "m4b", "m4r", "aac", // MPEG-4 Audio
        "mid", "midi", "rmi",  // MIDI
        "mka",  // Matroska
        "mp3",  // MP3
        "mpa", "mp2", "m1a", "m2a",  // MPEG Audio
        "mpc", // Musepack
        "ofr", "ofs", // OptimFROG
        "ogg", "oga", // Ogg Vorbis
        "opus", // Opus
        "ra",  // Real Audio
        "tak", // TAK
        "tta", // True Audio
        "wav", // WAV
        "wma", // Windows Media Audio
        "wv",  // WavPack
        "aob", "mlp", "thd"  // Other
    ],
    image: ["ase","art","bmp","blp","cd5","cit","cpt","cr2","cut","dds","dib","djvu","egt","exif","gif","gpl","grf","icns","ico","iff","jng","jpeg","jpg","jfif","jp2","jps","lbm","max","miff","mng","msp","nitf","ota","pbm","pc1","pc2","pc3","pcf","pcx","pdn","pgm","PI1","PI2","PI3","pict","pct","pnm","pns","ppm","psb","psd","pdd","psp","px","pxm","pxr","qfx","raw","rle","sct","sgi","rgb","int","bw","tga","tiff","tif","vtf","xbm","xcf","xpm","3dv","amf","ai","awg","cgm","cdr","cmx","dxf","e2d","egt","eps","fs","gbr","odg","svg","stl","vrml","x3d","sxd","v2d","vnd","wmf","emf","art","xar","png","webp","jxr","hdp","wdp","cur","ecw","iff","lbm","liff","nrrd","pam","pcx","pgf","sgi","rgb","rgba","bw","int","inta","sid","ras","sun","tga"],
    alt: ["ada","adb","ads","applescript","au3","as","asc","ascx","ascii","asm","asmx","asp","aspx","atom","awk","bas","bash","bashrc","bat","bbcolors","bcp","bdsgroup","bdsproj","bib","bowerrc","c","cbl","cc","cfc","cfg","cfm","cfml","cgi","clj","cljs","cls","cmake","cmd","cnf","cob","code-snippets","coffee","coffeekup","conf","cp","cpp","cpy","crt","cs","csh","cson","csproj","csr","css","csslintrc","ctl","curlrc","cxx","d","dart","dfm","diff","dof","dpk","dpr","dproj","dtd","eco","editorconfig","ejs","el","elm","emacs","eml","ent","erb","erl","eslintignore","eslintrc","ex","exs","f03","f77","f90","f95","fish","for","fpp","frm","fsproj","fsx","ftn","gemrc","gemspec","gitattributes","gitconfig","gitignore","gitkeep","gitmodules","go","gpp","gradle","groovy","groupproj","grunit","gtmpl","gvimrc","h","haml","hbs","hgignore","hh","hrl","hpp","hs","hta","htaccess","htc","htm","html","htpasswd","hxx","iced","iml","inc","ini","ino","irbrc","itcl","itermcolors","itk","jade","java","jhtm","jhtml","js","jscsrc","jshintignore","jshintrc","json","json5","jsonld","jsp","jspx","jsx","ksh","less","lhs","lisp","log","ls","lsp","lua","m","m4","mak","map","markdown","master","md","mdown","mdwn","mdx","metadata","mht","mhtml","mjs","mk","mkd","mkdn","mkdown","ml","mli","mm","mxml","nfm","nfo","noon","npmignore","npmrc","nuspec","nvmrc","ops","pas","pasm","patch","pbxproj","pch","pem","pg","php","php3","php4","php5","phpt","phtml","pir","pl","pm","pmc","pod","prettierrc","properties","props","pt","pug","purs","py","pyx","r","rake","rb","rbw","rc","rdoc","rdoc_options","resx","rexx","rhtml","rjs","rlib","ron","rs","rss","rst","rtf","rvmrc","rxml","s","sass","scala","scm","scss","seestyle","sh","shtml","sln","sls","spec","sql","sqlite","sqlproj","ss","sss","st","strings","sty","styl","stylus","sub","sublime-build","sublime-commands","sublime-completions","sublime-keymap","sublime-macro","sublime-menu","sublime-project","sublime-settings","sublime-workspace","sv","svc","swift","t","tcl","tcsh","terminal","tex","text","textile","tg","tk","tmLanguage","tmTheme","tmpl","tpl","tsv","tsx","tt","tt2","ttml","twig","txt","v","vb","vbproj","vbs","vcproj","vcxproj","vh","vhd","vhdl","vim","viminfo","vimrc","vm","vue","webapp","x-php","wsc","xaml","xht","xhtml","xml","xs","xsd","xsl","xslt","y","yaml","yml","zsh","zshrc"],
    archive: [
        'a', 'ar', 'cpio', 'shar', 'lbr', 'iso', 'mar', 'sbx', 'tar',  // Archiving only
        'bz2', 'f', 'gz', 'lz', 'lzma', 'lzo', 'rz', 'sfark', 'sz', 'xz', 'z', // Compression only
        '7z', 's7z', 'ace', 'afa', 'alz', 'apk', 'arc', 'arj', 'b1', 'b6z', 'ba', 'bh', 'cab', 'car', 'cfs', 'cpt', 'dar', 'dd', 'dgc', 'dmg',
        'ear', 'gca', 'ha', 'hki', 'ice', 'jar', 'kgb', 'lzh', 'lha', 'lzx', 'pak', 'partimg', 'paq6', 'paq7', 'paq8', 'pea', 'pim',
        'qda', 'rar', 'rk', 'sda', 'sea', 'sen', 'sfx', 'shk', 'sitx', 'sqx', 'tgz', 'tbz2', 'tlz', 'xz', 'txz', 'uc', 'uc0', 'uc2', 'ucn',
        'ur2', 'ue2', 'uca', 'war', 'wim', 'xar', 'xp3', 'yz1', 'zip', 'zipx', 'zoo', 'zpaq', 'zz', // Archiving and compression
        'ecc', 'ecsbx', 'par', 'par2', 'rev' // Data recovery
    ],
    word: ["doc", "docx", "docm", "dotx", "dotm", "dot", "odt"],
    powerpoint: ["ppt", "pptx", "pptm", "potx", "potm", "pot", "ppsx", "ppsm", "pps", "ppam", "ppa", "odp"],
    excel: ["xlsx", "xlsm", "xlsb", "xls", "xltx", "xltm", "xlt", "xlam", "xla", "ods"],
    pdf: ["pdf"],
    csv: ["csv"],
    code: [],
    contract: []
};

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

function get_ext_icon(ext) {
    for (let type in ext2Icon) {
        if (ext2Icon[type].indexOf(ext.toLowerCase()) >= 0) {
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
    
    // Captcha Img Re-flush
    let captcha_img_another = $('.captcha_img');
    captcha_img_another.on('click',function () {
        $(this).attr('src','/captcha?t=' + Date.now())  // Change src to get another captcha image
            .parent('.captcha_img_load').addClass('load-indicator loading');  // Add loading indicator in parent of img tag
    });
    captcha_img_another.on('load',function () {
        $(this).parent('.captcha_img_load').removeClass('load-indicator loading');
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
                    if (result.feedback.warning !== "") {feedback.push(result.feedback.warning);}
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

        // TODO Add Client Cache ( innodb ) since the file list will not change in specific torrent
        $.get(api_point + '/torrent/filelist', {'tid': tid}, function (res) {
            if (res.success) {
                let file_list = res.result;

                (new $.zui.ModalTrigger({
                    name: 'torrent_filelist_model',
                    showHeader: false,
                    size: 'lg',
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
    }
    
    // User Invite
    $('.invite-btn').click(function () {
        $('.invite-btn').removeAttr('disabled');
        $('#invite_form:hidden').show();

        let that = $(this);
        let invite_type = that.data('type');
        let invite_panel_notice = $('#invite_type');

        that.attr('disabled','disabled');
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
