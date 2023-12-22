<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

global $wpdb;

$errormsg = '';
$import_count = 0;
$sett_import_count = 0;

if ( isset($_POST['asp_import_textarea'], $_POST['asp_import_nonce1']) ) {
	if ( wp_verify_nonce( $_POST['asp_import_nonce1'], 'asp_import_nonce1' ) ) {
		if ( empty($_POST['asp_import_textarea']) ) {
			$errormsg = __('Import data is empty.', 'ajax-search-pro');
		} else {
			$data = json_decode(stripcslashes($_POST['asp_import_textarea']));
			if ( json_last_error() == 0 ) {
				$import_count = wd_asp()->instances->import($data);
				if ( is_wp_error($import_count) ) {
					$errormsg = $import_count->get_error_message();
					$import_count = 0;
				}
			} else {
				$errormsg = __('Import data error.', 'ajax-search-pro');
			}
		}
	} else {
		$errormsg = __('Error importing: invalid NONCE, please try again', 'ajax-search-pro');
	}
}

if ( isset($_POST['asp_import_textarea_sett'], $_POST['asp_import_nonce2']) ) {
	if ( wp_verify_nonce( $_POST['asp_import_nonce2'], 'asp_import_nonce2' ) ) {
		if ( empty($_POST['asp_import_textarea_sett']) ) {
			$errormsg = __('Import data is empty.', 'ajax-search-pro');
		} else {
			$instance = json_decode(base64_decode(stripcslashes($_POST['asp_import_textarea_sett'])), true);
			if ( json_last_error() == 0 && isset($instance['data']) ) {
				wd_asp()->instances->update($_POST['asp_import_sett'], $instance['data']);
				$sett_import_count = 1;
			} else {
				$errormsg = __('Cannot import. Invalid data! Please try again!', 'ajax-search-pro');
			}
		}
	} else {
		$errormsg = __('Error importing: invalid NONCE, please try again', 'ajax-search-pro');
	}
}

$search_instances = wd_asp()->instances->get(-1, true);

if ( $import_count > 0 || $sett_import_count > 0) {
    wd_asp()->css_manager->generator->generate();
}
?>

