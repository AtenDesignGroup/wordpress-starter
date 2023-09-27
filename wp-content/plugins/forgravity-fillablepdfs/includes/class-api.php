<?php

namespace ForGravity\Fillable_PDFs;

use Exception;

/**
 * Fillable PDFs API library.
 *
 * @since     1.0
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2017, ForGravity
 */
class API {

	/**
	 * Base Fillable PDFs API URL.
	 *
	 * @since  1.0
	 * @var    string
	 * @access protected
	 */
	public static $api_url = FG_FILLABLEPDFS_API_URL;

	/**
	 * License key.
	 *
	 * @since 1.0
	 * @var   string
	 */
	protected $license_key;

	/**
	 * Site home URL.
	 *
	 * @since 1.0
	 * @var   string
	 */
	protected $site_url;

	/**
	 * Cache of previously requested templates.
	 *
	 * @since 3.4
	 * @var   array
	 */
	private $templates = [];

	/**
	 * EDD Product ID.
	 *
	 * @since 3.4
	 * @var   int
	 */
	private $product_id;

	/**
	 * Initialize Fillable PDFs API library.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $license_key License key.
	 */
	public function __construct( $license_key, $product_id = FG_FILLABLEPDFS_EDD_ITEM_ID ) {

		$this->license_key = $license_key;
		$this->site_url    = home_url();

		$this->product_id = $product_id;

	}





	// # FILES ---------------------------------------------------------------------------------------------------------

	/**
	 * Get PDF file fields.
	 *
	 * @since  2.0
	 *
	 * @param array $file       Temporary file details.
	 * @param bool  $has_fields Check if file has fields.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_file_meta( $file, $has_fields = false ) {

		// Build request URL.
		$request_url = self::$api_url . 'files/meta';
		$request_url = $has_fields ? add_query_arg( [ 'has_fields' => 'true' ], $request_url ) : $request_url;

		// Generate boundary.
		$boundary = wp_generate_password( 24 );

		// Prepare request body.
		$body = '--' . $boundary . "\r\n";
		$body .= 'Content-Disposition: form-data; name="pdf_file"; filename="' . $file['name'] . '"' . "\r\n\r\n";
		$body .= file_get_contents( $file['tmp_name'] ) . "\r\n";
		$body .= '--' . $boundary . '--';

		// Execute request.
		$response = wp_remote_request(
			$request_url,
			$this->request_args(
				$body,
				'POST',
				'multipart/form-data; boundary=' . $boundary
			)
		);

		// If request attempt threw a WordPress error, throw exception.
		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}

		// Decode response.
		$response = json_decode( $response['body'], true );

		// If error response was received, throw exception.
		if ( isset( $response['error'] ) ) {
			throw new Exception( $response['message'] );
		}

		return $response;

	}





	// # TEMPLATES -----------------------------------------------------------------------------------------------------

	/**
	 * Create template.
	 *
	 * @since  1.0
	 *
	 * @param string $name      Template name.
	 * @param array  $file      Temporary file details.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function create_template( $name = '', $file = [] ) {

		// Build request URL.
		$request_url = self::$api_url . 'templates';

		// Generate boundary.
		$boundary = wp_generate_password( 24 );

		// Prepare request body.
		$body  = '--' . $boundary . "\r\n";
		$body .= 'Content-Disposition: form-data; name="name"' . "\r\n\r\n" . $name . "\r\n";
		$body .= '--' . $boundary . "\r\n";
		$body .= 'Content-Disposition: form-data; name="pdf_file"; filename="' . $file['name'] . '"' . "\r\n\r\n";
		$body .= file_get_contents( $file['tmp_name'] ) . "\r\n";
		$body .= '--' . $boundary . '--';

		// Execute request.
		$response = wp_remote_request(
			$request_url,
			$this->request_args(
				$body,
				'POST',
				'multipart/form-data; boundary=' . $boundary
			)
		);

		// If request attempt threw a WordPress error, throw exception.
		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}

		// Decode response.
		$response = json_decode( $response['body'], true );

		// If error response was received, throw exception.
		if ( isset( $response['error'] ) ) {
			throw new Exception( $response['message'] );
		}

		return $response;

	}

	/**
	 * Delete template.
	 *
	 * @since  1.0
	 *
	 * @param string $template_id Template ID.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function delete_template( $template_id = '' ) {

		return $this->make_request( 'templates/' . $template_id, [], 'DELETE' );

	}

	/**
	 * Get specific template.
	 *
	 * @since  1.0
	 *
	 * @param string $template_id Template ID.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_template( $template_id = '' ) {

		if ( rgar( $this->templates, $template_id ) ) {
			return $this->templates[ $template_id ];
		}

		try {

			$template = $this->make_request( 'templates/' . $template_id );

			if ( ! is_wp_error( $template ) ) {
				$this->templates[ $template_id ] = $template;
			}

			return $template;

		} catch ( Exception $e ) {

			throw $e;

		}

	}

	/**
	 * Get number of templates registered to license.
	 *
	 * @since  3.0
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_template_count() {

		return $this->make_request( 'templates/_count' );

	}

	/**
	 * Get templates for license.
	 *
	 * @since  3.0 Added $page, $per_page parameters.
	 * @since  1.0
	 *
	 * @param int $page     Page number.
	 * @param int $per_page Templates per page.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_templates( $page = 1, $per_page = 20 ) {

		$params = [
			'page'     => $page,
			'per_page' => $per_page,
		];

		/**
		 * Determine whether to show all templates for license or templates registered to site.
		 *
		 * @since 3.0
		 *
		 * @param bool $display_all_templates Display all templates for license.
		 */
		if ( ! fg_pdfs_apply_filters( 'display_all_templates', true ) ) {
			$params['current_site'] = true;
		}

