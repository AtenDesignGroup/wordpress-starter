/**
 * JS for Memberships forms
 **/

jQuery(document).ready(function($) {
    $('.pmpro_error:not(.pmpro_message)').each(function(){
        let el_name = $(this).attr('name');
        $('label[for="' + el_name + '"]').addClass('pmpro_error');
        $('<p class="error-description pmpro_error">This field is required</p>').insertAfter($(this));
    });

    $('.pmpro_checkout-field-required, .pmpro_checkout-field').each(function(){
        $(this).find('.pmpro_asterisk').detach().appendTo($(this).find('label'));
    });

    $('#pmpro_checkout_box-more-information h2').remove();

    $('.pmpro-checkout #pmpro_btn-submit').attr('value', 'Submit Registration');
});