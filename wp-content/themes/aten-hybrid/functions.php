<?php

/**
 * @file
 * Aten Hybrid functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Aten Hybrid
 * @since Twenty Twenty One 1.0
 */

// Enqueue Parent Theme Styles
function enqueue_parent_theme_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_parent_theme_styles' );

/**
 * Add custom child theme styles to site.
 */
add_action('wp_enqueue_scripts', 'aten_hybrid_enqueue_styles', 11);
function aten_hybrid_enqueue_styles() {
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
add_action( 'after_setup_theme', 'aten_hybrid_theme_support_settings' );
function aten_hybrid_theme_support_settings() {
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


add_filter( 'body_class', 'aten_hybrid_body_wrapper_class' );
function aten_hybrid_body_wrapper_class( $classes ) {
	$classes[] = 'aten-hybrid-theme';
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
function aten_hybrid_widgets_init() {

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Text Area', 'aten-hybrid' ),
			'id'            => 'sidebar-2',
			'description'   => esc_html__( 'Add widgets here to appear in your footer.', 'aten-hybrid' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'aten_hybrid_widgets_init' );

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
 * Register custom blocks for Aten FSE theme.
 */
add_action('init', 'register_acf_blocks');

/**
 * Retrieves a list of block files from the /blocks directory.
 *
 * This function scans the /blocks directory and for each subdirectory,
 * it creates an array with the block's name and the names of its CSS and
 * JS files, if they exist.
 *
 * @return array
 *   An array of associative arrays, each containing the name of a
 *   block and the names of its CSS and JS files.
 */
function get_block_files() {
  $dirs = glob(__DIR__ . '/blocks/*', GLOB_ONLYDIR);

  $block_files = [];

  foreach ($dirs as $dir) {
    $block_name = pathinfo($dir, PATHINFO_BASENAME);
    $css_file = "{$block_name}.css";
    $js_file = "{$block_name}.js";

    // Setting block name.
    $block = ['name' => $block_name];

    // If css file exists, add to the block array.
    if (file_exists(__DIR__ . "/blocks/{$block_name}/{$css_file}")) {
      $block['css'] = $css_file;
    }
    // If JS file exists, add to the block array.
    // @todo Update attachment to provide dependencies and load requirements.
    if (file_exists(__DIR__ . "/blocks/{$block_name}/{$js_file}")) {
      $block['js']['src'] = get_template_directory_uri() . "/blocks/{$block_name}/{$js_file}";
      $block['js']['deps'] = ['jquery'];
      $block['js']['ver'] = '1.0';
      $block['js']['in_footer'] = TRUE;
    }

    // Add block to the block files array.
    $block_files[] = $block;
  }

  return $block_files;
}

/**
 * Registers ACF blocks using the block files in the /blocks directory.
 *
 * This function uses the get_block_files function to retrieve an array
 *  of block files.
 * It then registers each block using acf_register_block_type, setting
 * the block's name, CSS file (as render_template), and JS file
 * (as enqueue_script).
 *
 * @return void
 *   Registers each block using acf_register_block_type.
 */
function register_acf_blocks() {
  if (class_exists('ACF')) {
    if (function_exists('register_block_type')) {
      $block_files = get_block_files();

      foreach ($block_files as $block) {
        register_block_type(__DIR__ . '/blocks/' . $block['name']);

        // If css exists attach it.
        if (isset($block['css'])) {
          wp_register_style($block['name'], get_stylesheet_directory_uri() . '/blocks/' . $block['css']);
        }

        // If js exists attach it.
        if (isset($block['js'])) {
          wp_enqueue_script($block['name'], get_stylesheet_directory_uri() . '/blocks/' . $block['js']['src'], $block['js']['deps'], $block['js']['ver'], $block['js']['in_footer']);
        }
      }
    }
  }
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
	unset($blocks['core/post-comments']);

	// Return the new list of allowed blocks.
	return array_keys($blocks);
}

/**
 *  Allowing control over the archive query posts_per_page based on ACF Options fields
 */
// add_action( 'pre_get_posts', 'dynamic_resource_per_page_query_parameter' );
// function dynamic_resource_per_page_query_parameter( $query ) {
// 	if( !is_admin() && (is_post_type_archive('research')) && $query->is_main_query() ) {
// 		$research_fields = get_field('research_archive', 'option');
// 		$research_per_page = ($research_fields && $research_fields['research_posts_per_page']) ? $research_fields['research_posts_per_page'] : 8;
// 		$query->query_vars['posts_per_page'] = $research_per_page;
// 	}

// 	if( !is_admin() && (is_post_type_archive('message')) && $query->is_main_query() ) {
// 		$message_fields = get_field('message_lab_archive', 'option');
// 		$message_per_page = ($message_fields && $message_fields['message_posts_per_page']) ? $message_fields['message_posts_per_page'] : 8;
// 		$query->query_vars['posts_per_page'] = $message_per_page;
// 	}
// }
