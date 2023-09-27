<?php
/**
 * The File_Path class.
 *
 * These methods are derived from CMB2.
 *
 * @since 3.3
 *
 * @package ForGravity\Fillable_PDFs
 */

namespace ForGravity\Fillable_PDFs\Utils;

/**
 * The File_Path class.
 *
 * @since 3.3
 *
 * @package ForGravity\Fillable_PDFs
 */
class File_Path {

	/**
	 * The WordPress ABSPATH constant.
	 *
	 * @since 3.3
	 * @var   string
	 */
	private static $ABSPATH = ABSPATH;

	/**
	 * The url which is used to load local resources.
	 *
	 * @since 3.3
	 * @var   string
	 */
	private static $url = '';

	/**
	 * Helper function to provide directory path to FillablePDFs.
	 *
	 * @since  3.3
	 * @param  string $path Path to append.
	 * @return string        Directory with optional path appended
	 */
	public static function dir( $path = '' ) {
		$path = ltrim( $path, '/' );

		return trailingslashit( FG_FILLABLEPDFS_DIR ) . $path;
	}

	/**
	 * Defines the url which is used to load local resources.
	 * This may need to be filtered for local Window installations.
	 * If resources do not load, please check the wiki for details.
	 *
	 * @since 3.3
	 *
	 * @param string $path URL path.
	 *
	 * @return string URL to FillablePDFs resources
	 */
	public static function url( $path = '' ) {
		$path = ltrim( $path, '/' );

		if ( self::$url ) {
			return self::$url . $path;
		}

		$url = self::get_url_from_dir( self::dir() );

		/**
		 * Filter the FillablePDFs location url.
		 *
		 * @param string $url Currently registered url.
		 */
		self::$url = trailingslashit( fg_pdfs_apply_filters( 'url', $url, FG_FILLABLEPDFS_VERSION ) );

		return self::$url . $path;
	}

	/**
	 * Returns a file name with the required file extension.
	 *
	 * @since 4.0
	 *
	 * @param string $file_name File name.
	 * @param string $extension Extension to add.
	 *
	 * @return string
	 */
	public static function add_file_extension( $file_name, $extension = 'pdf' ) {

		if ( pathinfo( $file_name, PATHINFO_EXTENSION ) !== $extension ) {
			$file_name  = rtrim( $file_name, '.' );
			$file_name .= '.' . $extension;
		}

		return $file_name;

	}

	/**
	 * Converts a system path to a URL
	 *
	 * @since  3.3
	 *
	 * @param string $dir Directory path to convert.
	 *
	 * @return string      Converted URL.
	 */
	private static function get_url_from_dir( $dir ) {
		$dir = self::normalize_path( $dir );

		// Let's test if We are in the plugins or mu-plugins dir.
		$test_dir = trailingslashit( $dir ) . 'unneeded.php';
		if (
			0 === strpos( $test_dir, self::normalize_path( WPMU_PLUGIN_DIR ) )
			|| 0 === strpos( $test_dir, self::normalize_path( WP_PLUGIN_DIR ) )
		) {
			// Ok, then use plugins_url, as it is more reliable.
			return trailingslashit( plugins_url( '', $test_dir ) );
		}

		// Ok, now let's test if we are in the theme dir.
		$theme_root = self::normalize_path( get_theme_root() );
		if ( 0 === strpos( $dir, $theme_root ) ) {
			// Ok, then use get_theme_root_uri.
			return set_url_scheme(
				trailingslashit(
					str_replace(
						untrailingslashit( $theme_root ),
						untrailingslashit( get_theme_root_uri() ),
						$dir
					)
				)
			);
		}

		// Check to see if it's anywhere in the root directory.
		$site_dir = self::get_normalized_abspath();
		$site_url = trailingslashit( is_multisite() ? network_site_url() : site_url() );

		$url = str_replace(
			array( $site_dir, WP_PLUGIN_DIR ),
			array( $site_url, WP_PLUGIN_URL ),
			$dir
		);

		return set_url_scheme( $url );
	}

	/**
	 * Get the normalized absolute path defined by WordPress.
	 *
	 * @since  3.3
	 *
	 * @return string  Normalized absolute path.
	 */
	private static function get_normalized_abspath() {
		return self::normalize_path( self::$ABSPATH );
	}

	/**
	 * `wp_normalize_path` wrapper for back-compat. Normalize a filesystem path.
	 *
	 * On windows systems, replaces backslashes with forward slashes
	 * and forces upper-case drive letters.
	 * Allows for two leading slashes for Windows network shares, but
	 * ensures that all other duplicate slashes are reduced to a single.
	 *
	 * @since 3.3
	 *
	 * @param string $path Path to normalize.
	 *
	 * @return string Normalized path.
	 */
	private static function normalize_path( $path ) {
		if ( function_exists( 'wp_normalize_path' ) ) {
			return wp_normalize_path( $path );
		}

		// Replace newer WP's version of wp_normalize_path.
		$path = str_replace( '\\', '/', $path );
		$path = preg_replace( '|(?<=.)/+|', '/', $path );
		if ( ':' === substr( $path, 1, 1 ) ) {
			$path = ucfirst( $path );
		}

		return $path;
	}

}
