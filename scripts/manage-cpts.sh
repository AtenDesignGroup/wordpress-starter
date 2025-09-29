#!/bin/bash

# WordPress Installation directory (root of the project)
INSTALL_DIR="."
export INSTALL_DIR

# Plugin directory and file
PLUGIN_DIR="$INSTALL_DIR/wp-content/plugins/wordpress-starter-custom-posttypes"
PLUGIN_FILE="$PLUGIN_DIR/wordpress-starter-custom-posttypes.php"

mkdir -p "$PLUGIN_DIR"

# Create the plugin file
cat > "$PLUGIN_FILE" <<'PHP'
<?php
/**
 * Plugin Name: WordPress Starter - Custom Post Types
 * Description: Registers custom post types for this WordPress project.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL2+
 * Text Domain: wordpress-starter-custom-posttypes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Register News CPT
function wpstarter_register_news_cpt() {
    $labels = array(
        'name'               => _x( 'News', 'post type general name', 'wordpress-starter-custom-posttypes' ),
        'singular_name'      => _x( 'News Item', 'post type singular name', 'wordpress-starter-custom-posttypes' ),
        'menu_name'          => _x( 'News', 'admin menu', 'wordpress-starter-custom-posttypes' ),
        'name_admin_bar'     => _x( 'News Item', 'add new on admin bar', 'wordpress-starter-custom-posttypes' ),
        'add_new'            => _x( 'Add New', 'news', 'wordpress-starter-custom-posttypes' ),
        'add_new_item'       => __( 'Add New News Item', 'wordpress-starter-custom-posttypes' ),
        'new_item'           => __( 'New News Item', 'wordpress-starter-custom-posttypes' ),
        'edit_item'          => __( 'Edit News Item', 'wordpress-starter-custom-posttypes' ),
        'view_item'          => __( 'View News Item', 'wordpress-starter-custom-posttypes' ),
        'all_items'          => __( 'All News', 'wordpress-starter-custom-posttypes' ),
        'search_items'       => __( 'Search News', 'wordpress-starter-custom-posttypes' ),
        'parent_item_colon'  => __( 'Parent News:', 'wordpress-starter-custom-posttypes' ),
        'not_found'          => __( 'No news found.', 'wordpress-starter-custom-posttypes' ),
        'not_found_in_trash' => __( 'No news found in Trash.', 'wordpress-starter-custom-posttypes' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'news' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-media-document',
        'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' )
    );

    register_post_type( 'news', $args );
}
add_action( 'init', 'wpstarter_register_news_cpt' );
PHP

echo "📰 Custom Post Type 'News' plugin created at $PLUGIN_DIR"

# Auto-activate plugin via WP-CLI if available
if command -v wp >/dev/null 2>&1; then
    echo "⚡ Activating plugin via WP-CLI..."
    wp plugin activate wordpress-starter-custom-posttypes --path="$INSTALL_DIR"
    echo "✅ Plugin activated"
else
    echo "⚠️ WP-CLI not found. Activate the plugin manually in WP Admin."
fi
