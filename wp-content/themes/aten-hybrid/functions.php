<?php

// Enqueue Parent Theme Styles
function enqueue_parent_theme_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_parent_theme_styles' );

/**
 * Add custom child theme styles to site.
 */
add_action('wp_enqueue_scripts', 'ccc_enqueue_styles', 11);
function ccc_enqueue_styles() {
	// dequeue the Twenty Twenty-One parent style
	wp_dequeue_style( 'twenty-twenty-one-style' ); 

	// Theme stylesheets
	wp_enqueue_style( 'child-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version') );

	// Add external styles
	wp_enqueue_style('theme-fonts', '//use.typekit.net/zkj5mew.css', []);
	wp_enqueue_style('theme-icons-outline', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined', []);

	// Custom JS 
	wp_enqueue_script( 'utility-functions', get_stylesheet_directory_uri() . '/js/utility-functions.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'animations', get_stylesheet_directory_uri() . '/js/animations.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'accordion-block', get_stylesheet_directory_uri() . '/js/accordion-block.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'button-block', get_stylesheet_directory_uri() . '/js/button-block.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'cta-block', get_stylesheet_directory_uri() . '/js/cta-block.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'image-gallery-block', get_stylesheet_directory_uri() . '/js/image-gallery-block.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'legal-disclaimer', get_stylesheet_directory_uri() . '/js/legal-disclaimer.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'login', get_stylesheet_directory_uri() . '/js/login.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'main-navigation', get_stylesheet_directory_uri() . '/js/main-navigation.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'membership-forms', get_stylesheet_directory_uri() . '/js/membership-forms.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'notification-bar', get_stylesheet_directory_uri() . '/js/notification-bar.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'pagination', get_stylesheet_directory_uri( ) . '/js/pagination.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'resource-filters', get_stylesheet_directory_uri() . '/js/resource-filters.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'social-listening-map', get_stylesheet_directory_uri( ) . '/js/social-listening-map.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'video-gallery-block', get_stylesheet_directory_uri() . '/js/video-gallery-block.js', array( 'jquery' ), '1.0', true );
}

/** 
 * Theme Support setup
 */
add_action( 'after_setup_theme', 'ccc_theme_support_settings' );
function ccc_theme_support_settings() {
	add_theme_support( 'disable-custom-font-sizes' );
	add_theme_support( 'disable-custom-colors' );
	add_theme_support( 'disable-custom-gradients' );
	add_theme_support( 'disable-layout-styles' );
	remove_theme_support( 'core-block-patterns' );
}

add_action( 'admin_init', 'wpdocs_add_editor_styles' );
function wpdocs_add_editor_styles() {
  add_theme_support( 'editor-styles' );
  add_editor_style( 'editor-style.css' );
}


add_action('enqueue_block_editor_assets', function () {
    // Remove editor style resets
    wp_deregister_style('wp-reset-editor-styles');
	
    // Replace with our own version (WP has dependency on this slug)
    wp_enqueue_style('wp-reset-editor-styles', get_theme_file_uri('editor-style.css'),
    array( 'common', 'forms' ));
}, 102);


add_filter( 'body_class', 'ccc_body_wrapper_class' );
function ccc_body_wrapper_class( $classes ) {
    $classes[] = 'ccc-theme';
	return $classes;
}

/**
 * Register widget area.
 *
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 *
 * @return void
 */
