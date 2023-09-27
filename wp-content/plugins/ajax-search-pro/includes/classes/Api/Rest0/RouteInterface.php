<?php
namespace WPDRMS\ASP\API\REST0;
use WP_REST_Request;

interface RouteInterface {
	public function handle( WP_REST_Request $request );
}