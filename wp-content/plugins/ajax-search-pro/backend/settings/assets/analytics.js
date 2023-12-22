jQuery(function($){
    $('select[name=analytics]').on('change', function(){
        var v = $(this).val();
        if ( v == '0' ) {
            $('.asp_al_pageview').addClass('hiddend');
            $('.asp_al_event').addClass('hiddend');
            $('.asp_al_both').addClass('hiddend');
        } else if ( v == 'pageview' ) {
            $('.asp_al_pageview').removeClass('hiddend');
            $('.asp_al_event').addClass('hiddend');
            $('.asp_al_both').removeClass('hiddend');
        } else if ( v == 'event' ) {
            $('.asp_al_pageview').addClass('hiddend');
            $('.asp_al_event').removeClass('hiddend');
            $('.asp_al_both').removeClass('hiddend');
        }
    }).trigger('change');
    $('.asp_gtag_switch input[isparam]').on('change', function(){
        if ( $(this).val() == 1 ) {
            $(this).closest('fieldset').find('.asp_gtag_inputs').removeClass('disabled');
        } else {
            $(this).closest('fieldset').find('.asp_gtag_inputs').addClass('disabled');
        }
    }).trigger('change');
    $('.asp_submit_reset').on('click', function(){
        if(confirm('Do you really want to reset the options to defaults?')) {
            return true;
        }
        return false;
    });
});