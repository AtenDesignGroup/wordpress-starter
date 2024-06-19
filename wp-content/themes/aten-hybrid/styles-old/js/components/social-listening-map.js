/**
 * Custom JS for the Social Listening Map block
 */

jQuery( document ).ready(function($) {
    // Disabling a link until enabled by another event
    $('.disabled-link').click(function(e){
        // Check that it is still disabled by class
        if($(this).hasClass('disabled-link')) {
            e.preventDefault();
        }
    });

    // When state select field is manually changed without map
    $('#state-selector').on('change', function() {
        // Get the state and its abbreviation
        var abbreviation = $(this).find(":selected").val();
        var state = $(this).find(":selected").text();
        // Update the button accordingly
        updateStateDashboardButton(state, abbreviation);
    });

    // Dispatch text to the aria-live region when a state is selected
    function dispatchStateChange(state) {
        // Announce the new region selection 
        $('#selected-state-information h3').text('The currently selected region is ' + state);
    }

    // Update the selected state dynamically
    function updateStateSelectField(state, abbreviation) {
        // Set the value
        $('#state-selector').val(abbreviation);
        // Update the button
        updateStateDashboardButton(state, abbreviation);
    }

    // Update link text and url based on selected state
    function updateStateDashboardButton(state, abbreviation) {
        // Enabling the link
        $('#state-dashboard-external-link').removeClass('disabled-link');
        // Setting the link to the hidden field value from ACF
        var state_dashboard_link = $('#state-link-' + abbreviation).val();
        // // Updating link text
        // $('#state-dashboard-external-link').text('Go to ' + state + ' dashboard');
        // Updating link URL
        $('#state-dashboard-external-link').attr('href', state_dashboard_link);
        // Dispatch aria-live update
        dispatchStateChange(state);
    }
});