/**
 * Custom JS for the Building Inspection Request Form
 */
jQuery(document).ready(function($) {
    // Add the event listener for the click event on the submit button (if needed)
    $('#gform_submit_button_11').on('click', function(event) {
        // Trigger the click event of the other button

        // Get the value from the input field with the name "name"
        var oxridate_name = $("input[name='name']").val();
        var oxridate_phone  = $("#oxriphone").val();
        var oxridate_email  = $("#oxriemail").val();
        var oxridate_date  = $("#oxridate").val();

        // Set the values to the hidden fields with the corresponding IDs
        $("#input_11_7").val(oxridate_name);
        $("#input_11_8").val(oxridate_phone);
        $("#input_11_9").val(oxridate_email);
        $("#input_11_10").val(oxridate_date);

        //Select Elements
        var building_inspection = $("#BI").val();
        $(".hidden_BI").val(building_inspection);

        var electrical_inspection = $("#EPC").val();
        $(".hidden_EPC").val(electrical_inspection);

        $('#oxrisend').click();
    });
});


