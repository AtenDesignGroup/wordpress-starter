<?php

/**
 * @file
 * WordPress child theme generator.
 *
 * PHP version 8.1
 *
 * @package WordPress
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since File available since Release 1.0.0
 */

// Define the help message.
$description = "Description:
  Generates a new child theme based on an existing parent theme.\n";

$usage = "Usage:
  generator.php [options] [--] <machine-name>
  generator.php --name custom_child_theme --display-name 'Custom Child Theme' --path wp-content/themes --parent aten-fse\n";

$varOptions = "Options:
 -n, --name           A name for the child theme.
 -d, --display-name   A human readable display name for your theme.
 -p, --path           The path where your theme will be created. Defaults to: wp-content/themes
 -t, --parent         The parent theme for your child theme. Choose either 'aten-fse' or 'aten-hybrid'.
 \n";

// Parse command-line arguments.
$shortopts = 'h';
$longopts = ['help'];
$options = getopt($shortopts, $longopts);

// If the help option was passed, display the help message and exit.
if (isset($options['help']) || isset($options['h'])) {
	echo $description . "\n" . $usage . "\n" . $varOptions . "\n";
	exit(0);
}

// Default values.
$theme_name = 'child_theme';
$theme_path = 'wp-content/themes';
$theme_display_name = '';
$parent_theme = '';

// Parse command-line arguments.
$shortopts = 'n:p:d:t:';
$longopts = ['name:', 'path:', 'display-name:', 'parent:'];
$options = getopt($shortopts, $longopts);

/**
 * Sanitize provided theme name.
 *
 * This function cleans provided name to meet WordPress expectations.
 *
 * @param string $data
 *   - Theme name provided as string.
 */
function sanitize_theme_name($data) {
	$cleaned_string = preg_replace("/[^A-Za-z0-9\_]/", "", $data);

	// If sanitized string is empty, stop generator.
	if (empty($cleaned_string)) {
		echo ("An invalid name was provided, please try again.\n");
		die();
	}

	return $cleaned_string;
}

/**
 * Sanitize provided theme path.
 *
 * @param string $data
 *   - Folder path provided as string.
 */
function sanitize_path($data) {
	// Remove forward & trailing slashes if they exist.
	$string = preg_replace_callback('/\/([A-Za-z0-9]+)[\s-]*([A-Za-z0-9]*)/', function ($matches) {
		return '/' . str_replace([' ', '-'], '', $matches[1]) .
		       str_replace([' ', '-'], '', $matches[2]);
	}, $data);
	$cleaned_string = preg_replace('/^\/|\/$/', '', $string);

	// If sanitized string is empty, stop generator.
	if (empty($cleaned_string)) {
		echo ("An invalid path was provided, please try again.\n");
		die();
	}

	return $cleaned_string;
}

// Set values based on command-line arguments or defaults.
if (isset($options['n']) || isset($options['name'])) {
	$theme_name = $options['n'] ?? $options['name'];
} else {
	// Store desired child theme name.
	$theme_name = readline('Enter child theme machine name: ');
}

// Sanitize data.
$theme_name = sanitize_theme_name($theme_name);

// Would user like to change install path.
if (isset($options['p']) || isset($options['path'])) {
	$changePath = $options['p'] ?? $options['path'];
} else {
	$changePath = readline("Where should we generate the child theme? [ $theme_path ]: ");
}
// If user entered data sanitize, otherwise use default.
$theme_path = empty($changePath) ? $theme_path : sanitize_path($changePath);

// Set display name for the child theme.
if (isset($options['d']) || isset($options['display-name'])) {
	$theme_display_name = $options['d'] ?? $options['display-name'];
} else {
	$theme_display_name = readline("Set a theme display name: ");
}

// If the user didn't enter a display name, use the machine name as default.
if (empty($theme_display_name)) {
	$theme_display_name = ucfirst(str_replace('_', ' ', $theme_name));
}

// Handle parent theme input.
if (isset($options['t']) || isset($options['parent'])) {
	$parent_theme = $options['t'] ?? $options['parent'];
} else {
	// Ask the user to choose the parent theme.
	$parent_theme = readline("Please choose a parent theme (aten-fse or aten-hybrid): ");
}

// Validate the parent theme choice.
$parent_theme = strtolower($parent_theme);
if ($parent_theme !== 'aten-fse' && $parent_theme !== 'aten-hybrid') {
	echo "Invalid parent theme selected. Please choose either 'aten-fse' or 'aten-hybrid'.\n";
	die();
}

// Create the child theme directory.
if (!is_dir($theme_path . '/' . $theme_name)) {
	mkdir($theme_path . '/' . $theme_name, 0777, true);
}

// Generate the style.css file.
$style_css_content = "/*
Theme Name: $theme_display_name
Theme URI: https://example.com
Author: Your Name
Author URI: https://example.com
Description: A custom WordPress child theme based on $parent_theme.
Version: 1.0
Template: $parent_theme
Text Domain: $theme_name
*/";

// Save the style.css file.
file_put_contents($theme_path . '/' . $theme_name . '/style.css', $style_css_content);

// Generate the functions.php file.
$functions_php_content = "<?php
/**
 * Enqueue parent theme and child theme styles.
 */
function {$theme_name}_enqueue_styles() {
  // Enqueue the parent theme styles.
  wp_enqueue_style( '$parent_theme-style', get_template_directory_uri() . '/style.css' );

  // Enqueue child theme styles.
  wp_enqueue_style( '{$theme_name}-style', get_stylesheet_directory_uri() . '/style.css', array( '$parent_theme-style' ) );
}
add_action( 'wp_enqueue_scripts', '{$theme_name}_enqueue_styles' );
";

// Save the functions.php file.
file_put_contents($theme_path . '/' . $theme_name . '/functions.php', $functions_php_content);

echo "Child theme '$theme_display_name' based on '$parent_theme' has been generated successfully at '$theme_path/$theme_name'.\n";
