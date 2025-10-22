/* Javascript for resizable sidebar in WordPress admin interface */

jQuery(window).ready(function($) {
    setInterval(function() {
        // Set the width of the sidebar from localStorage or default to 300px
        $('.interface-interface-skeleton__sidebar').width(
            localStorage.getItem('adg_block_editor_sidebar_width') || '300px'
        );
        
        // Make the sidebar resizable
        $('.interface-interface-skeleton__sidebar').resizable({
            handles: 'w',
            resize: function(event, ui) {
                $(this).css({
                    'left': 0
                });
                // Store the new width in localStorage for each user
                localStorage.setItem('adg_block_editor_sidebar_width', $(this).width());
            }
        });
        
        adg_get_sidebar_status();
    }, 500);

    // Event listener for sidebar toggle buttons
    $('body').on('click', '.interface-pinned-items button', function() {
        adg_get_sidebar_status();
    });

    // Function to check the status of the sidebar and apply classes accordingly
    function adg_get_sidebar_status() {
        let is_sidebar_active = false;
        
        $('.interface-pinned-items button').each(function() {
            if ($(this).hasClass('is-pressed')) {
                is_sidebar_active = true;
            }
        });

        // Add or remove class for styling based on current sidebar status
        if (is_sidebar_active) {
            $('.edit-post-layout, .edit-site-layout').addClass('adg-sidebar-expanded');
        } else {
            $('.edit-post-layout, .edit-site-layout').removeClass('adg-sidebar-expanded');
        }
    }
});