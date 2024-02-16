<?php

/**
 * @file
 * Aten FSE functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Aten FSE
 * @since Twenty Twenty-Three 1.1
 */

require_once __DIR__ . '/includes/footer-elements.php';

/**
 * These includes have been commented out as we are importing Post Types into the UI after they're created
 * these files will include the back up PHP for Custom Post Types / Advanced Custom Fields / Custom Taxonomies
 */
require_once __DIR__ . '/includes/custom-post-type-default-content/news-default-content.php';
require_once __DIR__ . '/includes/custom-post-type-default-content/location-default-content.php';

/**
 * Adding support for custom editor styles.
 */
add_theme_support('editor-styles');
add_editor_style( get_stylesheet_directory_uri() . '/editor-style.css' );

add_action('wp_enqueue_scripts', 'aten_fse_enqueue_styles');

// Adding inline styles to WP Admin header to apply them without the default 'editor-style-wrapper' prefix from Gutenberg
// This is necessary to style the Add Block button, as that lives outside the default Gutenberg style wrapper
function editor_style_add_block_btn() {
  echo '<style>
  .block-editor-default-block-appender,
  .block-editor-block-list__empty-block-inserter {
    text-align: center !important;
  }
  .block-editor-default-block-appender .block-editor-inserter,
  .block-editor-block-list__empty-block-inserter .block-editor-inserter {
    position: relative !important;
    margin: 1rem auto !important;
  }
  .block-editor-default-block-appender .block-editor-inserter .block-editor-default-block-appender__content,
  .block-editor-block-list__empty-block-inserter .block-editor-inserter .block-editor-default-block-appender__content {
    position: absolute !important;
  }
  
  .block-editor-block-list__empty-block-inserter {
    left: 50%;
    right: auto;
    -webkit-transform: translate(-50%, 0);
    -ms-transform: translate(-50%, 0);
    transform: translate(-50%, 0);
  }
  
  .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon,
  .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon,
  .block-editor-block-list__block .block-list-appender .block-editor-inserter__toggle.components-button.has-icon,
  .block-editor-inserter__toggle.components-button.has-icon,
  .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon, .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon {
    background-color: #1C3F94 !important;
    border: none;
    border-radius: 0.25rem;
    color: #FFFFFF !important;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: reverse;
    -ms-flex-direction: row-reverse;
    flex-direction: row-reverse;
    -webkit-box-pack: justify;
    -ms-flex-pack: justify;
    justify-content: space-between;
    font-family: "Azo sans", sans-serif;
    font-size: 1.125rem;
    font-weight: bold;
    line-height: 156%;
    padding: 0.5rem 1rem !important;
    text-align: left;
    text-decoration: none;
    -webkit-transition-property: all;
    -o-transition-property: all;
    transition-property: all;
    -webkit-transition-duration: 150ms;
    -o-transition-duration: 150ms;
    transition-duration: 150ms;
    -webkit-transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    -o-transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    width: -webkit-fit-content;
    width: -moz-fit-content;
    width: fit-content;
    height: auto !important;
    text-indent: 0;
    position: relative !important;
  }
  .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon:after,
  .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon:after,
  .block-editor-block-list__block .block-list-appender .block-editor-inserter__toggle.components-button.has-icon:after,
  .block-editor-inserter__toggle.components-button.has-icon:after,
  .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon:after, .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon:after {
    content: "Add Block";
    display: inline-block;
  }
  .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon:hover, .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon:focus,
  .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon:hover,
  .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon:focus,
  .block-editor-block-list__block .block-list-appender .block-editor-inserter__toggle.components-button.has-icon:hover,
  .block-editor-block-list__block .block-list-appender .block-editor-inserter__toggle.components-button.has-icon:focus,
  .block-editor-inserter__toggle.components-button.has-icon:hover,
  .block-editor-inserter__toggle.components-button.has-icon:focus,
  .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon:hover,
  .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon:focus, .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon:hover, .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon:focus {
    background-color: #002176 !important;
    text-decoration: underline;
    -webkit-transition-property: all;
    -o-transition-property: all;
    transition-property: all;
    -webkit-transition-duration: 150ms;
    -o-transition-duration: 150ms;
    transition-duration: 150ms;
    -webkit-transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    -o-transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  }
  .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon svg,
  .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon svg,
  .block-editor-block-list__block .block-list-appender .block-editor-inserter__toggle.components-button.has-icon svg,
  .block-editor-inserter__toggle.components-button.has-icon svg,
  .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon svg, .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon svg {
    border: 0.125rem solid #FFFFFF;
    border-radius: 100%;
    margin-left: 0.5rem;
    height: 1.125rem;
    width: 1.125rem;
    display: inline-block;
  }
  .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon svg path,
  .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon svg path,
  .block-editor-block-list__block .block-list-appender .block-editor-inserter__toggle.components-button.has-icon svg path,
  .block-editor-inserter__toggle.components-button.has-icon svg path,
  .block-editor-block-list__empty-block-inserter .block-editor-inserter__toggle.components-button.has-icon svg path, .block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle.components-button.has-icon svg path {
    stroke: #FFFFFF;
  }
  </style>
  ';
}
add_action('admin_head', 'editor_style_add_block_btn');

