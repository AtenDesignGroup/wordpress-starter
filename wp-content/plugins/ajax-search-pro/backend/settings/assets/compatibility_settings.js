// Simulate a click on the first element to initialize the tabs
jQuery(function ($) {
    // Remove the # from the hash, as different browsers may or may not include it
    var hash = location.hash.replace('#','');

    if(hash != ''){
        hash = parseInt(hash);
        $('.tabs a[tabid=' + Math.floor( hash / 100 ) + ']').trigger('click');
        $('.tabs a[tabid=' + hash + ']').trigger('click');
    } else {
        $('.tabs a[tabid=1]').trigger('click');
    }

    $('.tabs a').on('click', function(){
        location.hash = $(this).attr('tabid');
    });

    $('input[name=selective_enabled]').on('change', function(){
       if ( $(this).val() == 0 ) {
           $(this).closest('fieldset').find('.item_selective_load').addClass('disabled');
       } else {
           $(this).closest('fieldset').find('.item_selective_load').removeClass('disabled');
       }
    }).trigger('change');

    $('select[name=js_source]').on('change', function(){
       if ( $(this).val().indexOf('jqueryless') < 0 ) {
           $('select[name=script_loading_method]').closest('.item').addClass('disabled');
       } else {
           $('select[name=script_loading_method]').closest('.item').removeClass('disabled');
       }
    }).trigger('change');


});