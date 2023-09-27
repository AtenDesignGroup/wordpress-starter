<?php
/* Prevent direct access */

use WPDRMS\ASP\Misc\PriorityGroups;

defined('ABSPATH') or die("You can't access this file directly.");

if (ASP_DEMO) $_POST = null;

$args = array(
    'public'   => true,
    '_builtin' => false
);

$output = 'names'; // names or objects, note names is the default
$operator = 'or'; // 'and' or 'or'

$post_types = array_merge(array('all'), get_post_types( $args, $output, $operator ));

$blogs = array();
if (function_exists('get_sites'))
    $blogs = get_sites();

wd_asp()->priority_groups = PriorityGroups::getInstance();
?>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/sidebar.css?v='.ASP_CURR_VER; ?>" />
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/priorities.css?v='.ASP_CURR_VER; ?>" />
<div id='wpdreams' class='asp-be wpdreams wrap<?php echo isset($_COOKIE['asp-accessibility']) ? ' wd-accessible' : ''; ?>'>
	<?php do_action('asp_admin_notices'); ?>

	<!-- This forces custom Admin Notices location -->
	<div style="display:none;"><h2 style="display: none;"></h2></div>
	<!-- This forces custom Admin Notices location -->

	<?php if ( wd_asp()->updates->needsUpdate() ) { wd_asp()->updates->printUpdateMessage(); } ?>

    <div class="wpdreams-box" style="position: relative; float:left;">
        <ul id="tabs" class='tabs'>
            <li><a tabid="1" class='current general'><?php echo __('Priority Groups', 'ajax-search-pro'); ?></a></li>
            <li><a tabid="2" class='general'><?php echo __('Individual Priorities', 'ajax-search-pro'); ?></a></li>
        </ul>

        <div class='tabscontent'>
            <div tabid="1">
                <fieldset>
                    <legend><?php echo __('Priority Groups', 'ajax-search-pro'); ?></legend>

                    <?php include(ASP_PATH . "backend/tabs/priorities/priority_groups.php"); ?>

                </fieldset>
            </div>
            <div tabid="2">
                <fieldset>
                    <legend><?php echo __('Individual Priorities', 'ajax-search-pro'); ?></legend>

                    <?php include(ASP_PATH . "backend/tabs/priorities/priorities_individual.php"); ?>

                </fieldset>
            </div>
        </div>

    </div>
    <?php include(ASP_PATH . "backend/sidebar.php"); ?>
    <div class="clear"></div>
</div>

<?php
$media_query = ASP_DEBUG == 1 ? asp_gen_rnd_str() : get_site_option("asp_media_query", "defn");
wp_enqueue_script('asp-backend-priorities', plugin_dir_url(__FILE__) . 'settings/assets/priorities.js', array(
    'jquery'
), $media_query, true);
wp_localize_script('asp-backend-priorities', 'ASP_PTS', array(
    "admin_url" => admin_url(),
    "ajax_url"  => admin_url('admin-ajax.php'),
    'msg_pda' => esc_attr__('Post Title/Date/Author', 'ajax-search-pro'),
    'msg_sav' => esc_attr__('Save changes!', 'ajax-search-pro'),
    'msg_pri' => esc_attr__('Priority', 'ajax-search-pro')
));
wp_enqueue_script('asp-backend-pg-controllers', plugin_dir_url(__FILE__) . 'settings/assets/priorities/controllers.js', array(
    'jquery'
), $media_query, true);
wp_enqueue_script('asp-backend-pg-events', plugin_dir_url(__FILE__) . 'settings/assets/priorities/events.js', array(
    'jquery',
    'asp-backend-pg-controllers'
), $media_query, true);
wp_localize_script('asp-backend-pg-events', 'ASP_EVTS', array(
    'msg_npg' => esc_attr__('Add new priority group', 'ajax-search-pro'),
    'msg_sav' => esc_attr__('Save!', 'ajax-search-pro'),
    'msg_can' => esc_attr__('Cancel', 'ajax-search-pro'),
    'msg_epg' => esc_attr__('Edit priority group:', 'ajax-search-pro'),
    'msg_del' => esc_attr__('Are you sure you want to delete %s ?', 'ajax-search-pro'),
    'msg_dal' => esc_attr__('Are you sure you want to delete all groups? This is not reversible!', 'ajax-search-pro'),
    'msg_dru' => esc_attr__('Are you sure you want to delete this rule?', 'ajax-search-pro'),
    'msg_cru' => esc_attr__('Only 10 categories are allowed per rule!', 'ajax-search-pro'),
    'msg_uns' => esc_attr__('You have unsaved changes! Are you sure you want to leave?', 'ajax-search-pro')
));