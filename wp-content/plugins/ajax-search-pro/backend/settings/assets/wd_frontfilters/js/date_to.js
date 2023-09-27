jQuery(function($){
    var controller = WD_FrontFilters;

    var type = 'date_to';
    var nodes = {
        'container': '.date_to_filter_container',
        'attributes': '.date_to_filter_attributes',
        'otherAttributes': '.date_to_filter_other_attributes',
        'label': '.date_to_filter_container input[attr="label.text"]',
        'displayMode': '.date_to_filter_attributes select[attr=display_mode]',
        'relDateContainer': '.date_to_filter_rel_date',
        'relDateYear': '.date_to_filter_rel_date input[attr="relative_date.year"]',
        'relDateMonth': '.date_to_filter_rel_date input[attr="relative_date.month"]',
        'relDateDay': '.date_to_filter_rel_date input[attr="relative_date.day"]',
        'dateContainer': '.date_to_filter_date',
        'datePicker': '.date_to_filter_date input[attr="date"]'
    };

    controller.module.register(new controller.tmp.dateFromClass(type, nodes));
});