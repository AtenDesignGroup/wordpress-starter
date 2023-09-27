<?php
namespace WPDRMS\ASP\Api\Rest0;
use WP_Error, WP_REST_Request, WPDRMS\ASP\Patterns\SingletonTrait;


class Rest {
	use SingletonTrait;

	/**
	 * The Rest Namespace with version code
	 *
	 * @var string
	 */
	private $namespace = 'ajax-search-pro/v0';

	/**
	 * Route information
	 *
	 * @var string[][]
	 */
	private $routes = array(
		'/woo_search' => array(
			'methods' => 'GET, POST',
			'handler' => 'RouteWooSearch'
		),
		'/search' => array(
			'methods' => 'GET, POST',
			'handler' => 'RouteSearch'
		)
	);

	/**
	 * Init process
	 *
	 * @return bool
	 */
	function init(): bool {
		if ( defined('ASP_DISABLE_REST') && ASP_DISABLE_REST ) {
			return false;
		} else {
			add_action('rest_api_init', array($this, 'registerRoutes'));
		}
		return true;
	}

	/**
	 *	Registering the routes
	 */
	function registerRoutes() {
		foreach ( $this->routes as $route => $data ) {
			register_rest_route($this->namespace, $route, array(
				'methods' => $data['methods'],
				'callback' => array( $this, 'route'),
				'permission_callback' => '__return_true'
			));
		}
	}

	/**
	 * Handles the request route
	 *
	 * @param WP_REST_Request $request
	 * @return array|WP_Error
	 */
	function route( WP_REST_Request $request ) {
		$route = str_replace($this->namespace . '/', '', $request->get_route());
		if ( isset($this->routes[$route]) ) {
			if ( !$this->securityCheck( $request ) ) {
				return new WP_Error(
					'401',
					esc_html__( 'Not Authorized', 'ajax-search-pro' ),
					array( 'status' => 401 )
				);
			} else {
				// PHP 7.4 support, the $class name has to be put into a variable first
				$class = __NAMESPACE__ . '\\' . $this->routes[$route]['handler'];
				return (new $class)->handle($request);
			}
		} else {
			return new WP_Error(
				'400',
				esc_html__( 'Route error', 'ajax-search-pro' ),
				array( 'status' => 400 )
			);
		}
	}

	/** @noinspection PhpUnusedParameterInspection */
	private function securityCheck($request ): bool {
		return true;
	}
}