		$templates = $this->make_request( 'templates', $params );

		if ( ! is_wp_error( $templates ) ) {
			foreach ( $templates as $template ) {
				if ( ! isset( $this->templates[ $template['template_id'] ] ) ) {
					$this->templates[ $template['template_id'] ] = $template;
				}
			}
		}

		return $templates;

	}

	/**
	 * Get original file for template.
	 *
	 * @since  1.0
	 *
	 * @param string $template_id Template ID.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_template_file( $template_id = '' ) {

		return $this->make_request( 'templates/' . $template_id . '/file' );

	}

	/**
	 * Create template.
	 *
	 * @since  1.0
	 *
	 * @param string     $template_id Template ID.
	 * @param string     $name        Template name.
	 * @param array|null $file        Temporary file details.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function save_template( $template_id, $name, $file = null ) {

		// If no file is provided, use default method.
		if ( ! is_array( $file ) ) {
			return $this->make_request( 'templates/' . $template_id, [ 'name' => $name ], 'PUT' );
		}

		// Build request URL.
		$request_url = self::$api_url . 'templates/' . $template_id;

		// Generate boundary.
		$boundary = wp_generate_password( 24 );

		// Prepare request body.
		$body  = '--' . $boundary . "\r\n";
		$body .= 'Content-Disposition: form-data; name="name"' . "\r\n\r\n" . $name . "\r\n";
		$body .= '--' . $boundary . "\r\n";
		$body .= 'Content-Disposition: form-data; name="pdf_file"; filename="' . $file['name'] . '"' . "\r\n\r\n";
		$body .= file_get_contents( $file['tmp_name'] ) . "\r\n";
		$body .= '--' . $boundary . '--';

		// Execute request.
		$response = wp_remote_request(
			$request_url,
			$this->request_args(
				$body,
				'POST',
				'multipart/form-data; boundary=' . $boundary
			)
		);

		// If request attempt threw a WordPress error, throw exception.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Decode response.
		$response = json_decode( $response['body'], true );

		// If error response was received, throw exception.
		if ( isset( $response['error'] ) ) {
			throw new Exception( $response['message'] );
		}

		return $response;

	}

	/**
	 * Generate PDF.
	 *
	 * @since  1.0
	 *
	 * @param string $template_id Template ID.
	 * @param array  $meta        PDF meta.
	 *
	 * @return string
	 * @throws Exception
	 */
	public function generate( $template_id = '', $meta = [] ) {

		return $this->make_request( 'templates/' . $template_id . '/generate', $meta, 'POST' );

	}





	// # LICENSE -------------------------------------------------------------------------------------------------------

	/**
	 * Get information about current license.
	 *
	 * @since  1.0
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_license_info() {

		static $license_info;

		if ( ! isset( $license_info ) ) {
			$license_info = $this->make_request( 'license' );
		}

		return $license_info;

	}





	// # REQUEST METHODS -----------------------------------------------------------------------------------------------

	/**
	 * Make API request.
	 *
	 * @since  1.0
	 *
	 * @param string $path    Request path.
	 * @param array  $options Request options.
	 * @param string $method  Request method. Defaults to GET.
	 *
	 * @return array|string
	 * @throws Exception
	 */
	private function make_request( $path, $options = [], $method = 'GET' ) {

		// Build request URL.
		$request_url = self::$api_url . $path;

		// Add options if this is a GET request.
		if ( 'GET' === $method ) {
			$request_url = add_query_arg( $options, $request_url );
		}

		// Execute request.
		$response = wp_remote_request(
			$request_url,
			$this->request_args(
				'GET' !== $method ? wp_json_encode( $options ) : null,
				$method
			)
		);

		// If request attempt threw a WordPress error, throw exception.
		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}

		// Decode response.
		$response_body = fg_fillablepdfs()->maybe_decode_json( $response['body'] );

		// If error response was received, throw exception.
		if ( isset( $response_body['error'] ) || wp_remote_retrieve_response_code( $response ) >= 400 ) {
			throw new Exception( rgar( $response_body, 'message', $response_body ), wp_remote_retrieve_response_code( $response ) );
		}

		return $response_body;

	}

	/**
	 * Returns the default set of request arguments.
	 *
	 * @since 3.4
	 *
	 * @param string $body         Request body.
	 * @param string $method       Request method. Defaults to GET.
	 * @param string $content_type Request content type.
	 *
	 * @return array
	 */
	private function request_args( $body = null, $method = 'GET', $content_type = 'application/json' ) {

		return [
			'body'    => $body,
			'method'  => $method,
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( $this->site_url . ':' . $this->license_key ), // phpcs:ignore
				'Content-Type'  => $content_type,
				'Product-ID'    => $this->product_id,
			],
		];

	}

}
