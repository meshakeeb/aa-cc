<?php
// phpcs:ignoreFile

/**
 * Alternative version installer.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use stdClass;
use WP_Error;
use Plugin_Upgrader;
use Automatic_Upgrader_Skin;

defined( 'ABSPATH' ) || exit;

/**
 * Alternate plugin version installer
 */
class Plugin_Installer {
	/**
	 * The version to install
	 *
	 * @var string
	 */
	private $version;

	/**
	 * URL to the .zip archive for the desired version
	 *
	 * @var string
	 */
	private $package_url;

	/**
	 * The plugin name
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The plugin slug
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Constructor
	 *
	 * @param string $version     The version to install.
	 * @param string $package_url The url to the .zip archive on https://wordpress.org.
	 */
	public function __construct( $version, $package_url ) {
		$this->version     = $version;
		$this->package_url = $package_url;
		$this->plugin_name = ADVADS_PLUGIN_BASENAME;
		$this->plugin_slug = basename( ADVADS_FILE ) . '.php';
	}

	/**
	 * Do the plugin update process
	 *
	 * @return array|bool|WP_Error
	 */
	public function install() {
		$update_plugins = get_site_transient( 'update_plugins' );

		if ( ! is_object( $update_plugins ) ) {
			$update_plugins = new stdClass();
		}

		$plugin_info                                    = new stdClass();
		$plugin_info->new_version                       = $this->version;
		$plugin_info->slug                              = $this->plugin_slug;
		$plugin_info->package                           = $this->package_url;
		$update_plugins->response[ $this->plugin_name ] = $plugin_info;

		set_site_transient( 'update_plugins', $update_plugins );

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin( [ 'plugin' => $this->plugin_name ] ) );

		return $upgrader->upgrade( $this->plugin_name );
	}
}
