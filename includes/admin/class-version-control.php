<?php
/**
 * Admin Version Control.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Version Control.
 */
class Version_Control implements Integration_Interface {
	/**
	 * Includes up to this amount of latest minor version into the usable version, including all the in between patches.
	 *
	 * @var int
	 */
	private const MINOR_VERSION_COUNT = 3;

	/**
	 * The version list transient name
	 *
	 * @var string
	 */
	public const VERSIONS_TRANSIENT = 'advads-versions-list';

	/**
	 * Hooks into WordPress
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'wp_ajax_advads_get_usable_versions', [ $this, 'get_usable_versions' ] );
		add_action( 'wp_ajax_advads_install_alternate_version', [ $this, 'install_plugin' ] );
	}

	/**
	 * Download and install the desired version
	 *
	 * @return void
	 */
	public function install_plugin(): void {
		$this->check_user_capabilities();
		wp_parse_str( Params::post( 'vars', '' ), $args );
		$nonce = sanitize_key( $args['nonce'] ) ?? '';

		if ( ! wp_verify_nonce( $nonce, 'advads-version-control' ) ) {
			wp_send_json_error( 'Not authorized', 401 );
		}

		$exploded  = explode( '|', $args['version'] );
		$version   = sanitize_text_field( $exploded[0] );
		$package   = sanitize_url( $exploded[1] );
		$installer = new Plugin_Installer( $version, $package );
		$result    = $installer->install();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				[
					'error_code'    => $result->get_error_code(),
					'error_message' => $result->get_error_message(),
				],
				400
			);
		}

		activate_plugin( basename( ADVADS_ABSPATH . basename( ADVADS_FILE ) ) );

		wp_send_json_success(
			[
				'result'   => $result,
				'redirect' => admin_url( 'plugins.php' ),
			],
			200
		);
	}

	/**
	 * Get usable version, fetch from the info API if needed
	 *
	 * @return mixed|void
	 */
	public function get_usable_versions() {
		$this->check_user_capabilities();

		if ( ! wp_verify_nonce( Params::post( 'nonce', '', FILTER_SANITIZE_FULL_SPECIAL_CHARS ), 'advads-version-control' ) ) {
			wp_send_json_error( 'Not authorized', 401 );
		}

		$stored_versions = get_transient( self::VERSIONS_TRANSIENT );

		if ( $stored_versions ) {
			if ( wp_doing_ajax() ) {
				wp_send_json_success( $stored_versions, 200 );
			}

			return $stored_versions;
		}

		$info = $this->get_advanced_ads_info();

		if ( is_wp_error( $info ) ) {
			wp_send_json_error( $info->get_error_message() . '>>' . $info->get_error_message(), $info->get_error_code() );
		}

		$versions = $this->filter_version_number( $info['versions'] );
		set_transient( self::VERSIONS_TRANSIENT, $versions, 3 * HOUR_IN_SECONDS );
		wp_send_json_success( $versions, 200 );
	}

	/**
	 * Perform capabilities check
	 *
	 * @return void
	 */
	private function check_user_capabilities() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( 'Not enough permissions', 401 );
		}
	}

	/**
	 * Filter the versions list from the info API
	 *
	 * - all updates until the last three minor updates
	 * - the last version before the last major update
	 *
	 * @param array $versions all version the info API.
	 *
	 * @return array
	 */
	private function filter_version_number( $versions ) {
		$results = [];

		// Remove the "dev" version.
		unset( $versions['trunk'] );

		$version_numbers = array_keys( $versions );

		usort( $version_numbers, 'version_compare' );

		$version_numbers = array_reverse( $version_numbers );

		$major                 = '';
		$minor                 = '';
		$minor_version_changes = -1;
		$major_version_changes = -1;

		foreach ( $version_numbers as $number ) {
			if ( false !== stripos( $number, 'rc' ) || false !== stripos( $number, 'alpha' ) || false !== stripos( $number, 'beta' ) ) {
				// keep only production versions.
				continue;
			}
			$parts      = explode( '.', $number );
			$major_part = $parts[0];
			$minor_part = explode( '-', $parts[1] )[0];

			if ( $major !== $major_part ) {
				$major = $major_part;
				++$major_version_changes;
			}

			if ( $minor !== $minor_part ) {
				$minor = $minor_part;
				++$minor_version_changes;
			}

			if ( self::MINOR_VERSION_COUNT >= $minor_version_changes || 1 === $major_version_changes ) {
				$results[ $number ] = $versions[ $number ];
			}

			if ( self::MINOR_VERSION_COUNT > $major_version_changes && 1 === $major_version_changes ) {
				break;
			}
		}

		return [
			'versions' => $results,
			'order'    => array_keys( $results ),
		];
	}

	/**
	 * Get all version from the info API
	 *
	 * @return array|\WP_Error
	 */
	private function get_advanced_ads_info() {
		$aa_info = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.0/advanced-ads.json' );

		if ( is_wp_error( $aa_info ) ) {
			return $aa_info;
		}

		$info = json_decode( wp_remote_retrieve_body( $aa_info ), true );

		if ( $info['versions'] ) {
			return $info;
		}

		// Likely a change in the WP info API.
		return new \WP_Error( 404, __( 'Plugin info not found', 'advanced-ads' ) );
	}
}
