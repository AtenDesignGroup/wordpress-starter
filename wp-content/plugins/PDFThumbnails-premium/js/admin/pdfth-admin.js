
jQuery(document).ready(function() {

    function pdfthSetActionToTab(id) {
        var frm = jQuery('#pdfth_form');
        frm.attr('action', frm.attr('action').replace(/(#.+)?$/, '#' + id));
    }

    jQuery('#pdfth-tabs').find('a').click(function () {
        jQuery('#pdfth-tabs').find('a').removeClass('nav-tab-active');
        jQuery('.pdfthtab').removeClass('active');
        var id = jQuery(this).attr('id').replace('-tab', '');
        jQuery('#' + id + '-section').addClass('active');
        jQuery(this).addClass('nav-tab-active');

        // Set submit URL to this tab
        pdfthSetActionToTab(id);
    });

    // Did page load with a tab active?
    var active_tab = window.location.hash.replace('#', '');
    if (active_tab != '') {
        var activeSection = jQuery('#' + active_tab + '-section');
        var activeTab = jQuery('#' + active_tab + '-tab');

        if (activeSection && activeTab) {
            jQuery('#pdfth-tabs').find('a').removeClass('nav-tab-active');
            jQuery('.pdfthtab').removeClass('active');

            activeSection.addClass('active');
            activeTab.addClass('nav-tab-active');
            pdfthSetActionToTab(active_tab);
        }
    }

    // Generate all thumbnails:

    jQuery('#pdfth-start-generate-all').on('click', function(e){
        var outputDiv = jQuery('<div></div>', {'class': 'pdfth-generate-all-output'});
        jQuery(e.target).replaceWith(outputDiv);
        pdfth_generate_all_thumbnails(outputDiv, jQuery('#pdfemb-onlynew').attr('checked'));
    });

});