function ccc_widgets_init() {

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Text Area', 'ccc' ),
			'id'            => 'sidebar-2',
			'description'   => esc_html__( 'Add widgets here to appear in your footer.', 'ccc' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'ccc_widgets_init' );

/*
 * Convert all H1 headings added via the heading block to H2
 */
add_filter('render_block', function($block_content, $block) {
	if ( $block['blockName'] === 'core/heading' ) {
		$block_content = str_replace('<h1', '<h2', $block_content);
		$block_content = str_replace('</h1', '</h2', $block_content);
	}
  
	return $block_content;
  }, 10, 2);
  
/*
* Removing H1 from all TinyMCE text editors (WYSIWYG)
*/
add_filter( 'tiny_mce_before_init', function( $settings ){
	$settings['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6';
	return $settings;
} );
  
/*
 * Control what options appear in the backend editor block toolbars
 */
function control_block_toolbar_settings () {

	wp_enqueue_script(
		'control-block-toolbar-settings',
		get_stylesheet_directory_uri( ) . '/js/block-toolbar-settings.js',
		array( 'wp-block-editor', 'wp-blocks', 'wp-dom' ),
		time(),
		true
	);
}
add_action('enqueue_block_assets', 'control_block_toolbar_settings');

/**
 * Adjusting the path for the ACF Icon Selector to use the Material Font Icons inside of /assets/icons/acf-icons
 */
add_filter( 'acf_icon_path_suffix', 'acf_icon_path_suffix' );
function acf_icon_path_suffix( $path_suffix ) {
	return 'assets/icons/acf-icons/';
}
add_filter( 'acf_icon_path', 'acf_icon_path' );
function acf_icon_path( $path_suffix ) {
	return plugin_dir_path( __FILE__ );
}
add_filter( 'acf_icon_url', 'acf_icon_url' );
function acf_icon_url( $path_suffix ) {
	return plugin_dir_url( __FILE__ );
}


/**
 * Register custom blocks for the CCC theme.
 */
add_action('init', 'register_acf_blocks');
function register_acf_blocks() {
	register_block_type(__DIR__ . '/blocks/accordion-block');
	register_block_type(__DIR__ . '/blocks/call-to-action');
	register_block_type(__DIR__ . '/blocks/dashboard-cta-panel');
	register_block_type(__DIR__ . '/blocks/dashboard-featured-resources');
	register_block_type(__DIR__ . '/blocks/dashboard-hero');
	register_block_type(__DIR__ . '/blocks/dashboard-tools');
	register_block_type(__DIR__ . '/blocks/homepage-about');
	register_block_type(__DIR__ . '/blocks/homepage-card-panel');
	register_block_type(__DIR__ . '/blocks/homepage-hero');
	register_block_type(__DIR__ . '/blocks/image-gallery-block');
	register_block_type(__DIR__ . '/blocks/page-header');
	register_block_type(__DIR__ . '/blocks/pullquote');
	register_block_type(__DIR__ . '/blocks/social-listening-map');
	register_block_type(__DIR__ . '/blocks/taxonomy-term-block');
	register_block_type(__DIR__ . '/blocks/video-gallery-block');
}

/**
 * Restrict available blocks by page
 */
add_filter( 'allowed_block_types_all', 'disable_specific_blocks', 10, 2 );
function disable_specific_blocks( $allowed_blocks ) {
	// Get current post ID
	$current_id = get_the_ID();
	// Get all the registered blocks.
	$blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
	// Removing all WP Core blocks that are unnecessary across all post types
	unset($blocks['core/legacy-widget']);
	unset($blocks['core/widget-group']);
	unset($blocks['core/archives']);
	unset($blocks['core/avatar']);
	unset($blocks['core/calendar']);
	unset($blocks['core/categories']);
	unset($blocks['core/comment-author-name']);
	unset($blocks['core/comment-content']);
	unset($blocks['core/comment-date']);
	unset($blocks['core/comment-edit-link']);
	unset($blocks['core/comment-reply-link']);
	unset($blocks['core/comment-template']);
	unset($blocks['core/comments']);
	unset($blocks['core/comments-pagination']);
	unset($blocks['core/comments-pagination-next']);
	unset($blocks['core/comments-pagination-numbers']);
	unset($blocks['core/comments-pagination-previous']);
	unset($blocks['core/comments-title']);
	unset($blocks['core/cover']);
	unset($blocks['core/home-link']);
	unset($blocks['core/latest-comments']);
	unset($blocks['core/latest-posts']);
	unset($blocks['core/loginout']);
	unset($blocks['core/navigation']);
	unset($blocks['core/navigation-link']);
	unset($blocks['core/navigation-submenu']);
	unset($blocks['core/page-list']);
	unset($blocks['core/post-author']);
	unset($blocks['core/post-author-biography']);
	unset($blocks['core/post-author-name']);
	unset($blocks['core/post-comments-form']);
	unset($blocks['core/post-navigation-link']);
	unset($blocks['core/post-template']);
	unset($blocks['core/post-terms']);
	unset($blocks['core/pullquote']);
	unset($blocks['core/query']);
	unset($blocks['core/query-no-results']);
	unset($blocks['core/query-pagination']);
	unset($blocks['core/query-pagination-next']);
	unset($blocks['core/query-pagination-numbers']);
	unset($blocks['core/query-pagination-previous']);
	unset($blocks['core/query-title']);
	unset($blocks['core/read-more']);
	unset($blocks['core/rss']);
	unset($blocks['core/site-logo']);
	unset($blocks['core/site-tagline']);
	unset($blocks['core/site-title']);
	unset($blocks['core/tag-cloud']);
	unset($blocks['core/template-part']);
	unset($blocks['core/term-description']);
	unset($blocks['core/audio']);
	unset($blocks['core/column']);
	unset($blocks['core/columns']);
	unset($blocks['core/freeform']);
	unset($blocks['core/group']);
	unset($blocks['core/gallery']);
	unset($blocks['core/media-text']);
	unset($blocks['core/more']);
	unset($blocks['core/nextpage']);
	unset($blocks['core/preformatted']);
	unset($blocks['core/quote']);
	unset($blocks['core/text-columns']);
	unset($blocks['core/verse']);
	unset($blocks['pmpro/checkout-button']);
	unset($blocks['pmpro/account-page']);
	unset($blocks['pmpro/account-membership-section']);
	unset($blocks['pmpro/account-profile-section']);
	unset($blocks['pmpro/account-invoices-section']);
	unset($blocks['pmpro/account-links-section']);
	unset($blocks['pmpro/billing-page']);
	unset($blocks['pmpro/cancel-page']);
	unset($blocks['pmpro/checkout-page']);
	unset($blocks['pmpro/confirmation-page']);
	unset($blocks['pmpro/invoice-page']);
	unset($blocks['pmpro/levels-page']);
	unset($blocks['pmpro/membership']);
	unset($blocks['pmpro/member-profile-edit']);
	unset($blocks['pmpro/login-form']);
	unset($blocks['core/post-comments']);

    // Checking for post IDs where page header should be disabled
	$disabled_page_header_ids = array( 34, 36, 38, 40, 42 );
	if(in_array($current_id, $disabled_page_header_ids)) {
		// Disable page header block
		unset($blocks['acf/page-header']);
	}

	// Checking for homepage ID
	$homepage_id = 34;
	// If not on the homepage
	if($current_id != $homepage_id) {
		// Then disable all homepage-only blocks
		unset($blocks['acf/homepage-about']);
		unset($blocks['acf/homepage-card-panel']);
		unset($blocks['acf/homepage-hero']);
	} else { // If currently editing the homepage
		// Create an array of keys with the names of the permitted Homepage blocks
		$allowed_block_keys = array('acf/homepage-about' => '', 'acf/homepage-card-panel' => '', 'acf/homepage-hero' => '');
		// Intersect the Homepage block keys with the list of WP blocks
		$homepage_blocks = array_intersect_key($blocks, $allowed_block_keys);
		// Only allow the Homepage blocks to be used on the homepage
		return array_keys($homepage_blocks);
	}

	// Checking for dashboard page ID
	$dashboard_id = 40;
	// If not on the dashboard
	if($current_id != $dashboard_id) {
		// Then disable all dashboard-only blocks
		unset($blocks['acf/dashboard-cta-panel']);
		unset($blocks['acf/dashboard-featured-resources']);
		unset($blocks['acf/dashboard-hero']);
		unset($blocks['acf/dashboard-tools']);
	} else { // If currently editing the dashboard
		// Create an array of keys with the names of the permitted dashboard blocks
		$allowed_block_keys = array(
			'acf/dashboard-cta-panel' => '',
			'acf/dashboard-featured-resources' => '',
			'acf/dashboard-hero' => '',
			'acf/dashboard-tools' => '', 
		);
		// Intersect the dashboard block keys with the list of WP blocks
		$dashboard_blocks = array_intersect_key($blocks, $allowed_block_keys);
		// Only allow the dashboard blocks to be used on the dashboard page
		return array_keys($dashboard_blocks);
	}

		// Checking for Social Map page ID
		$social_map_id = 42;
		// If not on the dashboard
		if($current_id != $social_map_id) {
			// Then disable the social listening map block
			unset($blocks['acf/social-listening-map']);
		}

	// Return the new list of allowed blocks.
	return array_keys($blocks);
}

///**
// * Populating default page header block on every new page instance
// */
//add_action( 'init', 'ccc_page_header_default_block',20 );
//function ccc_page_header_default_block() {
//	$template = array(
//	  array('acf/page-header', array(
//		'data' => array(
//		),
//		'mode' => 'preview'
//	  ))
//	);
//	$post_type_object = get_post_type_object( 'page' );
//	$post_type_object->template = $template;
//
//	$post_type_object = get_post_type_object( 'research' );
//	$post_type_object->template = $template;
//
//	$post_type_object = get_post_type_object( 'message' );
//	$post_type_object->template = $template;
//}
 
/** 
 * Creating custom navigation to display with a shortcode
 */
//add_shortcode('navigation_main_menu', 'ccc_render_main_navigation_menu');
//function ccc_render_main_navigation_menu($args = array()) {
//	if ( !class_exists('CCC_Nav_Walker') ) {
//		class CCC_Nav_Walker extends Walker_Nav_Menu {
//			function start_el(&$output, $item, $depth=0, $args=[], $id=0) {
//				$output .= "<li class='" .  implode(" ", $item->classes) . "'>";
//				if (!(in_array("menu-item-has-children", $item->classes))) {
//					if((in_array("current-menu-item", $item->classes))) {
//						$output .= '<a href="' . $item->url . '" aria-current="page">';
//					} else {
//						$output .= '<a href="' . $item->url . '">';
//					}
//
//				} else {
//					$output .= '<button class="ccc-megamenu-button" aria-haspopup="true" aria-expanded="false">';
//				}
//				$output .= $item->title;
//				if (!(in_array("menu-item-has-children", $item->classes))) {
//					$output .= '</a>';
//				} else {
//					$output .= '</button>';
//				}
//			}
//		}
//	}
//
//	ob_start();
//
//        $menu_id = '';
//        if(is_user_logged_in()) {
//            $menu_id = 4;
//        } else {
//            $menu_id = 5;
//        }
//
//        $menu_name = 'Main Navigation Menu';
//        $menu_slug = 'main-nav-menu';
//        $menu_prefixed_id = 'ccc-megamenu-' . $menu_slug;
//
//		register_nav_menus( array( $menu_slug => esc_html__( $menu_name, get_stylesheet() ) ) );
//
//		wp_nav_menu( array(
//			'menu'			 		=> $menu_id,
//			'container'		 		=> '',
//			'menu_class'	 		=> 'ccc-megamenu',
//			'menu_id'		  	    => $menu_prefixed_id,
//			'walker' 			    => new CCC_Nav_Walker
//		) );
//
//	return ob_get_clean();
//}

/**
 * Hiding custom taxonomy metaboxes from the editor UI
 * Requires custom taxonomy CPT UI option "Metabox Callback" to have a value of: false
 * Currently hidden taxonomy metaboxes: 
 * - Message Category
 * - Message Topic
 * - Research Category
 * - Research Partner
 * - Research Topic
 */
add_filter( 'rest_prepare_taxonomy', 'ccc_remove_custom_taxonomy_metaboxes', 10, 3 );
function ccc_remove_custom_taxonomy_metaboxes( $response, $taxonomy, $request ){
	$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
	// Context is edit in the editor
	if( $context === 'edit' && $taxonomy->meta_box_cb === false ){
		$data_response = $response->get_data();
		$data_response['visibility']['show_ui'] = false;
		$response->set_data( $data_response );
		}
	return $response;
}

/**
 * Updating custom taxonomy terms via ACF fields for cleaner UI
 */
add_action('acf/save_post', 'my_acf_save_post', 5);
function my_acf_save_post( $post_id ) {
	// If updating the message category value
	if( isset($_POST['acf']['field_65130c24af62d']) ) {
		// Set the message category taxonomy from the ACF val
		$category_id = $_POST['acf']['field_65130c24af62d'];
		$taxonomy = 'message-category';
		wp_set_object_terms( $post_id, intval( $category_id ), $taxonomy );
	}

	// If updating the message topics value
	if( isset($_POST['acf']['field_65130c70af62e']) ) {
		// Set the message topics taxonomy from the ACF val
		$topics = $_POST['acf']['field_65130c70af62e'];
		$topic_ids = array_map( 'intval', $topics );
		$topic_ids = array_unique( $topic_ids );
		$taxonomy = 'message-topic';
		wp_set_object_terms( $post_id, $topic_ids, $taxonomy );
	}

	// If updating the research category value
	if( isset($_POST['acf']['field_65130d5348e27']) ) {
		// Set the research category taxonomy from the ACF val
		$category_id = $_POST['acf']['field_65130d5348e27'];
		$taxonomy = 'research-category';
		wp_set_object_terms( $post_id, intval( $category_id ), $taxonomy );
	}

	// If updating the research topics value
	if( isset($_POST['acf']['field_65130e48ef234']) ) {
		// Set the research topics taxonomy from the ACF val
		$topics = $_POST['acf']['field_65130e48ef234'];
		$topic_ids = array_map( 'intval', $topics );
		$topic_ids = array_unique( $topic_ids );
		$taxonomy = 'research-topic';
		wp_set_object_terms( $post_id, $topic_ids, $taxonomy );
	}

	// If updating the research partner value
	if( isset($_POST['acf']['field_64fb4ee5a51e8']) ) {
		// Set the research partner taxonomy from the ACF val
		$partner_id = $_POST['acf']['field_64fb4ee5a51e8'];
		$taxonomy = 'research-partner';
		wp_set_object_terms( $post_id, intval( $partner_id ), $taxonomy );
	}
}

/**
 * Redirect all users to the user dashboard on login, unless a redirect is in place.
 * 
 * Snippet adapted from Paid Memberships Pro: https://www.paidmembershipspro.com/redirect-members/
 */
function my_login_redirect( $redirect_to ) {
	$membership_account_string = '/membership-account/';

	// If there is a redirect URL and the redirect is not set to the My Account page
	if ( ! empty( $redirect_to ) && ! str_contains($redirect_to, $membership_account_string) ) {
		// Redirect the user
		return $redirect_to;
	}
	
	// Redirecting to user dashboard if no redirect is in place
	$redirect_to = home_url('/dashboard/');
	
	return $redirect_to;
}
add_filter( 'login_redirect', 'my_login_redirect', 999 );


/*
Define the global array below for your main accounts and sponsored levels.
Array keys should be the main account level.
*/
global $pmprosm_sponsored_account_levels;
$pmprosm_sponsored_account_levels = array(
	//set 25 seats at checkout
	2 => array(  // group level ID
		'main_level_id' => 2, //group leader level ID same as above
		'sponsored_level_id' => array(3), //array or single id
		'seats' => 25
	)
);

/**
 *  Allowing control over the archive query posts_per_page based on ACF Options fields
 */
add_action( 'pre_get_posts', 'dynamic_resource_per_page_query_parameter' );
function dynamic_resource_per_page_query_parameter( $query ) {
	if( !is_admin() && (is_post_type_archive('research')) && $query->is_main_query() ) {    
		$research_fields = get_field('research_archive', 'option');
		$research_per_page = ($research_fields && $research_fields['research_posts_per_page']) ? $research_fields['research_posts_per_page'] : 8;
		$query->query_vars['posts_per_page'] = $research_per_page;
	}

	if( !is_admin() && (is_post_type_archive('message')) && $query->is_main_query() ) {    
		$message_fields = get_field('message_lab_archive', 'option');
		$message_per_page = ($message_fields && $message_fields['message_posts_per_page']) ? $message_fields['message_posts_per_page'] : 8;
		$query->query_vars['posts_per_page'] = $message_per_page;
	}
}

/**
 * Creating a shortcode to render the corner graphic and wrap the PMP login form
 */
add_shortcode('ccc_custom_login', 'ccc_custom_login_shortcode');
function ccc_custom_login_shortcode() {
	ob_start(); ?>

		<div class="ccc-login-wrap">
			<div class="content-start-triangle"><img src="<?php echo get_stylesheet_directory_uri();?>/assets/images/content-start-triangle.svg" alt="" /></div>
			<h1>Log in to the Cost & Coverage Collaborative Hub</h1>
			<?php echo do_shortcode('[pmpro_login]'); ?>
		</div>

	<?php return ob_get_clean();
}

function custom_pmpro_level_cost_text($level_cost, $level)
{
	// You can modify the level cost text here
	// For example, you can add a currency symbol
	$level_cost = 'Please fill out the information below and submit';

	return $level_cost;
}

add_filter('pmpro_level_cost_text', 'custom_pmpro_level_cost_text', 10, 2);

/*
  Only let level Group Leaders sign up if they use a discount code.
  Place this code in your active theme's functions.php or a custom plugin.
*/
function ccc_pmpro_registration_checks_require_code_to_register($pmpro_continue_registration)
{
	//only bother if things are okay so far
	if(!$pmpro_continue_registration)
		return $pmpro_continue_registration;

	//level = 1 and there is no discount code, then show an error message
	global $pmpro_level, $discount_code;

	if($pmpro_level->id == 3 && empty($discount_code))
	{
		pmpro_setMessage("You must use a valid discount code to register for the Group Member level.", "pmpro_error");
		return false;
	}

	return $pmpro_continue_registration;
}
add_filter("pmpro_registration_checks", "ccc_pmpro_registration_checks_require_code_to_register");

//function get_user_pending_status() {
//	$user_id = get_current_user_id();
//	global $level_id; // Access $level_id from the global scope
//	global $user_approval; // Access $user_approval from the global scope
//
//	$pending_status = apply_filters('pmproap_user_is_pending', false, $user_id, $level_id, $user_approval);
//	return $pending_status;
//}
//
//function display_user_pending_status_in_footer() {
//	echo '<p>Your pending status: ' . (get_user_pending_status() ? 'Pending' : 'Not Pending') . '</p>';
//}
//
//add_action('wp_footer', 'display_user_pending_status_in_footer');


///**
// * Function to check if a user is either an admin, or an approved member/leader of a group
// *
// * Returns a boolean value
// */
//function check_user_authentication() {
//	$authentication_status = false;
//
//	if (is_user_logged_in()) {
//		$user_id = get_current_user_id(); // Get the user's ID
//		$user_data = get_userdata($user_id);
//		$user_roles = $user_data->roles;
//
//		if (!empty($user_roles)) {
//			// The user's role is stored in the $user_roles array. You can access it like this:
//			$user_role = $user_roles[0]; // In case a user has multiple roles, you can choose the primary role.
//		}
//		$membership_level = pmpro_getMembershipLevelForUser( $user_id ); // Get the user's membership level
//		if(in_array('administrator', $user_roles)){
//			// User is logged in as an administrator
//			 $authentication_status = true; // admins
//		}
//		elseif ( $membership_level ) {
//			$level_id = $membership_level->id;
//			$approval_status = get_user_meta( $user_id, 'pmpro_approval_' . $level_id, true );
//			if ($approval_status['status'] !== 'pending') {
//				// User is logged in and has an approved membership
//				$authentication_status = true; // User is authenticated
//			}
//		}
//	}
//
//	return $authentication_status;
//}

function get_state_by_abbreviation($abbreviation) {
	$state_name = '';

	switch ($abbreviation) {
		case 'AK':
			$state_name = 'Alaska';
			break;
		case 'AL':
			$state_name = 'Alabama';
			break;
		case 'AR':
			$state_name = 'Arkansas';
			break;
		case 'AZ':
			$state_name = 'Arizona';
			break;
		case 'CA':
			$state_name = 'California';
			break;
		case 'CO':
			$state_name = 'Colorado';
			break;
		case 'CT':
			$state_name = 'Connecticut';
			break;
		case 'DC':
			$state_name = 'Washington, D.C.';
			break;
		case 'DE':
			$state_name = 'Delaware';
			break;
		case 'FL':
			$state_name = 'Florida';
			break;
		case 'GA':
			$state_name = 'Georgia';
			break;
		case 'HI':
			$state_name = 'Hawai\'i';
			break;
		case 'IA':
			$state_name = 'Iowa';
			break;
		case 'ID':
			$state_name = 'Idaho';
			break;
		case 'IL':
			$state_name = 'Illinois';
			break;	
		case 'IN':
			$state_name = 'Indiana';
			break;
		case 'KS':
			$state_name = 'Kansas';
			break;		
		case 'KY':
			$state_name = 'Kentucky';
			break;
		case 'LA':
			$state_name = 'Louisiana';
			break;	
		case 'MA':
			$state_name = 'Massachusetts';
			break;
		case 'MD':
			$state_name = 'Maryland';
			break;		
		case 'ME':
			$state_name = 'Maine';
			break;
		case 'MI':
			$state_name = 'Michigan';
			break;	
		case 'MN':
			$state_name = 'Minnesota';
			break;
		case 'MO':
			$state_name = 'Missouri';
			break;		
		case 'MS':
			$state_name = 'Mississippi';
			break;
		case 'MT':
			$state_name = 'Montana';
			break;	
		case 'NC':
			$state_name = 'North Carolina';
			break;
		case 'ND':
			$state_name = 'North Dakota';
			break;		
		case 'NE':
			$state_name = 'Nebraska';
			break;
		case 'NH':
			$state_name = 'New Hampshire';
			break;	
		case 'NJ':
			$state_name = 'New Jersey';
			break;
		case 'NM':
			$state_name = 'New Mexico';
			break;		
		case 'NV':
			$state_name = 'Nevada';
			break;
		case 'NY':
			$state_name = 'New York';
			break;	
		case 'OH':
			$state_name = 'Ohio';
			break;
		case 'OK':
			$state_name = 'Oklahoma';
			break;		
		case 'OR':
			$state_name = 'Oregon';
			break;
		case 'PA':
			$state_name = 'Pennsylvania';
			break;	
		case 'PR':
			$state_name = 'Puerto Rico';
			break;	
		case 'RI':
			$state_name = 'Rhode Island';
			break;
		case 'SC':
			$state_name = 'South Carolina';
			break;		
		case 'SD':
			$state_name = 'South Dakota';
			break;
		case 'TN':
			$state_name = 'Tennessee';
			break;	
		case 'TX':
			$state_name = 'Texas';
			break;
		case 'UT':
			$state_name = 'Utah';
			break;		
		case 'VA':
			$state_name = 'Virginia';
			break;
		case 'VT':
			$state_name = 'Vermont';
			break;	
		case 'WA':
			$state_name = 'Washington';
			break;	
		case 'WI':
			$state_name = 'Wisconsin';
			break;
		case 'WV':
			$state_name = 'West Virginia';
			break;		
		case 'WY':
			$state_name = 'Wyoming';
			break;
	}

	return $state_name;
}