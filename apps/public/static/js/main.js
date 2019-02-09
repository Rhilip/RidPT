jQuery(document).ready(function(){


    jQuery('#menu > ul').superfish({
        delay:       0,
        animation:   {
            opacity:'show',
            height:'show'
        },
        speed:       'fast',
        autoArrows:  false
    });
    (function() {
        var $menu = $('#menu ul'),
            optionsList = '<option value="" selected>Menu...</option>';

        $menu.find('li').each(function() {
            var $this   = $(this),
                $anchor = $this.children('a'),
                depth   = $this.parents('ul').length - 1,
                indent  = '';

            if( depth ) {
                while( depth > 0 ) {
                    indent += ' - ';
                    depth--;
                }
            }
            optionsList += '<option value="' + $anchor.attr('href') + '">' + indent + ' ' + $anchor.text() + '</option>';
        }).end().after('<select class="res-menu">' + optionsList + '</select>');

        $('.res-menu').on('change', function() {
            window.location = $(this).val();
        });

    })();
});

