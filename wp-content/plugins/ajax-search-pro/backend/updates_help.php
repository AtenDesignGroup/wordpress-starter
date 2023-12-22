<?php
/* Prevent direct access */

use WPDRMS\ASP\Misc\EnvatoLicense;

defined('ABSPATH') or die("You can't access this file directly.");

if (ASP_DEMO) $_POST = null;
?>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/sidebar.css?v='.ASP_CURR_VER; ?>" />
<div id='wpdreams' class='asp-be asp_updates_help<?php echo isset($_COOKIE['asp-accessibility']) ? ' wd-accessible' : ''; ?>'>
	<?php do_action('asp_admin_notices'); ?>

	<!-- This forces custom Admin Notices location -->
	<div style="display:none;"><h2 style="display: none;"></h2></div>
	<!-- This forces custom Admin Notices location -->

	<div class="wpdreams-box" style="float: left;">
		<div class="wpd-half">
            <h3><?php echo __('Version status', 'ajax-search-pro'); ?></h3>
            <div class="item">
                <?php if (wd_asp()->updates->needsUpdate(true)): ?>
					<?php wd_asp()->updates->printUpdateMessage(); ?>
                <?php else: ?>
                    <p><?php echo __('You have the latest version installed:', 'ajax-search-pro'); ?> <strong><?php echo ASP_CURR_VER_STRING; ?></strong></p>
                <?php endif; ?>
            </div>
            <h3><?php echo __('Support', 'ajax-search-pro'); ?></h3>
            <div class="item">
                <?php echo sprintf( __('If you can\'t find the answer in the documentation or knowledge base, or if you are having other issues,
                feel free to <a href="%s" target="_blank">open a support ticket</a>.', 'ajax-search-pro'), 'https://wp-dreams.com/open-support-ticket-step-1/' ); ?>
            </div>
			<h3><?php echo __('Useful Resources', 'ajax-search-pro'); ?></h3>
			<div class="item">
				<ul>
					<li><a target="_blank" href="https://documentation.ajaxsearchpro.com/" title="Documentation"><?php echo __('Onlie Documentation', 'ajax-search-pro'); ?></a></li>
					<li><a target="_blank" href="https://knowledgebase.ajaxsearchpro.com/" title="Knowledge Base"><?php echo __('Knowledge base', 'ajax-search-pro'); ?></a></li>
					<li><a target="_blank" href="https://changelog.ajaxsearchpro.com/" title="Changelog"><?php echo __('Changelog', 'ajax-search-pro'); ?></a></li>
					<li><a target="_blank" href="https://documentation.ajaxsearchpro.com/plugin-updates/manual-updates"><?php echo __('How to manual update?', 'ajax-search-pro'); ?></a></li>
				</ul>
			</div>
		</div>
		<div class="wpd-half-last">
            <?php if (ASP_DEMO == 0): ?>
			<h3><?php echo __('Automatic Updates', 'ajax-search-pro'); ?></h3>
            <div class="item<?php echo EnvatoLicense::isActivated( true, true ) === false ? "" : " hiddend"; ?>">
                <div class="asp_auto_update">
                    <p><?php echo __('To activate Automatic Updates, please activate your purchase code with this site.', 'ajax-search-pro'); ?></p>
                    <label><?php echo __('Purchase code', 'ajax-search-pro'); ?></label>
                    <input type="text" name="asp_key" id="asp_key">
                    <div class="errorMsg" style="display:none;"></div>
                    <input type="button" id="asp_activate" name="asp_activate" class="submit wd_button_blue" value="<?php echo esc_attr__('Activate for this site', 'ajax-search-pro'); ?>">
                    <span class="small-loading" style="display:none; vertical-align: middle;"></span>
                    <?php echo __('<p>If you activated the plugin <b>with this site before</b>, and you see this activation form, just enter the purchase code again to re-activate.</p>', 'ajax-search-pro'); ?>
                </div>
                <div class="asp_remote_deactivate">
                    <p><?php echo __('If the purchase code is activated with a <b>different site</b>, then you will have to first de-activate it from there, or use the form below if the site does not work anymore:', 'ajax-search-pro'); ?></p>
                    <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo __('Site URL', 'ajax-search-pro'); ?></label>
                    <input type="text" name="asp_site_url" id="asp_site_url"><br><br>
                    <label><?php echo __('Purchase code', 'ajax-search-pro'); ?></label>
                    <input type="text" name="asp_keyd" id="asp_keyd"><br>
                    <div class="infoMsg" style="display:none;"></div>
                    <div class="errorMsg" style="display:none;"></div>
                    <input type="button" id="asp_deactivated" name="asp_deactivated" class="submit wd_button_blue" value="<?php echo esc_attr__('Deactivate', 'ajax-search-pro'); ?>">
                    <span class="small-loading" style="display:none; vertical-align: middle;"></span>
                    <p class="descMsg" style="text-align: left;margin-top: 10px;"><?php echo __('<b>NOTICE:</b> After deactivation there is a <b>30 minute</b> wait time until you can re-activate the same purchase code to prevent malicious activity.', 'ajax-search-pro'); ?></p>
                </div>
            </div>
            <div class="item<?php echo EnvatoLicense::isActivated() === false ? " hiddend" : ""; ?> asp_auto_update">
                <p><?php echo __('Auto updates are activated for this site with purchase code:', 'ajax-search-pro'); ?> <br><b><?php echo EnvatoLicense::isActivated(); ?></b></p>
                <div class="errorMsg" style="display:none;"></div>
                <input type="button" class="submit wd_button_blue" id="asp_deactivate" name="asp_deactivate" value="<?php echo esc_attr__('Deactivate', 'ajax-search-pro'); ?>">
                <span class="small-loading" style="display:none; vertical-align: middle;"></span>
                <p class="descMsg" style="text-align: left;margin-top: 10px;"><?php echo __('<b>NOTICE:</b> After deactivation there is a <b>30 minute</b> wait time until you can re-activate the same purchase code to prevent malicious activity.', 'ajax-search-pro'); ?></p>
            </div>
            <input type="hidden" id="asp_license_request_nonce" value="<?php echo wp_create_nonce( 'asp_license_request_nonce' ); ?>">
			<?php endif; ?>
		</div>
        <div class="clear"></div>
	</div>
    <?php include(ASP_PATH . "backend/sidebar.php"); ?>
    <div class="clear"></div>
</div>
<?php
wp_enqueue_script('wpd-backend-updates-help', plugin_dir_url(__FILE__) . 'settings/assets/updates_help.js', array(
    'jquery'
), ASP_CURR_VER_STRING, true);