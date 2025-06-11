<?php
/**
 * Aten Hybrid functions and definitions.
 *
 * @file
 * Aten Hybrid functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Aten Hybrid
 * @since Twenty Twenty One 1.0
 */

/**
 * Enqueues the parent theme styles.
 *
 * This function is responsible for enqueueing the parent theme's style.css file.
 * It is hooked to the 'wp_enqueue_scripts' action, which is fired on the front-end
 * when scripts and styles are enqueued.
 */
function enqueue_parent_theme_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_parent_theme_styles' );

add_theme_support( 'post-thumbnails' );

/**
 * Activates custom logo support for the theme.
 *
 * This function adds support for custom logos in the theme. It sets the default values for the logo height, width, and other options.
 *
 * @since 1.0.0
 *
 * @return void
 */
function aten_custom_logo_activation() {
	$defaults = array(
		'height'               => 100,
		'width'                => 400,
		'flex-height'          => true,
		'flex-width'           => true,
		'header-text'          => array( 'site-title', 'site-description' ),
		'unlink-homepage-logo' => true,
	);
	add_theme_support( 'custom-logo', $defaults );
}
add_action( 'after_setup_theme', 'aten_custom_logo_activation' );

/**
 * Enqueues the child theme styles.
 *
 * This function is responsible for enqueueing the child theme's style.css file.
 * It is hooked to the 'wp_enqueue_scripts' action, which is fired on the front-end
 * when scripts and styles are enqueued.
 */
function aten_hybrid_enqueue_styles() {
	// Dequeue the Twenty Twenty-One parent style.
	wp_dequeue_style( 'twenty-twenty-one-style' );

	// Enqueue the child theme style.
	wp_enqueue_style( 'child-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

	// Add external styles for theme fonts and icons.
	wp_enqueue_style( 'theme-fonts', '//use.typekit.net/ggd1wwe.css', array() );
	wp_enqueue_style( 'theme-icons-outline', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined', array() );
}
add_action( 'wp_enqueue_scripts', 'aten_hybrid_enqueue_styles', 11 );



/**
 * Enables or disables various theme support settings.
 *
 * This function adds or removes theme support settings such as disabling custom font sizes,
 * disabling custom colors, disabling custom gradients, disabling layout styles, and removing
 * core block patterns.
 *
 * @return void
 */
function aten_hybrid_theme_support_settings() {
	add_theme_support( 'disable-custom-font-sizes' );
	add_theme_support( 'disable-custom-colors' );
	add_theme_support( 'disable-custom-gradients' );
	add_theme_support( 'disable-layout-styles' );
	remove_theme_support( 'core-block-patterns' );
}
add_action( 'after_setup_theme', 'aten_hybrid_theme_support_settings' );


/**
 * Adds editor styles to the theme.
 *
 * This function adds support for editor styles and sets the path to the editor-style.css file.
 * The editor-style.css file is used to style the content in the WordPress editor.
 *
 * @since 1.0.0
 */
function wpdocs_add_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'editor-style.css' );
}
add_action( 'admin_init', 'wpdocs_add_editor_styles' );


add_action(
	'enqueue_block_editor_assets',
	function () {
		// Remove editor style resets.
		wp_deregister_style( 'wp-reset-editor-styles' );

		// Replace with our own version (WP has dependency on this slug).
		wp_enqueue_style(
			'wp-reset-editor-styles',
			get_theme_file_uri( 'editor-style.css' ),
			array( 'common', 'forms' )
		);
	},
	102
);

/**
 * Adds the 'aten-hybrid' class to the body wrapper classes.
 *
 * @param array $classes The array of body wrapper classes.
 * @return array The modified array of body wrapper classes.
 */
function aten_hybrid_body_wrapper_class( $classes ) {
	$classes[] = 'aten-hybrid';
	return $classes;
}
add_filter( 'body_class', 'aten_hybrid_body_wrapper_class' );

