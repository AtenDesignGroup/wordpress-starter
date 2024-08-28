<?php
/**
 * @var array $editor_data Various pieces of data passed by the plugin.
 */
$ame_current_user = wp_get_current_user();
$images_url = $editor_data['images_url'];
$is_pro_version = apply_filters('admin_menu_editor_is_pro', false);
$is_second_toolbar_visible = isset($_COOKIE['ame-show-second-toolbar']) && (intval($_COOKIE['ame-show-second-toolbar']) === 1);
$is_compact_layout_enabled = isset($_COOKIE['ame-compact-layout']) && (intval($_COOKIE['ame-compact-layout']) === 1);
$is_multisite = is_multisite();

$icons = array(
	'cut' => '/gnome-icon-theme/edit-cut-blue.png',
	'copy' => '/gion/edit-copy.png',
	'paste' => '/gnome-icon-theme/edit-paste.png',
	'hide'  =>  '/page-invisible.png',
	'hide-and-deny'  =>  '/font-awesome/eye-slash-color.png',
	'new' => '/page-add.png',
	'delete' => '/page-delete.png',
	'new-separator' => '/separator-add.png',
	'toggle-all' => '/check-all.png',
	'copy-permissions' => '/copy-permissions.png',
	'toggle-toolbar' => '/font-awesome/angle-double-down.png',
	'sort-ascending' => '/sort_ascending.png',
	'sort-descending' => '/sort_descending.png',
);
foreach($icons as $name => $url) {
	$icons[$name] = $images_url . $url;
}
$icons = apply_filters('admin_menu_editor-toolbar_icons', $icons, $images_url);

$toolbarButtons = new ameOrderedMap();
$toolbarButtons->addAll(array(
	'cut'           => array(
		'title' => 'Cut',
	),
	'copy'          => array(
		'title' => 'Copy',
	),
	'paste'         => array(
		'title' => 'Paste',
	),
	'separator-1'   => null,
	'new-menu'      => array(
		'title' => 'New menu',
		'iconName' => 'new',
	),
	'new-separator' => array(
		'title' => 'New separator',
		'topLevelOnly' => !$is_pro_version,
	),
	'delete'        => array(
		'title' => 'Delete menu',
		'class' => array('ws_delete_menu_button'),
	),
	'separator-2'   => null,
));

if ( !$is_pro_version ) {
	ame_register_sort_buttons($toolbarButtons);
}

if ( $editor_data['show_deprecated_hide_button'] ) {
	$toolbarButtons->insertBefore(
		'delete',
		'hide',
		array(
			'title' => 'Hide without preventing access (cosmetic)',
			'alt'   => 'Hide (cosmetic)',
		)
	);
}

$secondToolbarRow = new ameOrderedMap();
if ( $is_pro_version ) {
	//In the Pro version, the sort buttons are on the second row.
	ame_register_sort_buttons($secondToolbarRow);
}

$secondToolbarRowClasses = array('ws_second_toolbar_row');
if ( !$is_second_toolbar_visible ) {
	$secondToolbarRowClasses[] = 'hidden';
}

do_action('admin_menu_editor-register_toolbar_buttons', $toolbarButtons, $secondToolbarRow, $icons);

if ( count($secondToolbarRow) > 0 ) {
	$toolbarButtons->set(
		'toggle-toolbar',
		array(
			'title' => 'Toggle second toolbar',
			'alt'   => 'Toolbar toggle',
			'class' => array('ws_toggle_toolbar_button'),
			'topLevelOnly' => true,
		)
	);
}

/**
 * @param ameOrderedMap $buttons
 * @param array $icons
 * @param array $classes CSS classes to add to the toolbar row.
 */
