jQuery(function($){
    $('.tabs').on('click', 'a.tab_disabled', function (e) {
        var $li = $(this).closest('li');
        var $delegate = $li.nextUntil().find('a:not(.tab_disabled)');
        if ( $delegate.length > 0 ) {
            $delegate.first().trigger('click');
        } else {
            $delegate = $li.prevUntil().find('a:not(.tab_disabled)');
            if ( $delegate.length > 0 ) {
                $delegate.last().trigger('click');
            }
        }
    });

    $('.tabs').on('click', 'a:not(".tab_disabled")', function (e) {
        e.preventDefault();
        var tid = $(this).attr('tabid');
        var tabsContent = $(this).parent().parent().next();

        tabsContent.children().each(function () {

            // Form nested tabs
            if ($(this).is('form')) {

                // Hackidy-hack. Yea, hide this form, later if this is the active one we show it..
                // .. so the non-hidden content of the form is not present on other tabs
                // .. whatever man, STOP QUESTIONING MY METHODS
                $(this).hide(0);
                $form = $(this);

                // This is should be done with a recursive call, but meh...
                $(this).children().each(function () {
                    // Only apply to nodes with the tabid attribute
                    if ($(this).is('[tabid]')) {
                        $(this).hide(0);
                        if ($(this).attr('tabid') == tid) {
                            $form.fadeIn(0);
                            $(this).fadeIn(0);
                        }
                    }
                });
                return;
            }

            // Only apply to nodes with the tabid attribute
            if ($(this).is('[tabid]')) {
                $(this).hide(0);
                if ($(this).attr('tabid') == tid) {
                    $(this).fadeIn(0);
                }
            }

        });

        $('a', $(this).parent().parent()).removeClass('current');
        $(this).addClass('current');
    });
});