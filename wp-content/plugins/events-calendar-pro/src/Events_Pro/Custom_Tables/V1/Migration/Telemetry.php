<?php
/**
 * Reports the migration operation results to the TEC Support API.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Migration\State;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use Tribe__Events__Pro__Main as ECP;

/**
 * Class Telemetry.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration;
 */
class Telemetry {
	/**
	 * The name of the action that will be dispatched by Action Scheduler to POST the telemetry.
	 *
	 * @since 6.0.0
	 */
	const ACTION_NAME = 'tec_events_pro_custom_tables_v1_migration_report_telemetry_send';

	/**
	 * The name of the request type for this telemetry.
	 *
	 * @since 6.0.0
	 */
	const REQUEST_TYPE = 'migration.tec-6-0';

	/**
	 * The default telemetry report URL.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	private static $default_api_url = 'https://support-api.theeventscalendar.com/telemetry/notification';

	/**
	 * The full URL, including the route path, to send the migration report to.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * Telemetry contstructor.
	 *
	 * @since 6.0.0
	 */
	public function __construct() {
		/**
		 * Allows filtering the telemetry report URL.
		 *
		 * @since 6.0.0
		 *
		 * @param string $url The telemetry report URL to send the report to.
		 */
		$this->api_url = apply_filters( 'tec_events_pro_telemetry_url', self::$default_api_url );
	}

	/**
	 * Returns whether the telemetry is enabled or not.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $dry_run Whether this is a dry run or not.
	 *
	 * @return bool Whether the telemetry is enabled or not.
	 */
	public static function is_enabled( bool $dry_run ): bool {
		// By default, run only during preview.
		$enabled = $dry_run;

		/**
		 * Filters whether migration telemetry is enabled or not.
		 *
		 * @since 6.0.0
		 *
		 * @param bool $enabled Whether migration telemetry is enabled or not.
		 */
		return apply_filters( 'tec_events_pro_custom_tables_v1_migration_enabled', $enabled );
	}

	/**
	 * Queues the telemetry report to be sent in an Actions Scheduler async action.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $dry_run Whether this is a dry run or not.
	 *
	 * @return bool Whether the telemetry report was queued or not.
	 */
	public function queue( bool $dry_run ): bool {
		$id = as_enqueue_async_action( self::ACTION_NAME, [ $dry_run ] );

		if ( empty( $id ) ) {
			$this->log_error( 'Failed to queue telemetry dispatch action.' );

			return false;
		}

		return true;
	}

	/**
	 * Logs an error message.
	 *
	 * @since 6.0.0
	 *
	 * @param string      $message The error message to log.
	 * @param string|null $data    Optional. The data to log.
	 *
	 * @return void The method does not return a value and will dispatch a
	 *              logging action.
	 */
	private function log_error( string $message, string $data = null ): void {
		$context = [
			'source' => __CLASS__,
			'slug'   => 'telemetry-migration-report-post-fail',
			'error'  => $message,
		];

		if ( $data ) {
			$context['data'] = $data;
		}

		do_action( 'tribe_log', 'error', 'TEC 6.0 Migration report failed.', $context );
	}

	/**
	 * Logs a success message.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return a value and will dispatch a
	 *              logging action.
	 */
	private function log_success(): void {
		do_action( 'tribe_log', 'debug', 'TEC 6.0 Migration report sent.', [
			'source' => __CLASS__,
			'slug'   => 'telemetry-migration-report-post-success',
		] );
	}

	/**
	 * Sends the telemetry report to the TEC Support API.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the telemetry report was sent or not.
	 */
	public function send(): bool {
		try {
			if ( ( $license_key = $this->get_license_key() ) === null ) {
				$this->log_error( 'License key not found.' );

				return false;
			}

			$response = wp_remote_post( $this->api_url, $this->post_args( $license_key ) );

			if ( $response instanceof \WP_Error ) {
				$this->log_error( $response->get_error_message() );
			}

			$body = wp_remote_retrieve_body( $response );

			if ( empty( $body ) ) {
				$this->log_error( 'Response body is missing.' );

				return false;
			}

			$decoded = json_decode( $body, true );

			if ( $decoded === false ) {
				$this->log_error( 'Response body is malformed.' );

				return false;
			}

			if ( empty( $decoded['success'] ) ) {
				$this->log_error( 'Request failed.', $body );

				return false;
			}

			$this->log_success();

			return true;
		} catch ( \Exception $e ) {
			$this->log_error( $e->getMessage() );

			return false;
		}
	}

