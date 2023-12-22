<?php

// Hooks your functions into the correct filters
add_action('admin_head', 'wpdreams_asp_add_mce_button');
function wpdreams_asp_add_mce_button() {
    // check user permissions
    if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
        return;
    }
    // check if WYSIWYG is enabled
    if ( 'true' == get_user_option( 'rich_editing' ) ) {
        add_filter( 'mce_external_plugins', 'wpdreams_asp_add_tinymce_plugin' );
        add_filter( 'mce_buttons', 'wpdreams_asp_register_mce_button' );
    }
}
add_action('admin_head', 'wpdreams_asp_add_mce_button');

// Declare script for new button
function wpdreams_asp_add_tinymce_plugin( $plugin_array ) {
    $plugin_array['wpdreams_asp_mce_button'] = plugins_url()."/ajax-search-pro/backend/tinymce/buttons.js";
    return $plugin_array;
}

// Register new button in the editor
function wpdreams_asp_register_mce_button( $buttons ) {
    array_push( $buttons, 'wpdreams_asp_mce_button' );
    return $buttons;
}

// Generate the buttons JS variable
add_action('admin_head', 'wpdreams_asp_mce_generate_variable');
function wpdreams_asp_mce_generate_variable($settings) {
    $menu_items = array();
    $menu_result_items = array();
    $menu_setting_items = array();
    $menu_two_column_items = array();

    foreach (wd_asp()->instances->get() as $x => $instance) {
        $id = $instance['id'];
        $menu_items[] = "{text: 'Search $id (".preg_replace("/[^\w\d ]/ui", '', esc_attr( $instance['name'] )).")',onclick: function() {editor.insertContent('[wpdreams_ajaxsearchpro id=$id]');}}";
        $menu_result_items[] = "{text: 'Results $id (".preg_replace("/[^\w\d ]/ui", '', esc_attr( $instance['name'] )).")',onclick: function() {editor.insertContent('[wpdreams_ajaxsearchpro_results id=$id element=div]');}}";
        $menu_setting_items[] = "{text: 'Settings $id (".preg_replace("/[^\w\d ]/ui", '', esc_attr( $instance['name'] )).")',onclick: function() {editor.insertContent('[wpdreams_asp_settings id=$id]');}}";
        $menu_two_column_items[] = "{text: 'Two column layout for $id (".preg_replace("/[^\w\d ]/ui", '', esc_attr( $instance['name'] )).")',onclick: function() {editor.insertContent('[wpdreams_ajaxsearchpro_two_column id=$id]');}}";
    }
    ?>
    
    <?php if (count($menu_items)>0): ?>
        <?php $menu_items = implode(", ", $menu_items); ?>
        <?php $menu_result_items = implode(", ", $menu_result_items); ?>
        <?php $menu_setting_items = implode(", ", $menu_setting_items); ?>
        <?php $menu_two_column_items = implode(", ", $menu_two_column_items); ?>
        <script type="text/javascript">
            wpdreams_asp_mce_button_menu = "<?php echo $menu_items; ?>";
            wpdreams_asp_res_mce_button_menu = "<?php echo $menu_result_items; ?>";
            wpdreams_asp_sett_mce_button_menu = "<?php echo $menu_setting_items; ?>";
            wpdreams_asp_two_column_mce_button_menu = "<?php echo $menu_two_column_items; ?>";
        </script>
    <?php endif;
    return $settings;
}