<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/sidebar.css?v='.ASP_CURR_VER; ?>" />
<div id='wpdreams' class='asp-be wpdreams wrap<?php echo isset($_COOKIE['asp-accessibility']) ? ' wd-accessible' : ''; ?>'>
	<?php do_action('asp_admin_notices'); ?>

	<?php if ( wd_asp()->updates->needsUpdate() ) { wd_asp()->updates->printUpdateMessage(); } ?>

	<!-- This forces custom Admin Notices location -->
	<div style="display:none;"><h2 style="display: none;"></h2></div>
	<!-- This forces custom Admin Notices location -->

    <div class="wpdreams-box" style="float:left;">
        <?php if ($errormsg != ''): ?>
            <div class="errorMsg"><?php echo $errormsg; ?></div>
        <?php endif; ?>
        <?php if ($import_count > 0): ?>
            <div class='infoMsg'>
            <?php echo sprintf( __('Succesfully imported <b>%s</b> search instances.', 'ajax-search-pro'), $import_count ); ?>
            </div>
        <?php endif; ?>
        <?php if ($sett_import_count > 0): ?>
            <div class='infoMsg'><?php echo __('Succesfully imported the search settings.', 'ajax-search-pro'); ?></div>
        <?php endif; ?>
        <ul id="tabs" class='tabs'>
            <li><a tabid="1" class='current multisite'><?php echo __('Instances', 'ajax-search-pro'); ?></a></li>
            <li><a tabid="2" class='general'><?php echo __('Settings Only', 'ajax-search-pro'); ?></a></li>
        </ul>
        <div class='tabscontent'>
            <div tabid="1">
                <fieldset>
                    <legend>Instances</legend>
                    <p class="biggerDescMsg">
                        <?php echo __('On this tab you can import complete search instances. If you prefer to import settings only, click on the Settings Only button on the top of this page.', 'ajax-search-pro'); ?>
                    </p>
                    <fieldset>
                        <legend><?php echo __('Export Search instances', 'ajax-search-pro'); ?></legend>
                        <label for="asp_export" style="width: 335px;display: inline-block;"><?php echo __('Select the search instances to export', 'ajax-search-pro'); ?></label>
                        <label style="text-align: right;" for="asp_export_textarea"><?php echo __('Copy and save the text appearing in this box', 'ajax-search-pro'); ?></label><br>
                        <select id="asp_export" multiple>
                            <?php foreach ($search_instances as $instance): ?>
                                <option value="<?php echo wd_asp()->instances->export($instance['id']); ?>"><?php echo esc_html( $instance['name'] ); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <textarea id="asp_export_textarea" class="wd-export-import"></textarea>
                        <input id='asp_export_button' type='button' class='submit' value='Export!'/><span class="small-loading hiddend"></span>
                    </fieldset>
                    <form name="asp_import_instances" method="post" enctype='application/json'>
                    <fieldset>
                        <legend><?php echo __('Import Search instances', 'ajax-search-pro'); ?></legend>
                        <p class="biggerDescMsg">
                            <?php echo __('Please note that the search IDs may differ from the exported instances. The imported instance names will have the "Imported" string appended after their names.', 'ajax-search-pro'); ?>
                        </p>
						<input type="hidden" name="asp_import_nonce1" id="asp_import_nonce1" value="<?php echo wp_create_nonce( "asp_import_nonce1" ); ?>">
                        <label for="asp_import_textarea"><?php echo __('Paste the exported code here', 'ajax-search-pro'); ?></label><br>
                        <textarea id="asp_import_textarea" name="asp_import_textarea" class="wd-export-import"></textarea>
                        <br><input id='asp_import_button' type='submit' class='submit' value='Import!'/>
                    </fieldset>
                    </form>
                </fieldset>
            </div>
            <div tabid="2">
                <fieldset>
                    <legend><?php echo __('Settings only', 'ajax-search-pro'); ?></legend>
                    <p class="biggerDescMsg">
                        <?php echo __('On this page you can import settings to existing search instances. The import process will overwrite your current search options!', 'ajax-search-pro'); ?>
                    </p>
                    <fieldset>
                        <legend><?php echo __('Export settings', 'ajax-search-pro'); ?></legend>
                        <label for="asp_export_sett" style="width: 335px;display: inline-block;"><?php echo __('Select the instance to export settings from', 'ajax-search-pro'); ?></label>
                        <label style="text-align: right;" for="asp_export_textarea_sett"><?php echo __('Copy and save the text appearing in this box', 'ajax-search-pro'); ?></label><br>
                        <select id="asp_export_sett">
                            <?php foreach ($search_instances as $instance): ?>
                                <option value="<?php echo wd_asp()->instances->export($instance['id']); ?>"><?php echo esc_html( $instance['name'] ); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <textarea id="asp_export_textarea_sett" class="wd-export-import"></textarea>
                        <input id='asp_export_button_sett' type='button' class='submit' value='Export!'/><span class="small-loading hiddend"></span>
                    </fieldset>
                    <form name="asp_import_instances_sett" method="post" enctype='application/json'>
                        <fieldset>
                            <legend><?php echo __('Import settings', 'ajax-search-pro'); ?></legend>
                            <label for="asp_import_sett" style="width: 335px;display: inline-block;"><?php echo __('Select the instance to import settings to', 'ajax-search-pro'); ?></label>
                            <label style="text-align: right;" for="asp_import_textarea_sett"><?php echo __('Paste the exported settings code here', 'ajax-search-pro'); ?></label><br>
                            <select id="asp_import_sett" name="asp_import_sett">
                                <?php foreach ($search_instances as $instance): ?>
                                    <option value="<?php echo $instance['id']; ?>"><?php echo esc_html( $instance['name'] ); ?></option>
                                <?php endforeach; ?>
                            </select>
							<input type="hidden" name="asp_import_nonce2" id="asp_import_nonce2" value="<?php echo wp_create_nonce( "asp_import_nonce2" ); ?>">
                            <textarea id="asp_import_textarea_sett" name="asp_import_textarea_sett" class="wd-export-import"></textarea>
                            <br><input id='asp_import_button_sett' type='submit' class='submit' value='Import!'/>
                        </fieldset>
                    </form>
                </fieldset>
            </div>
        </div>

    </div>

    <?php include(ASP_PATH . "backend/sidebar.php"); ?>

    <div class="clear"></div>
</div>

<style>
    #asp_export {
        min-width: 350px;
        max-width: 350px;
        min-height: 150px;
        max-height: 150px;
        vertical-align: top;
    }

    #asp_export_sett,
    #asp_import_sett {
        min-width: 350px;
        max-width: 350px;
        vertical-align: top;
    }

    textarea.wd-export-import {
        margin-left: 40px;
    }

    input#asp_export_button,
    input#asp_import_button {
        margin: 13px 6px;
    }

    input#asp_export_button[disabled] {
        opacity: 0.6;
    }

    #wpdreams span.small-loading {
        vertical-align: middle;
        margin: 0 20px;
        display: inline-block;
    }

    #wpdreams span.hiddend {
        display: none;
    }

    #wpdreams .asp_succ_import {
        display: inline;
        vertical-align: top;
        padding: 0 10px;
    }
</style>
<?php
wp_enqueue_script('wpd-backend-export-import', ASP_URL_NP . 'backend/settings/assets/export_import.js', array(
    'jquery'
), ASP_CURR_VER_STRING, true);
wp_localize_script('wpd-backend-export-import', 'ASP_EI_LOC', array(
    "suc_msg" => __('Are you sure you want to import settings to that search instance?', 'ajax-search-pro')
));