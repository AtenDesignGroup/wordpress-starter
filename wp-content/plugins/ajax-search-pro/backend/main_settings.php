<?php
/* Prevent direct access */

use WPDRMS\ASP\Utils\FileManager;

defined('ABSPATH') or die("You can't access this file directly.");

if (isset($_GET) && isset($_GET['asp_sid'])) {
    include('search.php');
    return;
}
?>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/sidebar.css?v='.ASP_CURR_VER; ?>" />
<div id='wpdreams' style="display: flex; justify-content: start; align-items: flex-start; gap: 0;"
	 class='asp-be wpdreams wrap<?php echo isset($_COOKIE['asp-accessibility']) ? ' wd-accessible' : ''; ?>'>
	<div style="margin: 0;">
		<?php do_action('asp_admin_notices'); ?>

		<!-- This forces custom Admin Notices location -->
		<div style="display:none;"><h2 style="display: none;"></h2></div>
		<!-- This forces custom Admin Notices location -->

    <?php if (defined('ASL_PATH')): ?>
        <p class="errorMsg">
            <?php echo __('Warning: Please deactivate the Ajax Search Lite to assure every PRO feature works properly.', 'ajax-search-pro'); ?>
        </p>
    <?php endif; ?>

	<?php if ( wd_asp()->updates->needsUpdate() ) { wd_asp()->updates->printUpdateMessage(); } ?>

    <?php if ( !wd_asp()->db->exists('main', true) ): ?>
        <div id='wpdreams' class='asp-be wpdreams wrap'>
            <div class="wpdreams-box">
                <p class="errorMsg">
                    <?php echo __('One or more plugin tables are appear to be missing from the database.', 'ajax-search-pro'); ?>
                    <?php echo sprintf( __('Please check <a href="%s" target="_blank">this article</a> to resolve the issue.', 'ajax-search-pro'),
                        'https://wp-dreams.com/go/?to=kb-asp-missing-tables' ); ?>
                </p>
                <p class="errorMsg">
                    <?php echo __('Please be <strong>very careful</strong>, before making any changes to your database. Make sure to have a full database back-up, just in case!', 'ajax-search-pro'); ?>
                </p>
                <p>
                    <fieldset>
                        <legend><?php echo __('Copy this SQL code in your database editor tool to create them manually', 'ajax-search-pro'); ?></legend>
                        <textarea style="width:100%;height:480px;"><?php echo wd_asp()->db->create(); ?></textarea>
                    </fieldset>
                </p>
            </div>
        </div>
    <?php else: ?>

    <div class="wpdreams-box" style="overflow: visible; float: left; position: relative;width: 920px;">
        <form name="add-slider" action="" method="POST">
            <fieldset>
                <legend><?php echo __('Create a new search instance', 'ajax-search-pro'); ?></legend>
                <?php
                $new_slider = new wpdreamsText("addsearch", __('Search form name:', 'ajax-search-pro'), "", array(array("func" => "wd_isEmpty", "op" => "eq", "val" => false)), "Please enter a valid form name!");
                ?>
                <input name="submit" type="submit" value="<?php esc_attr_e("Add", 'ajax-search-pro' ); ?>"/>
                <?php if ( count( (array)get_option('asl_options', array()) ) > 0 && get_option('asl_version', 0) > 4732 ): ?>
                <input name="import" type="submit" value="<?php echo __('Import from Ajax Search Lite', 'ajax-search-pro'); ?>">
                <?php endif; ?>
                <input type="hidden"
                       name="asp_new_nonce"
                       id="asp_new_nonce"
                       value="<?php echo wp_create_nonce( "asp_new_nonce" ); ?>">
                <?php
                if (isset($_POST['addsearch']) && !$new_slider->getError()) {
                    if ( !wp_verify_nonce($_POST['asp_new_nonce'], 'asp_new_nonce') ) {
                        echo "<div class='errorMsg'>" . __('Failure. Nonce invalid, please reload the page and try again.', 'ajax-search-pro') . "</div>";
                    } else {
                        if (isset($_POST['import'])) {
                            $id = wd_asp()->instances->importFromLite($_POST['addsearch'], get_current_user_id());
                        } else {
                            $id = wd_asp()->instances->add($_POST['addsearch'], get_current_user_id());
                        }

                        if ($id !== false) {
                            wd_asp()->css_manager->generator->generate();
                            echo "<div class='successMsg'>" . __('Search Form Successfuly added!', 'ajax-search-pro') . "</div>";
                        } else {
                            echo "<div class='errorMsg'>" . __('The search form was not created. Please contact support.', 'ajax-search-pro') . "</div>";
                        }
                    }
                }
                if (
                    isset($_POST['instance_new_name'], $_POST['instance_id'], $_POST['asp_name_nonce' . '_' . $_POST['instance_id']])
                ) {
                    if ( !wp_verify_nonce($_POST['asp_name_nonce' . '_' . $_POST['instance_id']], 'asp_name_nonce_' . $_POST['instance_id']) ) {
                        echo "<div class='errorMsg'>" . __('Failure. Nonce invalid, please reload the page and try again.', 'ajax-search-pro') . "</div>";
                    } else {
                        if ($_POST['instance_new_name'] != ''
                            && strlen($_POST['instance_new_name']) > 0
                        ) {
                            if ( wd_asp()->instances->rename($_POST['instance_new_name'], $_POST['instance_id']) !== false )
                                echo "<div class='infoMsg'>" . __('Form name changed!', 'ajax-search-pro') . "</div>";
                            else
                                echo "<div class='errorMsg'>" . __('Failure. Search could not be renamed.', 'ajax-search-pro') . "</div>";
                        } else {
                            echo "<div class='errorMsg'>" . __('Failure. Form name must be at least 1 character long', 'ajax-search-pro') . "</div>";
                        }
                    }
                }
                if ( isset($_POST['instance_copy_id']) ) {
                    if ($_POST['instance_copy_id'] != '') {
                        if ( wd_asp()->instances->duplicate($_POST['instance_copy_id']) !== false ) {
                            wd_asp()->css_manager->generator->generate();
                            echo "<div class='infoMsg'>" . __('Form duplicated!', 'ajax-search-pro') . "</div>";
                        } else {
                            echo "<div class='errorMsg'>" . __('Failure. Search form could not be duplicated.', 'ajax-search-pro') . "</div>";
                        }
                    } else {
                        echo "<div class='errorMsg'>" . __('Failure :(', 'ajax-search-pro') . "</div>";
                    }
                }
                ?>
            </fieldset>
        </form>
        <?php
        if (
             isset($_POST['delete'], $_POST['asp_del_nonce_' . $_POST['delete']]) &&
             wp_verify_nonce($_POST['asp_del_nonce_' . $_POST['delete']], 'asp_del_nonce_' . $_POST['delete'] )
        ) {
            $_POST['delete'] = $_POST['delete'] + 0;
            wd_asp()->instances->delete( $_POST['delete'] );
			FileManager::_o()->delFile( wd_asp()->cache_path . "search" . $_POST['delete'] . ".css");
            wd_asp()->css_manager->generator->generate();
        }
        if ( isset($_POST['asp_st_override']) ) {
            update_option("asp_st_override", $_POST['asp_st_override']);
        }
        if ( isset($_POST['asp_woo_override']) ) {
            update_option("asp_woo_override", $_POST['asp_woo_override']);
        }

        if (
            isset($_POST['instance_owner'], $_POST['instance_id'], $_POST['asp_owner_nonce' . '_' . $_POST['instance_id']]) &&
            wp_verify_nonce($_POST['asp_owner_nonce_' . $_POST['instance_id']], 'asp_owner_nonce_' . $_POST['instance_id']) &&
            is_super_admin() && is_multisite()
        ) {
            wd_asp()->instances->update($_POST['instance_id'], array(), $_POST['instance_owner']);
        }

        if ( is_multisite() ) {
            $searchforms = wd_asp()->instances->get(-1, false, true);
        } else {
            $searchforms = wd_asp()->instances->getWithoutData();
        }

        ?>
        <?php if ( !empty($searchforms) ): ?>
        <?php
        $asp_st_override = get_option("asp_st_override", -1);
        $asp_woo_override = get_option("asp_woo_override", -1);
        ?>
        <br>
        <form name="sel-asp_st_override" action="" method="POST">
        <fieldset>
            <legend><?php echo __('Theme search bar replace', 'ajax-search-pro'); ?></legend>
            <label><?php echo __('Replace the default theme search with:', 'ajax-search-pro'); ?> </label>
            <select name="asp_st_override" style="max-width:90px;">
                    <option value="-1"><?php echo __('None', 'ajax-search-pro'); ?></option>
                <?php foreach ($searchforms as $_searchform): ?>
                    <option value="<?php echo $_searchform["id"]; ?>"
                        <?php echo $asp_st_override == $_searchform["id"] ? " selected='selected'" : ""; ?>>
                        <?php echo esc_html( $_searchform["name"] ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (class_exists("WooCommerce")): ?>
            <?php echo __('and the <strong>WooCommerce</strong> search with:', 'ajax-search-pro'); ?>
            <select name="asp_woo_override" style="max-width:90px;">
                <option value="-1"><?php echo __('None', 'ajax-search-pro'); ?></option>
                <?php foreach ($searchforms as $_searchform): ?>
                    <option value="<?php echo $_searchform["id"]; ?>"
                        <?php echo $asp_woo_override == $_searchform["id"] ? " selected='selected'" : ""; ?>>
                        <?php echo esc_html($_searchform["name"] ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <span style='
                            font-family: dashicons;
                            content: "\f348";
                            width: 24px;
                            height: 24px;
                            line-height: 24px;
                            font-size: 24px;
                            display: inline-block;
                            /* position: static; */
                            vertical-align: middle;
                            color: #167DB9;' class="dashicons dashicons-info">
            <a href="#" style="display:block; width:24px; height: 24px; margin-top: -24px;"
                class="tooltip-bottom" data-tooltip="<?php esc_attr_e('This might not work with all themes. If the default theme search bar is still visible after selection, then the only way is to replace the search within the theme code.', 'ajax-search-pro'); ?>"></a>
            </span>
            <input name="submit" type="submit" value="<?php esc_attr_e("Save", 'ajax-search-pro' ); ?>"/>
        </fieldset>
        </form>
        <?php endif; ?>
    </div>

    <div class="clear"></div>

    <?php

    $i = 0;
    if (is_array($searchforms)) {
        $extra_classes = '';
        if (is_multisite() && is_super_admin()) {
            $the_users = get_users(array(
                'role' => 'administrator'
            ));
            $extra_classes = 'wpdreams-box-wide';
        }
        foreach ($searchforms as $search) {
            $i++;
            // Needed for the tabindex for the CSS :focus to work with div
            ?>
            <div class="wpdreams-box <?php echo $extra_classes; ?>" tabindex="<?php echo $i; ?>" style="width: 920px;">
                <div class="asp_search_list_item">
                    <a href='<?php echo get_admin_url() . "admin.php?page=asp_main_settings"; ?>&asp_sid=<?php echo $search['id']; ?>'><svg width="24" height="24" version="1.1" id="gear_icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
							 viewBox="0 0 458.317 458.317" style="enable-background:new 0 0 458.317 458.317;" xml:space="preserve">
							<g>
								<path d="M446.185,179.159h-64.768c-2.536-7.702-5.636-15.15-9.26-22.29l45.818-45.818c4.737-4.737,4.737-12.416,0-17.152
										L364.416,40.34c-4.737-4.737-12.416-4.737-17.152,0l-45.818,45.818c-7.14-3.624-14.587-6.724-22.289-9.26V12.131
										c0.001-6.699-5.429-12.129-12.128-12.129h-75.743c-6.698,0-12.129,5.43-12.129,12.128v64.768
										c-7.702,2.535-15.149,5.636-22.29,9.26L111.05,40.341c-4.737-4.737-12.416-4.737-17.152,0L40.339,93.9
										c-4.737,4.736-4.737,12.416,0,17.152l45.817,45.817c-3.624,7.14-6.725,14.588-9.26,22.29H12.129C5.43,179.159,0,184.59,0,191.288
										v75.743c0,6.698,5.43,12.128,12.129,12.128h64.768c2.536,7.702,5.636,15.149,9.26,22.29L40.34,347.266
										c-4.737,4.736-4.737,12.416,0,17.152l53.559,53.559c4.737,4.736,12.416,4.736,17.152,0l45.817-45.817
										c7.14,3.624,14.587,6.725,22.29,9.26v64.768c0,6.698,5.43,12.128,12.129,12.128h75.743c6.698,0,12.129-5.43,12.129-12.128v-64.768
										c7.702-2.535,15.149-5.636,22.289-9.26l45.818,45.817c4.737,4.736,12.416,4.736,17.152,0l53.559-53.559
										c4.737-4.737,4.737-12.416,0-17.152l-45.817-45.817c3.624-7.14,6.724-14.587,9.26-22.289h64.768
										c6.698,0,12.129-5.43,12.129-12.128v-75.743C458.314,184.59,452.884,179.159,446.185,179.159z M229.157,289.542
										c-33.349,0-60.384-27.035-60.384-60.384s27.035-60.384,60.384-60.384s60.384,27.035,60.384,60.384
										S262.506,289.542,229.157,289.542z"/>
							</g>
							</svg></a>&nbsp;&nbsp;
					<a href="#" class="asp_search_delete"><svg width="24" height="24" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
												 viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
					<polygon style="fill:#E21B1B;" points="404.176,0 256,148.176 107.824,0 0,107.824 148.176,256 0,404.176 107.824,512 256,363.824
						404.176,512 512,404.176 363.824,256 512,107.824 "/>
					</svg></a>
                    <form action="" style="display:none;" method="POST">
                        <input type="hidden" name="delete" value=<?php echo $search['id']; ?>>
                        <input type="hidden"
                               name="asp_del_nonce<?php echo '_'.$search['id']; ?>"
                               id="asp_del_nonce<?php echo '_'.$search['id']; ?>"
                               value="<?php echo wp_create_nonce( "asp_del_nonce" . '_' . $search['id'] ); ?>">
                    </form>
                    <span class="wpd_instance_name"><?php
                        echo esc_html( $search['name'] );
                        ?>
                </span>

                    <form style="display: inline" name="instance_new_name_form" class="instance_new_name_form"
                          method="post">
                        <input type="text" class="instance_new_name" name="instance_new_name"
                               value="<?php echo esc_attr( $search['name'] ); ?>">
                        <input type="hidden" name="instance_id" value="<?php echo $search['id']; ?>"/>
                        <img title="<?php esc_attr_e('Click here to rename this form!', 'ajax-search-pro'); ?>"
                             src="<?php echo plugins_url('/settings/assets/icons/edit24x24.png', __FILE__) ?>"
                             class="wpd_instance_edit_icon"/>
                        <input type="hidden"
                               name="asp_name_nonce<?php echo '_'.$search['id']; ?>"
                               id="asp_name_nonce<?php echo '_'.$search['id']; ?>"
                               value="<?php echo wp_create_nonce( "asp_name_nonce" . '_' . $search['id']  ); ?>">
                    </form>
                    <form style="display: inline" name="instance_copy_form" class="instance_copy_form"
                          method="post">
                        <input type="hidden" name="instance_copy_id" value="<?php echo $search['id']; ?>"/>
                        <img title="<?php esc_attr_e('Click here to duplicate this form!', 'ajax-search-pro'); ?>"
                             src="<?php echo plugins_url('/settings/assets/icons/duplicate18x18.png', __FILE__) ?>"
                             class="wpd_instance_edit_icon"/>
                    </form>
                    <span style='margin-left: auto;min-width:540px;text-align:right;'>
                    <?php if (is_multisite() && is_super_admin()): ?>
                        <form style="display: inline" name="instance_owner_form" class="instance_owner_form"
                              method="post">
                            <label>Owner
                                <select name="instance_owner">
                                    <option value="0"<?php echo $search['data']['owner'] == 0 ? ' selected="selected"' : ''; ?>>Anyone (admins only)</option>
                                <?php foreach ($the_users as $auser): ?>
                                    <option
                                        <?php echo $search['data']['owner'] == $auser->ID ? ' selected="selected"' : ''; ?>
                                        value="<?php echo $auser->ID; ?>"><?php echo $auser->user_login; ?></option>
                                <?php endforeach; ?>
                                </select>
                            </label>
                            <input type="hidden" name="instance_id" value="<?php echo $search['id']; ?>"/>
                            <input type="hidden"
                                   name="asp_owner_nonce<?php echo '_'.$search['id']; ?>"
                                   id="asp_owner_nonce<?php echo '_'.$search['id']; ?>"
                                   value="<?php echo wp_create_nonce( "asp_owner_nonce" . '_' . $search['id']  ); ?>">

                        <img title="<?php esc_attr_e('Click here to change the owner of this form!', 'ajax-search-pro'); ?>"
                             src="<?php echo plugins_url('/settings/assets/icons/edit24x24.png', __FILE__) ?>"
                             class="wpd_owner_edit_icon"/>
                    </form>
                    <?php endif; ?>
                 <label class="shortcode"><?php __('Quick shortcode:', 'ajax-search-pro'); ?></label>
                 <input type="text" class="quick_shortcode" value="[wd_asp id=<?php echo $search['id']; ?>]"
                        readonly="readonly"/>
                </span>
                </div>
                <div class="clear"></div>
            </div>
            <?php


        }
    }
    ?>

    <?php endif; ?>
    <script>
    jQuery(function ($) {
        $('input.instance_new_name').on('focus', function () {
            $(this).parent().prev().css('display', 'none');
        }).blur(function () {
                $(this).parent().prev().css('display', '');
            });
        $('.instance_new_name_form').on('submit', function () {
            if (!confirm('<?php echo __('Do you want to change the name of this form?', 'ajax-search-pro'); ?>'))
                return false;
        });
        $('.instance_owner_form').on('submit', function () {
            if (!confirm('<?php echo __('Do you want to change the owner of this form?', 'ajax-search-pro'); ?>'))
                return false;
        });
        $('.instance_copy_form').on('submit', function () {
            if ( !confirm('<?php echo __('Do you want to duplicate this form?', 'ajax-search-pro'); ?>') )
                return false;
        });
        $('.instance_new_name_form .wpd_instance_edit_icon').on('click', function () {
            $(this).parent().submit();
        });
        $('.instance_copy_form .wpd_instance_edit_icon').on('click', function () {
            $(this).parent().submit();
        });
        $('.instance_owner_form .wpd_owner_edit_icon').on('click', function () {
            $(this).parent().submit();
        });
    });
    </script>
	</div>
	<?php include(ASP_PATH . "backend/sidebar.php"); ?>
</div>

