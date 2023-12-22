/**
 * Custom JS for the Building Inspection Request Form
 */
jQuery(document).on('gform_post_render', function(){
    // Wrapping text inside external form in span tags for show/hide capability
    jQuery('#external-inspection-form-wrapper').contents().filter(function(){
        return this.nodeType == 3 && jQuery.trim(this.nodeValue).length;
    }).wrap('<span />');
    // Move hidden form down into page
    jQuery('#external-inspection-form-wrapper').prependTo('#gform_page_11_2').removeClass('dynamically-hidden');
    // Add class to intro block of information so it always displays
    var displayedFieldset = jQuery("fieldset:contains('Permit Information')");
    jQuery(displayedFieldset).find('label').convertElement('p');
    jQuery(displayedFieldset).find('br').remove();

    setTimeout(function() {
        jQuery(displayedFieldset).addClass('external-form-visible');
      }, 300);
    // Check for hidden building or electrical select fields, if not there hide via CSS
    if(jQuery("#BI").length == 0) {
        jQuery("#input_11_23").closest('.gfield').addClass("dynamically-hidden");
    }
    if(jQuery("#EPC").length == 0) {
        jQuery("#input_11_24").closest('.gfield').addClass("dynamically-hidden");
    }

    // Hiding external form from screen readers
    jQuery('#external-inspection-form-wrapper input, #external-inspection-form-wrapper select, #external-inspection-form-wrapper textarea, #external-inspection-form-wrapper button').attr('aria-hidden', 'true');
    // Adding labels to external form for a11y
    jQuery('.oxridata[name="name"]').attr('aria-label', 'Contact Name');
    jQuery('#oxriphone').attr('aria-label', 'Contact Phone');
    jQuery('#oxriemail').attr('aria-label', 'Contact Email');
    jQuery('#oxridate').attr('aria-label', 'Inspection Date');
    jQuery('.oxridata[name="time"]').attr('aria-label', 'Preferred Time');
    jQuery('#BI').attr('aria-label', 'Building Inspection');
    jQuery('#EPC').attr('aria-label', 'Electrical Inspection');
    jQuery('#misc').attr('aria-label', 'Miscellaneous Comments');
    jQuery('#external-inspection-form-wrapper fieldset:not(:contains(Permit Information))').prepend('<legend>Contact Information</legend>');
    
    // Inserting external form into the Gravity Forms wrapper
    jQuery("#gform_submit_button_11").on("click", function(event) {
        // Get the values from the input fields
        var gf_name = jQuery("#input_11_7").val();
        var gf_phone = jQuery("#input_11_19").val();
        var gf_email = jQuery("#input_11_20").val();
        // Concatenating the d/m/y fields into a single string value
        var gf_date = jQuery("#input_11_21_1").val() + '/' + jQuery("#input_11_21_2").val() + '/' + jQuery("#input_11_21_3np").val();
        var gf_time = jQuery("#input_11_22").val();
        var gf_building = jQuery("#input_11_23").val();
        var gf_electrical = jQuery("#input_11_24").val();
        var gf_misc = jQuery("#input_11_14").val();
        // Set the values to the hidden fields with the corresponding IDs
        jQuery("input[name='name']").val(gf_name);
        jQuery("#oxriphone").val(gf_phone);
        jQuery("#oxriemail").val(gf_email);
        jQuery("#oxridate").val(gf_date);
        jQuery("#misc").val(gf_misc);
        // Select Elements
        jQuery(".oxridata[name='time'] option[value='" + gf_time + "'").prop("selected", true);
        jQuery("#BI option[value='" + gf_building + "'").prop("selected", true);
        jQuery("#EPC option[value='" + gf_electrical + "'").prop("selected", true);

        // Trigger the click event of the other button
        jQuery("#oxrisend").click();
    });
});