	/**
	 * Returns the current site domain.
	 *
	 * @since 6.0.0
	 *
	 * @return string The current site domain.
	 */
	private function get_domain(): string {
		return wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * Returns the site report data in JSON format.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $overrides An array of overrides to use when generating the report data.
	 *
	 * @return string The Site and Event report data, in JSON format.
	 */
	private function get_json_data(): string {
		$data = $this->get_array_data();

		return json_encode( $data );
	}

	/**
	 * Returns the POST request arguments that will be used to POST the telemetry.
	 *
	 * @since 6.0.0
	 *
	 * @param string $license_key The license key.
	 *
	 * @return array The POST request arguments.
	 */
	private function post_args( string $license_key ): array {
		return [
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
			],
			'body'    => [
				'license_key' => $license_key,
				'domain'      => $this->get_domain(),
				'type'        => self::REQUEST_TYPE,
				'data'        => $this->get_json_data(),
			],
		];
	}

	/**
	 * Returns the license key.
	 *
	 * @since 6.0.0
	 *
	 * @return string|null The license key, or `null` if not found.
	 */
	private function get_license_key(): ?string {
		$license = get_option( 'pue_install_key_events_calendar_pro', '' );

		if (
			empty( $license )
			&& class_exists( '\Tribe__Events__Pro__PUE__Helper' )
			&& defined( '\Tribe__Events__Pro__PUE__Helper::DATA' )
		) {
			$license = \Tribe__Events__Pro__PUE__Helper::DATA;
		}

		if ( ! $license && is_multisite() ) {
			$multisite_license = get_network_option( null, 'pue_install_key_events_calendar_pro' );
			$license = $multisite_license ?? null;
		}

		return $license;
	}

	/**
	 * Returns the Telemetry report data in an array format.
	 *
	 * @since 6.0.0
	 *
	 * @return array The Site and Event report data, in an array format.
	 */
	public function get_array_data(): array {
		$site_report = Site_Report::build();
		$data = $site_report->get_data();
		$event_reports = $site_report->get_event_reports();
		$strategies = [];
		$series_count = 0;
		$single_events_count = 0;
		$recurring_events_count = 0;
		$errors_count = 0;
		$errors = [];

		// Sort the reports in ascending order by post ID.
		usort( $event_reports, static function ( Event_Report $a, Event_Report $b ): int {
			return $a->source_event_post->ID <=> $b->source_event_post->ID;
		} );

		foreach ( $event_reports as $event_report ) {
			if ( $event_report->is_single ) {
				$single_events_count ++;
			} else {
				$recurring_events_count ++;
			}

			$strategies_applied = array_values( $event_report->strategies_applied );
			$strategy = $strategies_applied[0] ?? 'n/a';

			if ( ! isset( $strategies[ $strategy ] ) ) {
				$strategies[ $strategy ] = 0;
			}

			++ $strategies[ $strategy ];
			$series_count += count( $event_report->series );
			if ( $event_report->error ) {
				$errors_count ++;

				if ( ! isset( $errors[ $strategy ] ) ) {
					$errors[ $strategy ] = [];
				}

				$errors[ $strategy ][] = strip_tags( $event_report->error );
			}
		}

		$start_timestamp = tribe( State::class )->get( 'started_timestamp' );
		$preview_time = $data['completed_timestamp'] - $start_timestamp;

		return [
			'versions' => [
				'the-events-calendar'	 => TEC::VERSION,
				'events-calendar-pro'	 => ECP::VERSION,
			],
			'preview_time'     => $preview_time,
			'estimated_time'   => $data['estimated_time_in_seconds'],
			'events_previewed' => array_merge( [
				'total'            => $data['total_events'],
				'single_events'    => $single_events_count,
				'recurring_events' => $recurring_events_count,
			], $strategies ),
			'errors_count'     => $errors_count,
			'errors'           => $errors,
			'created_series'   => $series_count,
		];
	}
}