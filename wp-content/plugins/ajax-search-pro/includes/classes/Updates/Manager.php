<?php
namespace WPDRMS\ASP\Updates;

use stdClass;
use WP_Error;
use WPDRMS\ASP\Misc\EnvatoLicense;

class Manager {

	/**
	 * Plugin Slug (plugin_directory/plugin_file.php)
	 * @var string
	 */
	public $plugin_slug;

	/**
	 * Plugin name (plugin_file)
	 * @var string
	 */
	public $slug;

	/**
	 * Updates Object
	 * @var Object
	 */
	private $updates_o;

	private $download_link_url = 'http://update.wp-dreams.com/u.php';

	/**
	 * Initialize a new instance of the WordPress Auto-Update class
	 *
	 * @param string $plugin_slug
	 * @param object $updates_o
	 */
	function __construct( $plugin_name, $plugin_slug, $udpates_o ) {
		// Set the class public variables
		$this->plugin_slug = $plugin_slug;
		$this->udpates_o   = $udpates_o;
		$t                 = explode( '/', $plugin_slug );
		$this->slug        = str_replace( '.php', '', $t[1] );

		// define the alternative API for updating checking
		add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'check_update' ) );

		// Define the alternative response for information checking
		add_filter( 'plugins_api', array( &$this, 'check_info' ), 10, 3 );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );

		add_action( 'in_plugin_update_message-' . $plugin_name, array( &$this, 'addUpgradeMessageLink' ) );
	}

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param $transient
	 *
	 * @return object $ transient
	 */
	public function check_update( $transient ) {

		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// If a newer version is available, add the update
		if ( $this->udpates_o->needsUpdate( true ) ) {
			$download_url = '';
			// No need to check the remote, as the ->getDownLoadURL will do it later
			$license_key = EnvatoLicense::isActivated();
			if ( $license_key !== false ) {
				if ( get_site_transient('_asp_update_dl_url') === false ) {
					$response = $this->getDownloadUrl( $license_key );
					if ( !empty($response) ) {
						if ( isset($response['status']) && $response['status'] != 1 ) {
							EnvatoLicense::deactivate( false );
							return new WP_Error( 'inactive', $response['msg'] );
						} else {
							$download_url = $response['data'];
							set_site_transient('_asp_update_dl_url', $download_url, 3600 * 3);
						}
					}
				} else {
					$download_url = get_site_transient('_asp_update_dl_url');
				}
			}

			$obj                                       = new stdClass();
			$obj->slug                                 = $this->slug;
			$obj->name                                 = $this->slug;
			$obj->new_version                          = $this->udpates_o->getVersionString();
			$obj->url                                  = "";
			$obj->package                              = $download_url;
			$obj->plugin                               = $this->plugin_slug;
			$obj->tested                               = $this->udpates_o->getTestedVersion();
			$transient->response[ $this->plugin_slug ] = $obj;
		}

		return $transient;
	}

	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( ASP_PLUGIN_BASE === $plugin_file ) {
			$plugin_slug = $this->slug;
			$plugin_name = __( 'Ajax Search Pro', 'ajax-search-pro' );

			$row_meta = [
				'view-details' => sprintf( '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
					esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin_slug . '&TB_iframe=true&width=600&height=550' ) ),
					/* translators: %s: Plugin name - Elementor Pro. */
					esc_attr( sprintf( __( 'More information about %s', 'ajax-search-pro' ), $plugin_name ) ),
					esc_attr( $plugin_name ),
					__( 'View details', 'ajax-search-pro' )
				),
				'changelog' => '<a href="https://changelog.ajaxsearchpro.com/" title="' . esc_attr( __( 'View Ajax Search Pro Pro Changelog', 'ajax-search-pro' ) ) . '" target="_blank">' . __( 'Changelog', 'ajax-search-pro' ) . '</a>',
			];

			$plugin_meta = array_merge( $plugin_meta, $row_meta );
		}

		return $plugin_meta;
	}

	/**
	 * Add our self-hosted description to the filter
	 *
	 * @param boolean $false
	 * @param array $action
	 * @param object $arg
	 *
	 * @return bool|object
	 */
	public function check_info( $false, $action, $arg ) {

		if (!is_object($arg) || !isset($arg->slug) || !isset($this->slug))
			return $false;

		if ( $arg->slug == $this->slug ) {
			$information = new stdClass();

			$information->name          = "Ajax Search Pro";
			$information->slug          = $this->slug;
			$information->author = '<a href="https://ajaxsearchpro.com/">AjaxSearchPro.com</a>';
			$information->new_version   = $this->udpates_o->getVersionString();
			$information->version   = $this->udpates_o->getVersionString();
			$information->requires      = $this->udpates_o->getRequiresVersion();
			$information->tested        = $this->udpates_o->getTestedVersion();
			$information->downloaded    = $this->udpates_o->getDownloadedCount();
			$information->last_updated  = $this->udpates_o->getLastUpdated();
			$information->sections      = array(
				'changelog' => "<h4>Version ".$this->udpates_o->getVersionString()."</h4><p>
					For the changelog please visit <a target='_blank' href='https://changelog.ajaxsearchpro.com/'>the knowledge base.</a>
				</p>"
			);
			$information->banners = [
				'high' => 'https://ajaxsearchpro.com/assets/banner-1544x500-min.png',
				'low' => 'https://ajaxsearchpro.com/assets/banner-1544x500-min.png',
			];
			$information->download_link = 'http://codecanyon.net/downloads/';

			return $information;
		}

		return $false;
	}

	/**
	 * Shows message on Wp plugins page with a link for updating from envato.
	 */
	public function addUpgradeMessageLink() {
		echo '<style media="all">tr#ajax-search-pro + tr.plugin-update-tr a.thickbox + em { display: none; }</style>';
		if ( EnvatoLicense::isActivated() === false )
			echo ' <a href="'.get_admin_url() .'admin.php?page=asp_updates_help">' . __( 'Activate your license', 'ajax-search-pro') . '</a> ' . __( 'for automatic updates or', 'ajax-search-pro') . ' <a target="_blank" href="http://codecanyon.net/downloads/">' . __( 'download new version from CodeCanyon.', 'ajax-search-pro' ) . '</a>';
		else
			echo ' or <a href="' . wp_nonce_url( admin_url( 'update.php?action=upgrade-plugin&plugin=' . ASP_PLUGIN_NAME ), 'upgrade-plugin_' . ASP_PLUGIN_NAME ) . '">' . __( 'Update Ajax Search Pro now.', 'ajax-search-pro' ) . '</a>';
	}


	/**
	 * Get unique, short-lived download link
	 *
	 * @param string $license_key
	 *
	 * @return array|boolean JSON response or false if request failed
	 */
	public function getDownloadUrl( $license_key ) {
		$url = rawurlencode( $_SERVER['HTTP_HOST'] );
		$key = rawurlencode( $license_key );

		$url = $this->download_link_url . '?file=asp&url=' . $url . '&key=' . $key . '&version=' . ASP_CURR_VER;

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return json_decode( $response['body'], true );
	}
}