function ame_output_toolbar_row($buttons, $icons, $classes = array()) {
	$classes = array_merge(array('ws_button_container'), $classes);
	printf('<div class="%s">', esc_attr(implode(' ', $classes)));

	foreach ($buttons as $key => $settings) {
		if ( $settings === null ) {
			echo '<div class="ws_separator">&nbsp;</div>';
			continue;
		}

		if ( !isset($settings['title']) ) {
			$settings['title'] = $key;
		}
		$action = isset($settings['action']) ? $settings['action'] : $key;

		$buttonClasses = array('ws_button');
		if ( !empty($settings['class']) ) {
			$buttonClasses = array_merge($buttonClasses, $settings['class']);
		}

		$attributes = array(
			'data-ame-button-action' => $action,
			'class'                  => implode(' ', $buttonClasses),
			'href'                   => '#',
			'title'                  => $settings['title'],
		);
		if ( isset($settings['attributes']) ) {
			$attributes = array_merge($attributes, $settings['attributes']);
		}

		$iconName = isset($settings['iconName']) ? $settings['iconName'] : $key;
		$icon = '';
		if ( isset($icons[$iconName]) ) {
			$icon = sprintf(
				'<img src="%s" alt="%s">',
				esc_attr($icons[$iconName]),
				esc_attr(isset($settings['alt']) ? $settings['alt'] : $settings['title'])
			);
		}

		$pairs = array();
		foreach ($attributes as $name => $value) {
			$pairs[] = $name . '="' . esc_attr($value) . '"';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attribute $pairs and $icon attributes were escaped with esc_attr() above.
		printf('<a %s>%s</a>' . "\n", implode(' ', $pairs), $icon);
	}

	echo '<div class="clear"></div>' . "\n";
	echo '</div>';
}

//Output the "Upgrade to Pro" message
if ( !apply_filters('admin_menu_editor_is_pro', false) ){
	?>
	<script type="text/javascript">
	(function($){
		var screenLinks = $('#screen-meta-links');
		screenLinks.append(
			'<div id="ws-pro-version-notice" class="custom-screen-meta-link-wrap">' +
				'<a href="https://adminmenueditor.com/upgrade-to-pro/?utm_source=Admin%2BMenu%2BEditor%2Bfree&utm_medium=text_link&utm_content=top_upgrade_link&utm_campaign=Plugins" id="ws-pro-version-notice-link" class="show-settings custom-screen-meta-link" target="_blank" title="View Pro version details">Upgrade to Pro</a>' +
			'</div>'
		);
	})(jQuery);
	</script>
	<?php
}

?>

<?php do_action('admin_menu_editor-display_header'); ?>

<?php
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Don't need that here, just showing a notice.
if ( !empty($_GET['message']) && (intval($_GET['message']) == 2) ){
	echo '<div id="message" class="error"><p><strong>Failed to decode input! The menu wasn\'t modified.</strong></p></div>';
}

include dirname(__FILE__) . '/../modules/access-editor/access-editor-template.php';
$extrasDirectory = dirname(__FILE__) . '/../extras';
if ( $is_pro_version ) {
	include $extrasDirectory . '/copy-permissions-dialog.php';
}

/**
 * @param ameOrderedMap $toolbar
 */
function ame_register_sort_buttons($toolbar) {
	$toolbar->addAll(array(
		'sort-ascending'  => array(
			'title'      => 'Sort ascending',
			'action'     => 'sort',
			'attributes' => array(
				'data-sort-direction' => 'asc',
			),
		),
		'sort-descending' => array(
			'title'      => 'Sort descending',
			'action'     => 'sort',
			'attributes' => array(
				'data-sort-direction' => 'desc',
			),
		),
	));
}

?>

<div id='ws_menu_editor' style="visibility: hidden;" class="<?php
	if ( $is_compact_layout_enabled ) {
		echo 'ws_compact_layout';
	} else {
		echo 'ws_large_layout';
	}
?>">

	<?php include dirname(__FILE__) . '/../modules/actor-selector/actor-selector-template.php'; ?>

    <div>

	<div class='ws_main_container'>
		<div class='ws_toolbar'>
			<?php
			ame_output_toolbar_row($toolbarButtons, $icons);
			ame_output_toolbar_row($secondToolbarRow, $icons, $secondToolbarRowClasses);
			?>
		</div>

		<div id='ws_menu_box' class="ws_box">
		</div>

		<div id="ws_top_menu_dropzone" class="ws_dropzone"> </div>
		<?php do_action('admin_menu_editor-container', 'menu'); ?>
	</div>

	<div class='ws_main_container' id="ame-submenu-column-template" style="display: none;">
		<div class='ws_toolbar'>
			<?php
			function ame_button_can_be_in_submenu_toolbar($settings) {
				return empty($settings['topLevelOnly']);
			}

			ame_output_toolbar_row(
				$toolbarButtons->filter('ame_button_can_be_in_submenu_toolbar'),
				$icons
			);

			ame_output_toolbar_row(
				$secondToolbarRow->filter('ame_button_can_be_in_submenu_toolbar'),
				$icons,
				$secondToolbarRowClasses
			);
			?>
		</div>

		<div id='ws_submenu_box' class="ws_box">
		</div>

		<?php do_action('admin_menu_editor-container', 'submenu'); ?>
	</div>

	<div class="ws_basic_container">

		<div class="ws_main_container" id="ws_editor_sidebar">
		<form method="post" action="<?php echo esc_url(add_query_arg('noheader', '1', $editor_data['current_tab_url'])); ?>" id='ws_main_form' name='ws_main_form'>
			<?php wp_nonce_field('menu-editor-form'); ?>
			<input type="hidden" name="action" value="save_menu">
			<?php
			printf('<input type="hidden" name="config_id" value="%s">', esc_attr($editor_data['menu_config_id']));
			?>
			<input type="hidden" name="data" id="ws_data" value="">
			<input type="hidden" name="data_length" id="ws_data_length" value="">
			<input type="hidden" name="selected_actor" id="ws_selected_actor" value="">

			<input type="hidden" name="selected_menu_url" id="ws_selected_menu_url" value="">
			<input type="hidden" name="selected_submenu_url" id="ws_selected_submenu_url" value="">

			<input type="hidden" name="expand_menu" id="ws_expand_selected_menu" value="">
			<input type="hidden" name="expand_submenu" id="ws_expand_selected_submenu" value="">

			<input type="hidden" name="deep_nesting_enabled" id="ws_is_deep_nesting_enabled" value="">

			<button type="button" id='ws_save_menu' class="button button-primary ws_main_button">Save Changes</button>
		</form>

			<input type="button" id='ws_reset_menu' value="Undo changes" class="button ws_main_button" />
			<input type="button" id='ws_load_menu' value="Load default menu" class="button ws_main_button" />

			<!--
			<input type="button" id='ws_test_access' value="Test access..." class="button ws_main_button" />
			-->

			<?php
			$compact_layout_title = 'Compact layout';
			if ( $is_compact_layout_enabled ) {
				$compact_layout_title = '&#x2713; ' . $compact_layout_title;
			}
			?>
			<input type="button"
			       id='ws_toggle_editor_layout'
			       value="<?php echo esc_attr($compact_layout_title); ?>"
			       class="button ws_main_button" />

			<?php
				do_action('admin_menu_editor-sidebar');
			?>
		</div>

		<div class="clear"></div>
		<div class="metabox-holder">
		<?php
		if ( apply_filters('admin_menu_editor-show_general_box', false) ) :
			$is_general_box_open = true;
			if ( isset($_COOKIE['ame_vis_box_open']) ) {
				$is_general_box_open = ($_COOKIE['ame_vis_box_open'] === '1');
			}
			$box_class = $is_general_box_open ? '' : 'closed';

			?>
				<div class="postbox ws_ame_custom_postbox <?php echo esc_attr($box_class); ?>" id="ws_ame_general_vis_box">
					<button type="button" class="handlediv button-link">
						<span class="toggle-indicator"></span>
					</button>
					<h2 class="hndle">General</h2>
					<div class="inside">
						<?php do_action('admin_menu_editor-general_box'); ?>
					</div>
				</div>
			<?php
		endif;

		$is_how_to_box_open = true;
		if ( isset($_COOKIE['ame_how_to_box_open']) ) {
			$is_how_to_box_open = ($_COOKIE['ame_how_to_box_open'] === '1');
		}
		$box_class = $is_how_to_box_open ? '' : 'closed';

		if ( $is_pro_version ) {
			$tutorial_base_url = 'https://adminmenueditor.com/documentation/';
		} else {
			$tutorial_base_url = 'https://adminmenueditor.com/free-version-docs/';
		}

		/** @noinspection HtmlUnknownTarget */
		$how_to_link_template = '<a href="' . esc_url($tutorial_base_url) . '%1$s" target="_blank" title="Opens in a new tab">%2$s</a>';
		$how_to_item_template = '<li>' . $how_to_link_template . '</li>';

		?>
			<div class="postbox ws_ame_custom_postbox <?php echo esc_attr($box_class); ?>" id="ws_ame_how_to_box">
				<button type="button" class="handlediv button-link">
					<span class="toggle-indicator"></span>
				</button>
				<h2 class="hndle">How To</h2>
				<div class="inside">
					<ul class="ame-tutorial-list">
						<?php
						if ( $is_pro_version ):
							//Pro version tutorials.
							?>
							<li><?php
								printf(
									//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML template contains HTML.
									$how_to_link_template,
									'how-to-hide-a-menu-item/',
									'Hide a Menu...'
								);
								?>
								<ul class="ame-tutorial-list">
									<?php
									foreach (
										array(
											'how-to-hide-a-menu-item/#how-to-hide-a-menu-from-a-role'                   => 'From a Role',
											'how-to-hide-a-menu-item/#how-to-hide-a-menu-from-a-user'                   => 'From a User',
											'how-to-hide-a-menu-item/#how-to-hide-a-menu-from-everyone-except-yourself' => 'From Everyone Except You',
											'how-to-hide-menu-without-preventing-access/'                               => 'Without Preventing Access',
										)
										as $how_to_url => $how_to_title
									) {
										printf(
											//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML template.
											$how_to_item_template,
											esc_attr($how_to_url),
											esc_html($how_to_title)
										);
									}
									?>
								</ul>
							</li>
							<?php
							foreach (
								array(
									'how-to-give-access-to-menu/' => 'Show a Menu',
									'how-to-move-and-sort-menus/' => 'Move and Sort Menus',
									'how-to-add-a-new-menu-item/' => 'Add a New Menu',
								)
								as $how_to_url => $how_to_title
							) {
								printf(
								//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML template
									$how_to_item_template,
									esc_attr($how_to_url),
									esc_html($how_to_title)
								);
							}

						else:
							//Free version tutorials.
							foreach (
								array(
									'how-to-hide-menus/'          => 'Hide a Menu Item',
									'how-to-hide-menus-cosmetic/' => 'Hide Without Blocking Access',
									'how-to-add-new-menu/'        => 'Add a New Menu',
								)
								as $how_to_url => $how_to_title
							) {
								printf(
								//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML template
									$how_to_item_template,
									esc_attr($how_to_url),
									esc_html($how_to_title)
								);
							}
						endif;
						?>
					</ul>
				</div>
			</div>
		</div> <!-- / .metabox-holder -->

		<?php
		$hint_id = 'ws_sidebar_pro_ad';
		$show_pro_benefits = !apply_filters('admin_menu_editor_is_pro', false) && (!isset($editor_data['show_hints'][$hint_id]) || $editor_data['show_hints'][$hint_id]);

		if ( $show_pro_benefits):
			//Decide whether to show the Pro version link, or a link to one of our other WP tools.
			$hash_value = hexdec(substr(md5(get_site_url()), 0, 5)) % 100;

			//Pick one option proportionally to the weights. Weights should add up to 100.
			$sidebar_ad_weights = [
				'pro'                 => 80,
				'adminNoticesBoss'    => 18,
				'adminThemeGenerator' => 2,
			];
			$chosen_ad = array_keys($sidebar_ad_weights)[0];
			$cumulative_weight = 0;
			foreach ($sidebar_ad_weights as $ad => $weight) {
				$cumulative_weight += $weight;
				$chosen_ad = $ad;
				if ($hash_value < $cumulative_weight) {
					break;
				}
			}

			if ($chosen_ad === 'pro'):
				$benefit_variations = array(
					'Hide dashboard widgets.',
					'More menu icons.',
					'Make menus open in a new tab or an iframe.',
					'Prevent users from deleting a specific user.',
				);
				//Pseudo-randomly select one phrase based on the site URL.
				$variation_index = hexdec( substr(md5(get_site_url() . 'ab'), -2) ) % count($benefit_variations);
				$selected_variation = $benefit_variations[$variation_index];

				$pro_version_link = 'https://adminmenueditor.com/upgrade-to-pro/?utm_source=Admin%2BMenu%2BEditor%2Bfree&utm_medium=text_link&utm_content=sidebar_link_nv' . $variation_index . '&utm_campaign=Plugins';
				?>
				<div class="clear"></div>

				<div class="ws_hint" id="<?php echo esc_attr($hint_id); ?>">
					<div class="ws_hint_close" title="Close">x</div>
					<div class="ws_hint_content">
						<strong>Upgrade to Pro:</strong>
						<ul>
							<li>Role-based menu permissions.</li>
							<li>Hide items from specific users.</li>
							<li>Menu import and export.</li>
							<li>Change menu colors.</li>
							<li><?php echo esc_html($selected_variation); ?></li>
						</ul>
						<a href="<?php echo esc_url($pro_version_link); ?>" target="_blank">Learn more</a>
						|
						<a href="https://amedemo.com/" target="_blank">Try online demo</a>
					</div>
				</div>
			<?php
			elseif ($chosen_ad === 'adminThemeGenerator'):
				?>
				<div class="clear"></div>

				<div class="ws_hint ame-tgc-sidebar-ad" id="<?php echo esc_attr($hint_id); ?>">
					<div class="ws_hint_close" title="Close">x</div>
					<div class="ws_hint_content">
						<strong>Discover AdminThemeGenerator.com</strong>
						<ul>
							<li>A free
								<abbr title="An admin theme is a plugin that changes the appearance of the WordPress admin dashboard">
									admin theme
								</abbr>
								generator based on the Pro version of Admin Menu Editor.
							</li>
							<li>Design your own admin color scheme.</li>
							<li>Change admin menu and Toolbar size.</li>
							<li>Customize button, widget, and table styles.</li>
						</ul>
						<a href="<?php echo esc_url('https://adminthemegenerator.com/'); ?>" target="_blank">
							AdminThemeGenerator.com<span class="dashicons dashicons-external"></span>
						</a>
					</div>
				</div>
			<?php
			elseif ($chosen_ad === 'adminNoticesBoss'):
				?>
				<div class="clear"></div>

				<div class="ws_hint ame-anb-sidebar-ad" id="<?php echo esc_attr($hint_id); ?>">
					<div class="ws_hint_close" title="Close">x</div>
					<div class="ws_hint_content">
						<strong><span>Check out my other plugin: </span>Admin Notices Boss</strong>
						<ul>
							<li>Hide individual admin notices permanently.</li>
							<li>Move notices to an unobtrusive panel.</li>
							<li>Hide notices for specific roles or users.</li>
							<li>Hide notices based on type (warning, info, etc).</li>
						</ul>
						<a href="<?php echo esc_url('https://adminnoticesboss.com/'); ?>" target="_blank">
							AdminNoticesBoss.com<span class="dashicons dashicons-external"></span>
						</a>
					</div>
				</div>
			<?php
			endif;
		endif;
		?>

	</div> <!-- / .ws_basic_container -->

    </div>

	<div class="clear"></div>

</div> <!-- / .ws_menu_editor -->

<?php do_action('admin_menu_editor-display_footer'); ?>



<?php
	//Create a pop-up capability selector
	$capSelector = array('<select id="ws_cap_selector" class="ws_dropdown" size="10">');

	$capSelector[] = '<optgroup label="Roles">';
 	foreach($editor_data['all_roles'] as $role_id => $role_name){
 		$capSelector[] = sprintf(
		 	'<option value="%s">%s</option>',
		 	esc_attr($role_id),
		 	esc_html($role_name)
	 	);
 	}
 	$capSelector[] = '</optgroup>';

 	$capSelector[] = '<optgroup label="Capabilities">';
 	foreach($editor_data['all_capabilities'] as $cap){
 		$capSelector[] = sprintf(
		 	'<option value="%s">%s</option>',
		 	esc_attr($cap),
		 	esc_html($cap)
	 	);
 	}
 	$capSelector[] = '</optgroup>';
 	$capSelector[] = '</select>';

	 // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated HTML, should be escaped above.
 	echo implode("\n", $capSelector);
?>

<!-- Menu icon selector widget -->
<div id="ws_icon_selector" class="ws_with_more_icons" style="display: none;">

	<div id="ws_icon_source_tabs">
	<ul class="ws_tool_tab_nav">
		<?php
		$iconSelectorTabs = apply_filters(
			'admin_menu_editor-icon_selector_tabs',
			array('ws_core_icons_tab' => 'Dashicons')
		);
		foreach($iconSelectorTabs as $tabId => $caption) {
			/** @noinspection HtmlUnknownAnchorTarget -- It's a printf placeholder. */
			printf('<li><a href="#%s">%s</a></li>', esc_attr($tabId), esc_html($caption));
		}
		?>
	</ul>

	<?php
	//Let the user select a custom icon via the media uploader.
	//We only support the new WP 3.5+ media API. Hence, the function_exists() check.
	if ( function_exists('wp_enqueue_media') ):
		?>
		<input type="button" class="button"
		       id="ws_choose_icon_from_media"
		       title="Upload an image or choose one from your media library"
		       value="Media Library">
		<div class="clear"></div>
		<?php
	endif;
	?>

	<div class="ws_tool_tab" id="ws_core_icons_tab">
		<div class="ws_icon_search_bar">
			<label>
				<span class="screen-reader-text">Icon search box</span>
				<input type="text" class="regular-text ws_icon_search_box" placeholder="Search icons">
			</label>
		</div>

	<?php
	//These dashicons are used in the default admin menu.
	$defaultDashicons = array(
		'admin-generic', 'dashboard', 'admin-post', 'admin-media', 'admin-links', 'admin-page', 'admin-comments',
		'admin-appearance', 'admin-plugins', 'admin-users', 'admin-tools', 'admin-settings', 'admin-network',
	);

	//The rest of Dashicons.
	$dashicons = array(
		'menu',
		'admin-site',
		'admin-home',
		'admin-collapse',
		'filter',
		'admin-customizer',
		'admin-multisite',
		'format-image',
		'format-gallery',
		'format-audio',
		'format-video',
		'format-chat',
		'format-status',
		'format-aside',
		'format-quote',
		'welcome-write-blog',
		'welcome-add-page',
		'welcome-view-site',
		'welcome-widgets-menus',
		'welcome-comments',
		'welcome-learn-more',
		'image-crop',
		'image-rotate',
		'image-rotate-left',
		'image-rotate-right',
		'image-flip-vertical',
		'image-flip-horizontal',
		'image-filter',
		'undo',
		'redo',
		'editor-bold',
		'editor-italic',
		'editor-ul',
		'editor-ol',
		'editor-quote',
		'editor-alignleft',
		'editor-aligncenter',
		'editor-alignright',
		'editor-insertmore',
		'editor-spellcheck',
		'editor-expand',
		'editor-contract',
		'editor-kitchensink',
		'editor-underline',
		'editor-justify',
		'editor-textcolor',
		'editor-paste-word',
		'editor-paste-text',
		'editor-removeformatting',
		'editor-video',
		'editor-customchar',
		'editor-outdent',
		'editor-indent',
		'editor-help',
		'editor-strikethrough',
		'editor-unlink',
		'editor-rtl',
		'editor-break',
		'editor-code',
		'editor-paragraph',
		'editor-table',
		'align-left',
		'align-right',
		'align-center',
		'align-none',
		'lock',
		'unlock',
		'calendar',
		'calendar-alt',
		'visibility',
		'hidden',
		'post-status',
		'edit',
		'edit-large',
		'sticky',
		'external',
		'arrow-up',
		'arrow-down',
		'arrow-left',
		'arrow-right',
		'arrow-up-alt',
		'arrow-down-alt',
		'arrow-left-alt',
		'arrow-right-alt',
		'arrow-up-alt2',
		'arrow-down-alt2',
		'arrow-left-alt2',
		'arrow-right-alt2',
		'leftright',
		'sort',
		'randomize',
		'list-view',
		'excerpt-view',
		'grid-view',
		'move',
		'hammer',
		'art',
		'migrate',
		'performance',
		'universal-access',
		'universal-access-alt',
		'tickets',
		'nametag',
		'clipboard',
		'heart',
		'megaphone',
		'schedule',
		'wordpress',
		'wordpress-alt',
		'pressthis',
		'update',
		'screenoptions',
		'cart',
		'feedback',
		'translation',
		'tag',
		'category',
		'archive',
		'tagcloud',
		'text',
		'media-archive',
		'media-audio',
		'media-code',
		'media-default',
		'media-document',
		'media-interactive',
		'media-spreadsheet',
		'media-text',
		'media-video',
		'playlist-audio',
		'playlist-video',
		'controls-play',
		'controls-pause',
		'controls-forward',
		'controls-skipforward',
		'controls-back',
		'controls-skipback',
		'controls-repeat',
		'controls-volumeon',
		'controls-volumeoff',
		'yes',
		'no',
		'no-alt',
		'plus',
		'plus-alt',
		'plus-alt2',
		'minus',
		'dismiss',
		'marker',
		'star-filled',
		'star-half',
		'star-empty',
		'flag',
		'info',
		'warning',
		'share',
		'share1',
		'share-alt',
		'share-alt2',
		'twitter',
		'rss',
		'email',
		'email-alt',
		'facebook',
		'facebook-alt',
		'networking',
		'googleplus',
		'location',
		'location-alt',
		'camera',
		'images-alt',
		'images-alt2',
		'video-alt',
		'video-alt2',
		'video-alt3',
		'vault',
		'shield',
		'shield-alt',
		'sos',
		'search',
		'slides',
		'analytics',
		'chart-pie',
		'chart-bar',
		'chart-line',
		'chart-area',
		'groups',
		'businessman',
		'id',
		'id-alt',
		'products',
		'awards',
		'forms',
		'testimonial',
		'portfolio',
		'book',
		'book-alt',
		'download',
		'upload',
		'backup',
		'clock',
		'lightbulb',
		'microphone',
		'desktop',
		'laptop',
		'tablet',
		'smartphone',
		'phone',
		'smiley',
		'index-card',
		'carrot',
		'building',
		'store',
		'album',
		'palmtree',
		'tickets-alt',
		'money',
		'thumbs-up',
		'thumbs-down',
		'layout',
		'paperclip',
		'email-alt2',
		'menu-alt',
		'trash',
		'heading',
		'insert',
		'align-full-width',
		'button',
		'align-wide',
		'ellipsis',
		'buddicons-activity',
		'buddicons-buddypress-logo',
		'buddicons-community',
		'buddicons-forums',
		'buddicons-friends',
		'buddicons-groups',
		'buddicons-pm',
		'buddicons-replies',
		'buddicons-topics',
		'buddicons-tracking',
		'admin-site-alt',
		'admin-site-alt2',
		'admin-site-alt3',
		'rest-api',
		'yes-alt',
		'buddicons-bbpress-logo',
		'tide',
		'editor-ol-rtl',
		'instagram',
		'businessperson',
		'businesswoman',
		'color-picker',
		'camera-alt',
		'editor-ltr',
		'cloud',
		'twitter-alt',
		'menu-alt2',
		'menu-alt3',
		'plugins-checked',
		'text-page',
		'update-alt',
		'code-standards',
		'align-pull-left',
		'align-pull-right',
		'block-default',
		'cloud-saved',
		'cloud-upload',
		'columns',
		'cover-image',
		'embed-audio',
		'embed-generic',
		'embed-photo',
		'embed-post',
		'embed-video',
		'exit',
		'html',
		'info-outline',
		'insert-after',
		'insert-before',
		'remove',
		'shortcode',
		'table-col-after',
		'table-col-before',
		'table-col-delete',
		'table-row-after',
		'table-row-before',
		'table-row-delete',
		'saved',
		'airplane',
		'amazon',
		'bank',
		'beer',
		'bell',
		'calculator',
		'coffee',
		'database-add',
		'database-export',
		'database-import',
		'database-remove',
		'database-view',
		'database',
		'drumstick',
		'edit-page',
		'food',
		'fullscreen-alt',
		'fullscreen-exit-alt',
		'games',
		'google',
		'hourglass',
		'linkedin',
		'money-alt',
		'open-folder',
		'pdf',
		'pets',
		'pinterest',
		'printer',
		'privacy',
		'reddit',
		'spotify',
		'superhero-alt',
		'superhero',
		'twitch',
		'whatsapp',
		'youtube',
		'car',
		'podio',
		'xing',
	);

	if ($editor_data['dashicons_available']) {
		function ws_ame_print_dashicon_option($icon, $isExtraIcon = false) {
			printf(
				'<div class="ws_icon_option%3$s" title="%1$s" data-icon-url="dashicons-%2$s">
					<div class="ws_icon_image dashicons dashicons-%2$s"></div>
				</div>',
				esc_attr(ucwords(str_replace('-', ' ', $icon))),
				$icon,
				$isExtraIcon ? ' ws_icon_extra' : ''
			);
		}

		foreach($defaultDashicons as $icon) {
			ws_ame_print_dashicon_option($icon);
		}
		foreach($dashicons as $icon) {
			ws_ame_print_dashicon_option($icon, true);
		}
	}

	$defaultIconImages = array(
		'Blue-grey cogwheel (generic menu icon used by old WP versions)' => admin_url('images/generic.png'),
	);
	foreach($defaultIconImages as $name => $icon) {
		printf(
			'<div class="ws_icon_option" data-icon-url="%1$s">
				<img src="%1$s" alt="%2$s">
			</div>',
			esc_attr($icon),
			esc_attr($name)
		);
	}

	?>
	<div class="ws_icon_option ws_custom_image_icon" title="Custom image" style="display: none;">
		<img src="<?php echo esc_url(admin_url('images/loading.gif')); ?>" alt="Loading indicator">
	</div>

		<div class="ws_no_matching_icons" style="display: none">No results found</div>
		<div class="clear"></div>
	</div>

		<?php do_action('admin_menu_editor-icon_selector'); ?>

	</div><!-- tab container -->

</div>

<span id="ws-ame-screen-meta-contents" style="display:none;">
	<label for="ws-hide-advanced-settings">
		<input type="checkbox" id="ws-hide-advanced-settings"<?php
			if ( $this->options['hide_advanced_settings'] ){
				echo ' checked="checked"';
			}
		?> /> Hide advanced options
	</label><br>
</span>


<!-- Confirmation dialog when hiding "Dashboard -> Home" -->
<div id="ws-ame-dashboard-hide-confirmation" style="display: none;">
	<span>
		Hiding <em>Dashboard -> Home</em> may prevent users with the selected role from logging in!
		Are you sure you want to do it?
	</span>

	<h4>Explanation</h4>
	<p>
		WordPress automatically redirects users to the <em>Dashboard -> Home</em> page upon successful login.
		If you hide this page, users will get an "insufficient permissions" error when they log in
		due to being redirected to a hidden page. As a result, it will look like their login failed.
	</p>

	<h4>Recommendations</h4>
	<p>
		You can use a plugin like <a href="http://wordpress.org/plugins/peters-login-redirect/">Peter's Login Redirect</a>
		to redirect specific roles to different pages.
	</p>

	<div class="ws_dialog_buttons">
		<?php
		submit_button('Hide the menu', 'primary', 'ws_confirm_menu_hiding', false);
		submit_button('Leave it visible', 'secondary', 'ws_cancel_menu_hiding', false);
		?>
	</div>

	<label class="ws_dont_show_again">
		<input type="checkbox" id="ws-ame-disable-dashboard-hide-confirmation">
		Don't show this message again
	</label>
</div>

<!-- Confirmation dialog when trying to delete a non-custom item. -->
<div id="ws-ame-menu-deletion-error" title="Error" style="display: none;">
	<div class="ws_dialog_panel">
		Sorry, it's not possible to permanently delete
		<span id="ws-ame-menu-type-desc">{a built-in menu item|an item added by another plugin}</span>.
		Would you like to hide it instead?
	</div>

	<div class="ws_dialog_buttons ame-vertical-button-list">
		<?php
		submit_button('Hide it from all users', 'secondary', 'ws_hide_menu_from_everyone', false);
		submit_button(
			sprintf('Hide it from everyone except "%s"', $ame_current_user->get('user_login')),
			'secondary',
			'ws_hide_menu_except_current_user',
			false
		);
		submit_button(
			'Hide it from everyone except Administrator',
			'secondary',
			'ws_hide_menu_except_administrator',
			false
		);
		submit_button('Cancel', 'secondary', 'ws_cancel_menu_deletion', false);
		?>
	</div>
</div>

<?php include dirname(__FILE__) . '/cap-suggestion-box.php'; ?>

<?php include dirname(__FILE__) . '/test-access-screen.php'; ?>

<?php
if ( $is_pro_version ) {
	include $extrasDirectory . '/page-dropdown.php';
}
?>


<!--suppress JSUnusedLocalSymbols These variables are actually used by menu-editor.js -->
<script type='text/javascript'>
	var defaultMenu = <?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Should be JSON.
		echo $editor_data['default_menu_js'];
		?>;
	var customMenu = <?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Should also be JSON.
		echo $editor_data['custom_menu_js'];
		?>;
</script>
