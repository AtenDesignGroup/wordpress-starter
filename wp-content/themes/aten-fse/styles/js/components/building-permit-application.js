/**
 * Custom JS for the Building Permit Application Form
 */
jQuery(document).on('gform_post_render', function(){
    // Apply to input with class 'gf_readonly' 
    jQuery(".gf_readonly input").attr("readonly","readonly");

    // Checking hidden section checkboxes when a value within that section is changed
    jQuery('.building-fields input').change(function(){
       checkForSectionValues('building-fields', '152');
    });
    jQuery('.mechanical-fields input').change(function(){
        checkForSectionValues('mechanical-fields', '154');
    });
    jQuery('.plumbing-fields input').change(function(){
        checkForSectionValues('plumbing-fields', '153');
    });
    jQuery('.electrical-fields input').change(function(){
        checkForSectionValues('electrical-fields', '156');
    });
    jQuery('.signs-fields input').change(function(){
        checkForSectionValues('signs-fields', '155')
    });
    jQuery('.fire-fields input').change(function(){
        checkForSectionValues('fire-fields', '157');
    });
    jQuery('.roofing-fields input').change(function(){
        checkForSectionValues('roofing-fields', '158');
    });

    // Check for values within a field section
    function checkForSectionValues(section_class, checkbox_field_ID) {
        // Set a boolean flag to determine if values are present on each check
        var valuesPresent = false;
        // For each input field in that section
        jQuery('.' + section_class + ' input').each(function(){
            // If there is an input value or a checked selection
            if(jQuery(this).val() || jQuery(this).is(':checked')) {
                // Raise the value present flag
                valuesPresent = true;
            }
        });
        // If there is a value present in the section
        if(valuesPresent) {
            // Check that section's hidden box
            jQuery( '#choice_8_' + checkbox_field_ID + '_1' ).prop( "checked", true );
        } else {
            // If no values are present, uncheck the section's hidden box
            jQuery( '#choice_8_' + checkbox_field_ID + '_1' ).prop( "checked", false );
        }
    }
});


