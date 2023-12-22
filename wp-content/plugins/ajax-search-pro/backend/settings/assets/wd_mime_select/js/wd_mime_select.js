(function($){
    /**
     * @description determine if an array contains one or more items from another array.
     * @param {array} haystack the array to search.
     * @param {array} arr the array providing items to check for in the haystack.
     * @return {boolean} true|false if haystack contains at least one item from arr.
     */
    let findOne = function (haystack, arr) {
        return arr.some(function (v) {
            return haystack.indexOf(v) >= 0;
        });
    };
    let mimes = {
        'pdf': [
            'application/pdf'
        ],
        'text' : [
            'text/plain',
            'text/csv',
            'text/tab-separated-values',
            'text/calendar',
            'text/css',
            'text/html'
        ],
        'richtext' : [
            'text/richtext',
            'application/rtf'
        ],
        'mso_word' : [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-word.document.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'application/vnd.ms-word.template.macroEnabled.12',
            'application/vnd.oasis.opendocument.text'
        ],
        'mso_excel' : [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel.sheet.macroEnabled.12',
            'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'application/vnd.ms-excel.template.macroEnabled.12',
            'application/vnd.ms-excel.addin.macroEnabled.12',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.chart',
            'application/vnd.oasis.opendocument.database',
            'application/vnd.oasis.opendocument.formula'
        ],
        'mso_powerpoint' : [
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.template',
            'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.graphics'
        ],
        'image': [
            'image/jpeg',
            'image/gif',
            'image/png',
            'image/bmp',
            'image/tiff',
            'image/x-icon'
        ],
        'video': [
            'video/x-ms-asf',
            'video/x-ms-wmv',
            'video/x-ms-wmx',
            'video/x-ms-wm',
            'video/avi',
            'video/divx',
            'video/x-flv',
            'video/quicktime',
            'video/mpeg',
            'video/mp4',
            'video/ogg',
            'video/webm',
            'video/x-matroska'
        ],
        'audio': [
            'audio/mpeg',
            'audio/x-realaudio',
            'audio/wav',
            'audio/ogg',
            'audio/midi',
            'audio/x-ms-wma',
            'audio/x-ms-wax',
            'audio/x-matroska'
        ]
    };

    $('.wd_MimeTypeSelect .file_mime_types_list select').select2().on('change', function(){
        let values = [];
        $(this).val().forEach(function(v){
            values.push(mimes[v].join(','));
        });
        $(this).closest('.wd_MimeTypeSelect').find('.file_mime_types_input textarea')
            .val(values.join(',')).trigger('input').trigger('click');
    });

    $('.wd_MimeTypeSelect .file_mime_types_input textarea').on('click keyup change cut paste', function(){
        let val = $(this).val().toLowerCase().replace(' ', '');
        let vals_arr = val.split(',');
        let selected_mimes = [];

        if ( vals_arr.length > 0 ) {
            $.each(vals_arr, function(i, v){
                vals_arr[i] = v.trim();
            });
            $.each(mimes, function(i, v){
                $.each(v, function(ii, vv){
                    mimes[i][ii] = vv.toLowerCase();
                });
            });
            $.each(mimes, function(k, arr){
                if ( findOne(arr, vals_arr) ) {
                    selected_mimes.push(k);
                }
            });
        }

        $(this).closest('.wd_MimeTypeSelect')
            .find('.file_mime_types_list select').val(selected_mimes).trigger('change.select2');
    }).trigger('click');

    $('span.mime_input_hide').on('click', function(){
        let $p = $(this).closest('.wd_MimeTypeSelect');
        $p.find('.file_mime_types_input').addClass('hiddend')
        $p.find('.file_mime_types_list').removeClass('hiddend');
    });
    $('span.mime_list_hide').on('click', function(){
        let $p = $(this).closest('.wd_MimeTypeSelect');
        $p.find('.file_mime_types_input').removeClass('hiddend')
        $p.find('.file_mime_types_list').addClass('hiddend');
        $p.find('.file_mime_types_input textarea').trigger('input');
    });
}(jQuery));
