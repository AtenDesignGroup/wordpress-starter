<?php

namespace ForGravity\Fillable_PDFs;

if ( ! class_exists( 'ForGravity\Fillable_PDFs\Autoloader' ) ) {
	/**
	 * Autoload Fillable PDFs class files.
	 * Based on PHP-FIG PSR-4 example.
	 *
	 * @since     2.4
	 * @package   ForGravity\Fillable_PDFs
	 * @author    ForGravity
	 * @copyright Copyright (c) 2020, ForGravity
	 */
	class Autoloader {

		/**
		 * An associative array where the key is a namespace prefix and the value
		 * is an array of base directories for classes in that namespace.
		 *
		 * @since 2.4
		 * @var array
		 */
		protected $prefixes = [];

		/**
		 * Register loader with SPL autoloader stack.
		 *
		 * @sincw 2.4
		 */
		public function register() {

			spl_autoload_register( [ $this, 'load_class' ] );

		}

		/**
		 * Adds a base directory for a namespace prefix.
		 *
		 * @since 2.4
		 *
		 * @param string $prefix   The namespace prefix.
		 * @param string $base_dir A base directory for class files in the
		 *                         namespace.
		 * @param bool   $prepend  If true, prepend the base directory to the stack
		 *                         instead of appending it; this causes it to be searched first rather
		 *                         than last.
		 */
		public function add_namespace( $prefix, $base_dir, $prepend = false ) {

			// Normalize namespace prefix.
			$prefix = trim( $prefix, '\\' ) . '\\';

			// Normalize the base directory with a trailing separator.
			$base_dir = rtrim( $base_dir, DIRECTORY_SEPARATOR ) . '/';

			// Initialize the namespace prefix array.
			if ( isset( $this->prefixes[ $prefix ] ) === false ) {
				$this->prefixes[ $prefix ] = [];
			}

			// Retain the base directory for the namespace prefix.
			if ( $prepend ) {
				array_unshift( $this->prefixes[ $prefix ], $base_dir );
			} else {
				array_push( $this->prefixes[ $prefix ], $base_dir );
			}

		}

		/**
		 * Loads the class file for a given class name.
		 *
		 * @since 2.4
		 *
		 * @param string $class The fully-qualified class name.
		 *
		 * @return string|false The mapped file name on success, or boolean false on failure.
		 */
		public function load_class( $class ) {

			// The current namespace prefix.
			$prefix = $class;

			// Work backwards through the namespace names of the fully-qualified class name to find a mapped file name.
			while ( false !== $pos = strrpos( $prefix, '\\' ) ) {

				// Retain the trailing namespace separator in the prefix.
				$prefix = substr( $class, 0, $pos + 1 );

				// The rest is the relative class name.
				$relative_class = substr( $class, $pos + 1 );

				// Try to load a mapped file for the prefix and relative class.
				$mapped_file = $this->load_mapped_file( $prefix, $relative_class );
				if ( $mapped_file ) {
					return $mapped_file;
				}

				// Remove the trailing namespace separator for the next iteration of strrpos().
				$prefix = rtrim( $prefix, '\\' );

			}

			return false;

		}

		/**
		 * Load the mapped file for a namespace prefix and relative class.
		 *
		 * @since 2.4
		 *
		 * @param string $prefix         The namespace prefix.
		 * @param string $relative_class The relative class name.
		 *
		 * @return false|string Boolean false if no mapped file can be loaded, or the name of the mapped file that was loaded.
		 */
		protected function load_mapped_file( $prefix, $relative_class ) {

			// Are there any base directories for this namespace prefix?
			if ( isset( $this->prefixes[ $prefix ] ) === false ) {
				return false;
			}

			// Look through base directories for this namespace prefix.
			foreach ( $this->prefixes[ $prefix ] as $base_dir ) {

				// Prepare file name.
				$relative_path = str_replace( [ '\\', '_' ], [ '/', '-' ], $relative_class );
				$relative_path = strtolower( $relative_path );

				// Prepend file name with "class-".
				$file_parts    = explode( '/', $relative_path );
				$file_name     = array_pop( $file_parts );
				$file_parts[]  = 'class-' . $file_name;
				$relative_path = implode( '/', $file_parts );

				// Replace the namespace prefix with the base directory,
				$file = $base_dir . $relative_path . '.php';

				// If the mapped file exists, require it.
				if ( $this->require_file( $file ) ) {
					return $file;
				}

			}

			return false;

		}

		/**
		 * If a file exists, require it from the file system.
		 *
		 * @since 2.4
		 *
		 * @param string $file The file to require.
		 *
		 * @return bool True if the file exists, false if not.
		 */
		protected function require_file( $file ) {

			if ( file_exists( $file ) ) {
				require $file;
				return true;
			}

			return false;

		}

	}

	$autoloader = new Autoloader();
	$autoloader->add_namespace( 'ForGravity\\Fillable_PDFs\\', dirname( __DIR__ ) . '/includes/' );
	$autoloader->add_namespace( 'ForGravity\\Fillable_PDFs\\Legacy\\', dirname( __DIR__ ) . '/legacy/includes/' );
	$autoloader->register();
}