/**
 * Register widget area.
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
add_filter(
	'render_block',
	function ( $block_content, $block ) {
		if ( 'core/heading' === $block['blockName'] ) {
			$block_content = str_replace( '<h1', '<h2', $block_content );
			$block_content = str_replace( '</h1', '</h2', $block_content );
		}

		return $block_content;
	},
	10,
	2
);

/*
* Removing H1 from all TinyMCE text editors (WYSIWYG)
*/
add_filter(
	'tiny_mce_before_init',
	function ( $settings ) {
		$settings['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6';
		return $settings;
	}
);

/**
 * Enqueues the block toolbar settings script.
 *
 * This function enqueues the block-toolbar-settings.js script, which is used to add
 * custom settings to the block toolbar.
 */
function control_block_toolbar_settings() {

	wp_enqueue_script(
		'control-block-toolbar-settings',
		get_stylesheet_directory_uri() . '/dist/js/editor/block-toolbar-settings.js',
		array( 'wp-block-editor', 'wp-blocks', 'wp-dom' ),
		time(),
		true
	);
}
add_action( 'enqueue_block_assets', 'control_block_toolbar_settings' );

/**
 * Add support for block template parts in hybrid theme.
 */
function aten_block_template_part_support() {
	add_theme_support( 'block-template-parts' );
}

add_action( 'after_setup_theme', 'aten_block_template_part_support' );


/**
 * Adjusting the path for the ACF Icon Selector to use the Material Font Icons inside of /assets/icons/acf-icons
 */

/**
 * Sets the path suffix for ACF icons.
 *
 * This function is used to set the path suffix for ACF (Advanced Custom Fields) icons.
 * It takes a string parameter representing the path suffix and returns the modified path suffix.
 *
 * @param string $path_suffix The path suffix for ACF icons.
 * @return string The modified path suffix.
 */
function acf_icon_path_suffix( $path_suffix ) {
	return 'assets/icons/acf-icons/';
}
add_filter( 'acf_icon_path_suffix', 'acf_icon_path_suffix' );

/**
 * Returns the path to the ACF icon directory.
 *
 * @param string $path_suffix Optional suffix to append to the path.
 * @return string The path to the ACF icon directory.
 */
function acf_icon_path( $path_suffix ) {
	return plugin_dir_path( __FILE__ );
}
add_filter( 'acf_icon_path', 'acf_icon_path' );

/**
 * Returns the URL of the ACF icon based on the given path suffix.
 *
 * @param string $path_suffix The suffix to be appended to the ACF icon URL.
 * @return string The URL of the ACF icon.
 */
function acf_icon_url( $path_suffix ) {
	return plugin_dir_url( __FILE__ );
}
add_filter( 'acf_icon_url', 'acf_icon_url' );

/**
 * Register custom blocks for Aten Hybrid theme.
 */
add_action( 'init', 'register_acf_blocks' );

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
	$dirs = glob( __DIR__ . '/blocks/*', GLOB_ONLYDIR );

	$block_files = array();

	foreach ( $dirs as $dir ) {
		$block_name  = pathinfo( $dir, PATHINFO_BASENAME );
		$css_file    = "{$block_name}.css";
		$js_file     = "{$block_name}.js";
		$config_file = "{$block_name}.config.json";

		// Setting block name.
		$block = array( 'name' => $block_name );

		// If css file exists, add to the block array.
		if ( file_exists( __DIR__ . "/blocks/{$block_name}/{$css_file}" ) ) {
			$block['css'] = "/{$block_name}/{$css_file}";
		}
		// If JS file exists, add to the block array.
		// @todo Update attachment to provide dependencies and load requirements.
		if ( file_exists( __DIR__ . "/blocks/{$block_name}/{$js_file}" ) ) {
			$block['js']['src']       = "/{$block_name}/{$js_file}";
			$block['js']['deps']      = array( 'jquery', 'utility-functions' );
			$block['js']['ver']       = '1.0';
			$block['js']['in_footer'] = true;
		}

		// If config file exists, add to the block array.
		if ( file_exists( __DIR__ . "/blocks/{$block_name}/{$config_file}" ) ) {
			$json_data       = file_get_contents( __DIR__ . "/blocks/{$block_name}/{$config_file}" );
			$block_config    = json_decode( $json_data, true );
			$block['config'] = $block_config;
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
 * the block's name, CSS file (as wp_register_style), and JS file
 * (as wp_register_script).
 *
 * @return void
 *   Registers each block using acf_register_block_type.
 */
function register_acf_blocks() {
	if ( class_exists( 'ACF' ) ) {
		if ( function_exists( 'register_block_type' ) ) {
			$block_files = get_block_files();

			foreach ( $block_files as $block ) {
				register_block_type( __DIR__ . '/blocks/' . $block['name'] );

				// If css exists attach it.
				if ( isset( $block['css'] ) ) {
					wp_register_style( $block['name'], get_stylesheet_directory_uri() . '/blocks/' . $block['css'] );
				}

				// If js exists attach it.
				if ( isset( $block['js'] ) ) {
					wp_register_script( $block['name'], get_stylesheet_directory_uri() . '/blocks/' . $block['js']['src'], $block['js']['deps'], $block['js']['ver'], true );
				}

				// If config exists register nested stylesheets.
				if ( isset( $block['config'] ) && isset( $block['config']['nested_blocks'] ) && $block['config']['nested_blocks'] ) {
					foreach ( $block['config']['nested_blocks'] as $nested_block ) {
						if ( file_exists( get_stylesheet_directory_uri() . "/blocks/{$nested_block}/{$nested_block}.css" ) ) {
							wp_register_style( $nested_block, get_stylesheet_directory_uri() . "/blocks/{$nested_block}/{$nested_block}.css" );
						}
					}
				}
			}
		}
	}
}

/**
 * Enqueues block assets at runtime.
 *
 * This function uses the get_block_files function to retrieve an array
 * of block files.
 * It then loops through each block, checking if the block is present in the
 * content. If it is, the block's CSS and JS files are enqueued.
 *
 * @param string $content The content of the post.
 * @return string The content of the post.
 */
function aten_enqueue_block_assets_at_runtime( $content = '' ) {
	// Get all custom blocks.
	$block_files = get_block_files();

	// List any blocks programmatically rendered outside the content loop.
	$static_blocks = array(
		'project-list-block',
	);

	// Loop through each custom block.
	foreach ( $block_files as $block ) {
		if ( in_array( $block['name'], $static_blocks, true ) ) {
			// Enqueue CSS.
			if ( isset( $block['css'] ) ) {
				wp_enqueue_style( $block['name'] );
			}
			// Enqueue JS.
			if ( isset( $block['js'] ) ) {
				wp_enqueue_script( $block['name'] );
			}
		} elseif ( has_block( 'acf/' . $block['name'] ) ) {
			// Enqueue CSS.
			if ( isset( $block['css'] ) ) {
				wp_enqueue_style( $block['name'] );
			}
			// Enqueue JS.
			if ( isset( $block['js'] ) ) {
				wp_enqueue_script( $block['name'] );
			}
		}

		if ( isset( $block['config'] ) && isset( $block['config']['nested_blocks'] ) && $block['config']['nested_blocks'] ) {
			foreach ( $block['config']['nested_blocks'] as $nested_block ) {
				wp_enqueue_style( $nested_block );
			}
		}
	}

	return $content;
}
add_filter( 'the_content', 'aten_enqueue_block_assets_at_runtime' );

/**
 * Disables specific blocks from the block editor.
 *
 * This function disables specific blocks from the block editor by removing them
 * from the list of allowed block types.
 *
 * @param array $allowed_blocks The list of allowed block types.
 * @return array The modified list of allowed block types.
 */
function aten_hybrid_blacklist_blocks( $allowed_blocks ) {
	// Get all the registered blocks.
	$blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

	// Then disable some of them.
	unset( $blocks['core/pullquote'] );
	unset( $blocks['core/quote'] );
	unset( $blocks['core/separator'] );

	// Return the new list of allowed blocks.
	return array_keys( $blocks );
}
add_filter( 'allowed_block_types_all', 'aten_hybrid_blacklist_blocks' );

/**
 * Retrieves all JavaScript files from the blocks src directories.
 *
 * This function uses the glob function to get an array of all .js files in
 * the blocks src directories and libraries/js directories.
 * It then loops over these files, creating an array of script details for
 * each one to be enqueued by enqueue_custom_scripts.
 * The handle for each script is the file name without the extension, and the
 * source is the file's URL relative to the theme directory.
 *
 * @return array
 *   An array of scripts, where each script is an associative array with keys
 *   for the handle, source, dependencies, version, if to load in the footer.
 */
function get_global_js_files() {
	// Get all JS files in blocks/*/src.
	$files = array_merge( glob( __DIR__ . '/dist/js/**/*.js' ) );

	$scripts = array();
	foreach ( $files as $file ) {
		// Get the file name without the extension as $name.
		$name     = basename( $file, '.js' );
		$file_src = str_replace( 'app/', '', $file );

		$scripts[] = array(
			'name'      => $name,
			'src'       => $file_src,
			'deps'      => array( 'jquery', 'wp-block-editor', 'utility-functions' ),
			'ver'       => '1.0',
			'in_footer' => true,
		);
	}

	return $scripts;
}

/**
 * Enqueues custom scripts for the theme.
 *
 * This function retrieves all JavaScript files from the blocks src directories
 * using the get_all_js_files function.
 * It then loops over these scripts & enqueues each one using wp_enqueue_script.
 * The handle, source, dependencies, version, and footer loading preference
 * for each script are determined by the get_all_js_files function.
 *
 * This function is hooked into the wp_enqueue_scripts action.
 *
 * @see get_all_js_files
 * @see wp_enqueue_script
 */
function enqueue_custom_scripts() {
	wp_enqueue_script( 'utility-functions', get_stylesheet_directory_uri() . '/dist/js/editor/utility-functions.js', array( 'jquery' ), '2.4.16', true );

	$scripts = get_global_js_files();

	foreach ( $scripts as $script ) {
		wp_enqueue_script(
			$script['name'],
			$script['src'],
			$script['deps'],
			$script['ver'],
			$script['in_footer']
		);
	}
}

add_action( 'wp_enqueue_scripts', 'enqueue_custom_scripts' );


/**
 * Enqueues Splide.js scripts and styles.
 */
function enqueue_splide_scripts() {
	// Enqueue Splide.js CSS.
	wp_enqueue_style( 'splide-css', get_stylesheet_directory_uri() . '/vendor/splide/splide.min.css' );

	// Enqueue Splide.js JavaScript.
	wp_enqueue_script( 'splide-js', get_stylesheet_directory_uri() . '/vendor/splide/splide.min.js', array( 'jquery' ), '2.4.16', true );
}

add_action( 'wp_enqueue_scripts', 'enqueue_splide_scripts' );


/**
 * Disables specific blocks in the WordPress editor.
 *
 * This function removes unnecessary WP Core blocks from the list of allowed blocks
 * across all post types. It retrieves all the registered blocks and unsets the
 * ones that are not needed.
 *
 * @param array $allowed_blocks The list of allowed blocks.
 * @return array The new list of allowed blocks.
 */
function disable_specific_blocks( $allowed_blocks ) {
	// Get current post ID.
	$current_id = get_the_ID();
	// Get all the registered blocks.
	$blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
	// Removing all WP Core blocks that are unnecessary across all post types.
	unset( $blocks['core/legacy-widget'] );
	unset( $blocks['core/widget-group'] );
	unset( $blocks['core/archives'] );
	unset( $blocks['core/avatar'] );
	unset( $blocks['core/calendar'] );
	unset( $blocks['core/categories'] );
	unset( $blocks['core/comment-author-name'] );
	unset( $blocks['core/comment-content'] );
	unset( $blocks['core/comment-date'] );
	unset( $blocks['core/comment-edit-link'] );
	unset( $blocks['core/comment-reply-link'] );
	unset( $blocks['core/comment-template'] );
	unset( $blocks['core/comments'] );
	unset( $blocks['core/comments-pagination'] );
	unset( $blocks['core/comments-pagination-next'] );
	unset( $blocks['core/comments-pagination-numbers'] );
	unset( $blocks['core/comments-pagination-previous'] );
	unset( $blocks['core/comments-title'] );
	unset( $blocks['core/cover'] );
	unset( $blocks['core/home-link'] );
	unset( $blocks['core/latest-comments'] );
	unset( $blocks['core/latest-posts'] );
	unset( $blocks['core/loginout'] );
	unset( $blocks['core/navigation'] );
	unset( $blocks['core/navigation-link'] );
	unset( $blocks['core/navigation-submenu'] );
	unset( $blocks['core/page-list'] );
	unset( $blocks['core/post-author'] );
	unset( $blocks['core/post-author-biography'] );
	unset( $blocks['core/post-author-name'] );
	unset( $blocks['core/post-comments-form'] );
	unset( $blocks['core/post-navigation-link'] );
	unset( $blocks['core/post-template'] );
	unset( $blocks['core/post-terms'] );
	unset( $blocks['core/pullquote'] );
	unset( $blocks['core/query'] );
	unset( $blocks['core/query-no-results'] );
	unset( $blocks['core/query-pagination'] );
	unset( $blocks['core/query-pagination-next'] );
	unset( $blocks['core/query-pagination-numbers'] );
	unset( $blocks['core/query-pagination-previous'] );
	unset( $blocks['core/query-title'] );
	unset( $blocks['core/read-more'] );
	unset( $blocks['core/rss'] );
	unset( $blocks['core/site-logo'] );
	unset( $blocks['core/site-tagline'] );
	unset( $blocks['core/site-title'] );
	unset( $blocks['core/tag-cloud'] );
	unset( $blocks['core/template-part'] );
	unset( $blocks['core/term-description'] );
	unset( $blocks['core/audio'] );
	unset( $blocks['core/column'] );
	unset( $blocks['core/columns'] );
	unset( $blocks['core/freeform'] );
	unset( $blocks['core/group'] );
	unset( $blocks['core/gallery'] );
	unset( $blocks['core/media-text'] );
	unset( $blocks['core/more'] );
	unset( $blocks['core/nextpage'] );
	unset( $blocks['core/preformatted'] );
	unset( $blocks['core/quote'] );
	unset( $blocks['core/text-columns'] );
	unset( $blocks['core/verse'] );
	unset( $blocks['core/post-comments'] );

	// Return the new list of allowed blocks.
	return array_keys( $blocks );
}
add_filter( 'allowed_block_types_all', 'disable_specific_blocks', 10, 2 );

/**
 * Displays the posts navigation.
 *
 * This function outputs the pagination links for navigating between pages of posts.
 *
 * @since 1.0.0
 */
function aten_the_posts_navigation() {
	the_posts_pagination(
		array(
			'before_page_number' => esc_html__( 'Page', 'twentytwentyone' ) . ' ',
			'mid_size'           => 0,
			'prev_text'          => sprintf(
				'%s <span class="nav-prev-text">%s</span>',
				is_rtl() ? 'arrow_right' : 'arrow_left',
				wp_kses(
					__( 'Newer <span class="nav-short">posts</span>', 'twentytwentyone' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				)
			),
			'next_text'          => sprintf(
				'<span class="nav-next-text">%s</span> %s',
				wp_kses(
					__( 'Older <span class="nav-short">posts</span>', 'twentytwentyone' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				is_rtl() ? 'arrow_left' : 'arrow_right'
			),
		)
	);
}

/**
 * Removing the 'sizes="auto"' tag from WP generated images.
 * This is a fix to the bug introduced in WP Core 6.7 that causes images to distort without explicit width and height attributes.
 */
add_filter(
	'wp_content_img_tag',
	static function ( $image ) {
			return str_replace( 'sizes="auto, ', 'sizes="', $image );
	}
);
add_filter(
	'wp_get_attachment_image_attributes',
	static function ( $attr ) {
		if ( isset( $attr['sizes'] ) ) {
				$attr['sizes'] = preg_replace( '/^auto, /', '', $attr['sizes'] );
		}
			return $attr;
	}
);

/**
 * Enables support for classic menus and widgets.
 *
 * This function adds theme support for traditional WordPress menus and widgets,
 * allowing users to manage navigation menus and widgets through the Appearance menu.
 *
 * @return void
 */
function aten_enable_classic_menu_widget_support() {
	add_theme_support( 'menus' );
	add_theme_support( 'widgets' );
}
add_action( 'after_setup_theme', 'aten_enable_classic_menu_widget_support' );

/**
 * Sets the media URL path to 'wp-content/uploads/{filename}'.
 *
 * This function disables year/month folders for uploads.
 */
add_filter(
	'pre_option_uploads_use_yearmonth_folders',
	function () {
		return '0';
	},
	9999
);