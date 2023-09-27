<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

$com_options = wd_asp()->o['asp_compatibility'];

if (ASP_DEMO) $_POST = null;

?>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/sidebar.css?v='.ASP_CURR_VER; ?>" />
<div id='wpdreams' class='asp-be wpdreams wrap<?php echo isset($_COOKIE['asp-accessibility']) ? ' wd-accessible' : ''; ?>'>
	<?php do_action('asp_admin_notices'); ?>

	<!-- This forces custom Admin Notices location -->
	<div style="display:none;"><h2 style="display: none;"></h2></div>
	<!-- This forces custom Admin Notices location -->

	<?php if ( wd_asp()->updates->needsUpdate() ) { wd_asp()->updates->printUpdateMessage(); } ?>

    <div class="wpdreams-box" style="float:left; width: 690px;">

        <?php ob_start(); ?>

        <div tabid="1">
            <fieldset>
                <legend><?php echo __('CSS and JS compatibility', 'ajax-search-pro'); ?></legend>

                <?php include(ASP_PATH . "backend/tabs/compatibility/cssjs_options.php"); ?>

            </fieldset>
        </div>
        <div tabid="4">
            <fieldset>
                <legend><?php echo __('CSS and JS loading', 'ajax-search-pro'); ?></legend>

                <?php include(ASP_PATH . "backend/tabs/compatibility/cssjs_loading.php"); ?>

            </fieldset>
        </div>
        <div tabid="2">
            <fieldset>
                <legend><?php echo __('Query compatibility options', 'ajax-search-pro'); ?></legend>

                <?php include(ASP_PATH . "backend/tabs/compatibility/query_options.php"); ?>

            </fieldset>
        </div>
        <div tabid="3">
            <fieldset>
                <legend><?php echo __('Other options', 'ajax-search-pro'); ?></legend>

                <?php include(ASP_PATH . "backend/tabs/compatibility/other.php"); ?>

            </fieldset>
        </div>

        <?php $_r = ob_get_clean(); ?>

        <?php
        $updated = false;
        if (isset($_POST) && isset($_POST['asp_compatibility']) && (wpdreamsType::getErrorNum() == 0)) {
            $values = array(
                // CSS and JS
                "js_prevent_body_scroll" => $_POST['js_prevent_body_scroll'],
                "js_source" => $_POST['js_source'],
                "detect_ajax" => $_POST['detect_ajax'],
                "css_compatibility_level" => $_POST['css_compatibility_level'],
                'css_minify' => $_POST['css_minify'],
                "load_google_fonts" => $_POST['load_google_fonts'],
                "usecustomajaxhandler" => $_POST['usecustomajaxhandler'],
                // Loading
                "script_loading_method" => $_POST['script_loading_method'],
                "load_lazy_js" => $_POST['load_lazy_js'],
                "init_instances_inviewport_only" => $_POST['init_instances_inviewport_only'],
                "css_loading_method" => $_POST['css_loading_method'],
                'selective_enabled' => $_POST['selective_enabled'],
                'selective_front' => $_POST['selective_front'],
                'selective_archive' => $_POST['selective_archive'],
                'selective_exin_logic' => $_POST['selective_exin_logic'],
                'selective_exin' => $_POST['selective_exin'],
                // Query options
                'query_soft_check' => $_POST['query_soft_check'],
                'use_acf_getfield' => $_POST['use_acf_getfield'],
                'db_force_case' => $_POST['db_force_case'],
                'db_force_unicode' => $_POST['db_force_unicode'],
                'db_force_utf8_like' => $_POST['db_force_utf8_like'],
                // Other options
                'rest_api_enabled' => $_POST['rest_api_enabled'],
                'meta_box_post_types' => $_POST['meta_box_post_types']
            );
            update_option('asp_compatibility', $values);
            asp_parse_options();
            $updated = true;
            wd_asp()->css_manager->generator->generate();
        }
        ?>
        <div class='wpdreams-slider'>

            <?php if ($updated): ?>
                <div class='successMsg'>
                    <?php echo __('Search compatibility settings successfuly updated!', 'ajax-search-pro'); ?>
                </div>
            <?php endif; ?>

            <?php if (ASP_DEMO): ?>
                <p class="infoMsg">DEMO MODE ENABLED - Please note, that these options are read-only</p>
            <?php endif; ?>

            <ul id="tabs" class='tabs'>
                <li><a tabid="1" class='current multisite'><?php echo __('CSS & JS compatibility', 'ajax-search-pro'); ?></a></li>
                <li><a tabid="4" class='general'><?php echo __('CSS & JS loading', 'ajax-search-pro'); ?></a></li>
                <li><a tabid="2" class='general'><?php echo __('Query compatibility', 'ajax-search-pro'); ?></a></li>
                <li><a tabid="3" class='general'><?php echo __('Other', 'ajax-search-pro'); ?></a></li>
            </ul>

            <div class='tabscontent'>

            <!-- Compatibility form -->
            <form name='compatibility' method='post'>

                <?php print $_r; ?>

                <div class="item">
                    <input type='submit' class='submit' value='<?php echo esc_attr__('Save options', 'ajax-search-pro'); ?>'/>
                </div>
                <input type='hidden' name='asp_compatibility' value='1'/>
            </form>

            </div>
        </div>
    </div>

	<?php include(ASP_PATH . "backend/sidebar.php"); ?>
</div>
<?php
wp_enqueue_script('wpd-backend-compatibility', plugin_dir_url(__FILE__) . 'settings/assets/compatibility_settings.js', array(
    'jquery'
), ASP_CURR_VER_STRING, true);