/**
 * Add custom styles to site.
 */
function aten_fse_enqueue_styles()
{
  wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', []);

  // Add external styles.
  wp_enqueue_styles('theme-icon-default', 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap', []);
  wp_enqueue_style('theme-icons-outline', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined', []);
}

function admin_style() {
  wp_enqueue_style('admin-styles', get_stylesheet_directory_uri() . '/admin-styles.css');
}
add_action('admin_enqueue_scripts', 'admin_style');

/**
 *
 */
function aten_fse_menus_init()
{
  register_nav_menus([
    'primary' => esc_html__('Primary Menu', 'aten-fse'),
    'main-nav' => esc_html__('Main Navigation Menu', 'aten-fse'),
    'footer'  => esc_html__('Footer Menu', 'aten-fse'),
  ]);
}

add_action('after_setup_theme', 'aten_fse_menus_init');

/**
 * Pretty Printing.
 *
 * @since 1.0.0
 * @author Chris Bratlien
 * @param mixed $obj
 * @param string $label
 *
 * @return null
 */
function ea_pp($obj, $label = '')
{
  $data = json_encode(print_r($obj, TRUE));
?>
  <style type="text/css">
    #bsdLogger {
      position: absolute;
      top: 30px;
      right: 0px;
      border-left: 4px solid #bbb;
      padding: 6px;
      background: white;
      color: #444;
      z-index: 999;
      font-size: 1.25em;
      width: 400px;
      height: 800px;
      overflow: scroll;
    }
  </style>
  <script type="text/javascript">
    var doStuff = function() {
      var obj = <?php echo $data; ?>;
      var logger = document.getElementById('bsdLogger');
      if (!logger) {
        logger = document.createElement('div');
        logger.id = 'bsdLogger';
        document.body.appendChild(logger);
      }
      ////console.log(obj);
      var pre = document.createElement('pre');
      var h2 = document.createElement('h2');
      pre.innerHTML = obj;
      h2.innerHTML = '<?php echo addslashes($label); ?>';
      logger.appendChild(h2);
      logger.appendChild(pre);
    };
    window.addEventListener("DOMContentLoaded", doStuff, false);
  </script>
<?php
}

/**
 * Register custom header for Aten FSE theme.
 */
function aten_fse_custom_header()
{
  register_default_headers([
    'custom' => [
      'url'           => get_stylesheet_directory_uri() . '/images/header-custom.jpg',
      'thumbnail_url' => get_stylesheet_directory_uri() . '/images/header-custom-thumbnail.jpg',
      'description'   => __('Custom Header', 'aten-fse'),
    ],
  ]);
}

add_action('after_setup_theme', 'aten_fse_custom_header');

/**
 * Register custom blocks for Aten FSE theme.
 */
add_action('init', 'register_acf_blocks');
function register_acf_blocks()
{
  register_block_type(__DIR__ . '/blocks/all-services-block');
  register_block_type(__DIR__ . '/blocks/accent-text-block');
  register_block_type(__DIR__ . '/blocks/accordion-block');
  register_block_type(__DIR__ . '/blocks/callout');
  register_block_type(__DIR__ . '/blocks/callout-block');
  register_block_type(__DIR__ . '/blocks/column-block');
  register_block_type(__DIR__ . '/blocks/contact-info');
  register_block_type(__DIR__ . '/blocks/event-location');
  register_block_type(__DIR__ . '/blocks/featured-link-cards');
  register_block_type(__DIR__ . '/blocks/featured-link-section');
  register_block_type(__DIR__ . '/blocks/homepage-hero');
  register_block_type(__DIR__ . '/blocks/information-header');
  register_block_type(__DIR__ . '/blocks/inline-contact-block');
  register_block_type(__DIR__ . '/blocks/jump-link-block');
  register_block_type(__DIR__ . '/blocks/locations-block');
  register_block_type(__DIR__ . '/blocks/news-meta-fields');
  register_block_type(__DIR__ . '/blocks/news-release-block');
  register_block_type(__DIR__ . '/blocks/page-header');
  register_block_type(__DIR__ . '/blocks/progress-block');
  register_block_type(__DIR__ . '/blocks/pullquote');
  register_block_type(__DIR__ . '/blocks/resource-block');
  register_block_type(__DIR__ . '/blocks/rotating-banner');
  register_block_type(__DIR__ . '/blocks/separator');
  register_block_type(__DIR__ . '/blocks/services-list-block');
}

/*
 * Blacklist specific Gutenberg blocks
 */
add_filter('allowed_block_types_all', 'aten_fse_blacklist_blocks');

/**
 *
 */
function aten_fse_blacklist_blocks($allowed_blocks)
{
  // Get all the registered blocks.
  $blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

  // Then disable some of them.
  unset($blocks['core/pullquote']);
  unset($blocks['core/quote']);
  unset($blocks['core/separator']);

  // Return the new list of allowed blocks.
  return array_keys($blocks);
}

/**
 * Adding custom image sizes for custom blocks
 */
add_image_size( 'callout-link', 400, 400 );
add_image_size( 'location-image', 300, 240 );

/**
 * @return void
 */
function enqueue_splide_scripts() {
	// Enqueue Splide.js CSS
	wp_enqueue_style( 'splide-css', get_stylesheet_directory_uri() . '/styles/splide/splide.min.css' );

	// Enqueue Splide.js JavaScript
	wp_enqueue_script( 'splide-js', get_stylesheet_directory_uri() . '/styles/splide/splide.min.js', array( 'jquery' ), '2.4.16', true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_splide_scripts' );

/**
 * @return void
 */
function enqueue_custom_scripts() {
	// Enqueue your custom JavaScript file
  wp_enqueue_script( 'utility-functions', get_stylesheet_directory_uri() . '/js/utility-functions.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'accordion-block', get_stylesheet_directory_uri( ) . '/js/accordion-block.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'category-cards', get_stylesheet_directory_uri() . '/js/category-cards.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri() . '/styles/splide/splideshow.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'events', get_stylesheet_directory_uri() . '/js/events.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'featured-link-cards', get_stylesheet_directory_uri() . '/js/featured-link-cards.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'featured-link-section', get_stylesheet_directory_uri( ) . '/js/featured-link-section.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'forms', get_stylesheet_directory_uri() . '/js/forms.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'header-nav', get_stylesheet_directory_uri() . '/js/header-nav.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'jump-link-functionality', get_stylesheet_directory_uri( ) . '/js/jump-link-functionality.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'location-block', get_stylesheet_directory_uri() . '/js/location-block.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'notification-bar', get_stylesheet_directory_uri() . '/js/notification-bar.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'pagination', get_stylesheet_directory_uri( ) . '/js/pagination.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'search', get_stylesheet_directory_uri( ) . '/js/search.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'services-blocks', get_stylesheet_directory_uri( ) . '/js/services-blocks.js', array( 'jquery' ), '1.0', true );
  wp_enqueue_script( 'sidebar-menu', get_stylesheet_directory_uri( ) . '/js/sidebar-menu.js', array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_scripts' );

/*
 * Control what options appear in the backend editor block toolbars
 */
function remove_block_toolbar_settings () {

	wp_enqueue_script(
		'remove-block-toolbar-settings',
		get_stylesheet_directory_uri( ) . '/js/block-toolbar-settings.js',
		array( 'wp-block-editor' ),
	);
}

add_action('enqueue_block_assets', 'remove_block_toolbar_settings');

/*
 * Control what options appear in the backend editor block toolbars
 */
function custom_news_query_loop_variation () {

	wp_enqueue_script(
		'query-loop-variations',
		get_stylesheet_directory_uri( ) . '/js/query-loop-variations.js',
		array( 'wp-block-editor' ),
	);
}

add_action('enqueue_block_assets', 'custom_news_query_loop_variation');

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
* Adding support for pages to have categories
*/
function add_categories_to_pages() {
  register_taxonomy_for_object_type('category', 'page');
}
add_action( 'init', 'add_categories_to_pages' );

/*
 * Adding icon font to backend editor for button arrows to appear properly in editor
 */
function adding_icon_font_to_block_editor () {

  wp_enqueue_style( 
    'adding-icon-font-to-block-editor', 
    'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined',
    array( 'wp-block-editor' ), null 
  );

}

add_action('enqueue_block_assets', 'adding_icon_font_to_block_editor');

/**
 * Validates the phone number field in the contact info ACF group as a numeric value
 */
function validate_text_as_number($valid, $value, $field, $input) {
  if ($valid !== true) {
    return $valid;
  }
  if (preg_match('/[^0-9]/', $value)) {
    $valid = 'Please enter a valid number';
  }
  return $valid;
}
add_filter('acf/validate_value/name=phone_number', 'validate_text_as_number', 20, 4);

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
 * Creating a shortcode to embed the SVG version of the site logo
 */
function display_aten_fse_site_logo() {
  $logo_path = get_template_directory_uri() . '/assets/logo.svg';
  ob_start(); ?>
  <div class="site-logo">
    <a href="<?php echo get_home_url(); ?>" title="Homepage"><img src="<?php echo $logo_path; ?>" title="Home" alt="Aten Logo" /></a>
  </div>
  <?php 
  return ob_get_clean();
}

add_shortcode('aten_fse_site_logo', 'display_aten_fse_site_logo');

/**
 * Managing menu icons and menu item display through ACF fields
 */
add_filter('wp_nav_menu_objects', 'my_wp_nav_menu_objects', 10, 2);

function my_wp_nav_menu_objects( $items, $args ) {
    // Loop through each menu item
    foreach( $items as &$item ) {
        // Pulling ACF field values
        $icon = get_field('menu_item_icon', $item);
        $is_top_level = get_field('no_children', $item);
        $is_submenu_heading = get_field('submenu_heading', $item);
        $with_border = get_field('with_border', $item);
        
        // Append icon to the title of each menu item
        if( $icon ) {
          $item->title .= '<span class="menu-icon notranslate" aria-hidden="true">' . $icon . '</span>';
        }

        // Add class for top-level only items
        if($is_top_level) {
          $item->classes[] = 'no-children';
          $item->title .= ' <span class="menu-icon right notranslate" aria-hidden="true">chevron_right</span>';
        }

        // Add class for submenu heading items
        if($is_submenu_heading) {
          $item->classes[] = 'submenu-heading';

          // Add class for a bottom border on submenu heading items
          if($with_border) {
            $item->classes[] = 'with-border';
          }
        }
      }

    // Return menu items
    return $items;
}

/**
 * Set a default featured image for News posts
 */
function set_default_featured_image( $post_id, $post ) {
	// Check post type and see if the post has a featured image
	if ( !has_post_thumbnail( $post_id ) && get_post_type( $post_id ) === 'news' ) {
    // Assigning default image attachment ID
		$default_image_id = 72; 
		// Set featured image of the post to the default image
		set_post_thumbnail( $post_id, $default_image_id );
	}

	return $post_id;
}
add_action( 'save_post', 'set_default_featured_image', 10, 2 );

/* 
 *  Adjusting the WP Query Loop Block behavior for the extended News Query Block
 */
// Customizing the query used to render the block on the front-end
function custom_news_query_loop_pre_render( $pre_render, $parsed_block ) {
  // Verify that this only runs on our extended version of the query lop
  if ( !empty($parsed_block['attrs']['namespace']) && 'loop-patterns/news-query' === $parsed_block['attrs']['namespace'] ) {
    // Filter the query loop block args
    add_filter(
      'query_loop_block_query_vars',
      function( $query, $block ) {
        // Sorting by ACF field for most recently published -> oldest news post
        $query['meta_key'] = 'publication_date';
        $query['orderby'] = 'meta_value';
        $query['order'] = 'DESC';
        
        return $query;
      },
      10,
      2
    );
  }
  return $pre_render;
}
add_filter( 'pre_render_block', 'custom_news_query_loop_pre_render', 10, 2 );

// Customizing the query used to render the block on the back-end editor
function customizing_news_query_block( $args, $request ) {
  // Checking for date filter so this doesn't run on every news query sitewide
  $dateFilter = $request['filterByDate']; 
  // If our flag var is present, the query is being run by our custom query loop block
  if ( $dateFilter ) {
    // Update the query for the back end view
    $args['meta_key'] = 'publication_date';
    $args['orderby'] = 'meta_value';
    $args['order'] = 'DESC';
  }

  return $args;
}
add_filter( 'rest_news_query', 'customizing_news_query_block', 10, 2 );

/*
 * Rewriting Search slug to use /search/ base 
 */
function aten_fse_custom_search_rules( $rewrite ) {
  global $wp_rewrite;
  $rules = array(
    $wp_rewrite->search_base . '/?$' => '/search/?s=',
  );
  $rewrite = $rewrite + $rules;
  return $rewrite;
}
add_filter( 'search_rewrite_rules', 'aten_fse_custom_search_rules', 10, 1 );

function aten_fse_search_template_redirect() {
  global $wp_rewrite;
  // Check that there is a search term
  if ( is_search() && isset ( $_GET['s'] ) ) {
    // Append search term to the /search/ base URL
    $s         = sanitize_text_field( $_GET['s'] ); 
    $location  = '/';
    $location .= trailingslashit( $wp_rewrite->search_base );
    $location .= ( ! empty ( $s ) ) ? user_trailingslashit( urlencode( $s ) ) : urlencode( $s );
    $location  = home_url( $location );
    wp_safe_redirect( $location, 301 );

    exit;
  }
}
add_action( 'template_redirect', 'aten_fse_search_template_redirect' );

/** 
 * Shortcode to display count of results and search term on search results page
 */
function display_search_details() {
  // Setting up defaults
  $result_text = '';
  $search_term = get_search_query();

  // If a search term was submitted
  if($search_term) {
    // Get query details
    global $wp_query;
    if($wp_query->found_posts < 2) {
      $result_text = "Showing 1 result for";
    } else {
      $result_text = "Showing " . $wp_query->found_posts . " results for";
    }
  }
  ob_start();
    // Don't show anything if no search term exists
    if($search_term && ($wp_query->found_posts > 0)) {
      echo '<h2 class="search-results-count"><em>' . $result_text . ' </em><strong>' . $search_term . '</strong></h2>';
    }
  return ob_get_clean();
}

add_shortcode('search_details', 'display_search_details');

/* 
 * Adding the page slug to the body class for targeting global elements at the template-level
 */
function add_slug_body_class( $classes ) {
  global $post;
  if ( isset( $post ) ) {
    $classes[] = $post->post_type . '-' . $post->post_name;
  }
  return $classes;
}

add_filter( 'body_class', 'add_slug_body_class' );

/**
 * Trimming excerpts to the nearest full sentence, limit 3 sentences
 */

function adjust_excerpt_length( $length ) {
  if ( is_admin() ) {
          return $length;
  }
  // Setting it to 30 full words
  return 30;
}
add_filter( 'excerpt_length', 'adjust_excerpt_length', 999 );

function remove_read_more_ellipses( $more ) {
  return '';
}
add_filter('excerpt_more', 'remove_read_more_ellipses');

function trim_excerpt_to_full_sentence( $excerpt ) {
  $sentence_punctuation = array('.', '!', '?', '...');
  $number_sentences = 3;
  $excerpt_chunk = $excerpt;

  for($i = 0; $i < $number_sentences; $i++) {
      $lowest_sentence_end[$i] = 200;
      foreach( $sentence_punctuation as $end_punctuation) {
        $sentence_end = strpos( $excerpt_chunk, $end_punctuation);
        if($sentence_end !== false && $sentence_end < $lowest_sentence_end[$i]){
            $lowest_sentence_end[$i] = $sentence_end + strlen( $end_punctuation );
        }
        $sentence_end = false;
      }

      $sentences[$i] = substr( $excerpt_chunk, 0, $lowest_sentence_end[$i]);
      $excerpt_chunk = substr( $excerpt_chunk, $lowest_sentence_end[$i]);
  }

  return implode('', $sentences);
}
add_filter('get_the_excerpt', 'trim_excerpt_to_full_sentence');

/** 
 *  Removing unnecessary options from the Easy Notification Bar customizer
 */
function removing_unnecessary_notification_bar_options( $wp_customize ) {
	$wp_customize->remove_control('easy_nb_background_color');
  $wp_customize->remove_control('easy_nb_text_color');
  $wp_customize->remove_control('easy_nb_text_align');
  $wp_customize->remove_control('easy_nb_enable_system_font_family');
  $wp_customize->remove_control('easy_nb_font_size');
  $wp_customize->remove_control('easy_nb_button_background_color');
  $wp_customize->remove_control('easy_nb_button_text_color');
  $wp_customize->remove_control('easy_nb_button_padding');
  $wp_customize->remove_control('easy_nb_close_icon');
  $wp_customize->remove_control('easy_nb_is_sticky');
  $wp_customize->remove_control('easy_nb_button_text');
}
add_action( 'customize_register', 'removing_unnecessary_notification_bar_options' );

// Adding white bg to icon in editor
function changing_icon_bg_in_admin() {
  echo '<style>

  .edit-site-site-hub__view-mode-toggle-container {
      background-color: #fff;
      margin-right: 10px;
      border-radius: 0 0 4px 0;
  }

  .edit-post-header .components-button.edit-post-fullscreen-mode-close.has-icon {
    background-color: #fff;
    border-bottom: 1px solid rgb(224, 224, 224);
  }

  .edit-post-fullscreen-mode-close.components-button:before {
    box-shadow: none!important;
  }

  .edit-site-site-icon__image {
    background-color: #fff;
  }
  </style>';
}
add_action('admin_head', 'changing_icon_bg_in_admin');

// Disabling access to author archive pages
add_action('template_redirect', 'disable_direct_access_to_author_page');
function disable_direct_access_to_author_page() {
    global $wp_query;
    // If accessing an author archive page
    if ( is_author() ) {
        // Set author pages as a 404
        $wp_query->set_404();
        status_header(404);
        // Redirect to homepage
        wp_redirect(get_option('home'));
    }
}

/**
 * Disabling AJAX for all Gravity Forms
 */
add_filter('gform_form_args', 'no_ajax_on_all_forms', 10, 1);
function no_ajax_on_all_forms($args){
    $args['ajax'] = false;
    return $args;
}