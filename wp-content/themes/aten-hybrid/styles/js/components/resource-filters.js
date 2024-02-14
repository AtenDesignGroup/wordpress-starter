/**
 * Custom JS for Resource Filters
 */

jQuery( document ).ready(function($) {
    // Converting h4 labels from S&F into span tags
    $('.sf-field-post-meta-customizable_message h4').convertElement('span');

    // Converting h4 labels from S&F into span tags and tying labels to form elements
    $('.searchandfilter').find('li').each(function(){
        var field_element = $(this).find('input, select');
        var field_name = $(field_element).attr('name');
        $(field_element).attr('id', field_name);
        $(this).find('h4').wrap('<label for="' + field_name + '"></label>').convertElement('span');
        $(this).find('.sf-label-checkbox').attr('for', field_name);
    });
    
    // Disabling clear filter button until at least one filter value has been set
    $('.disabled-link').click(function(e){
        e.preventDefault();
    });
    $('.filter-container:not(.no-results-filters) .search-filter-reset').addClass('disabled-link');
    $('.searchandfilter input, .searchandfilter select').change(function(){
        if($(this).val() !== '') {
            $('.search-filter-reset').removeClass('disabled-link');
        }
    });
});