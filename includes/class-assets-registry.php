<?php
/**
 * Assets registry handles the registration of stylesheets and scripts required for plugin functionality.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Assets Registry.
 */
class Assets_Registry implements Integration_Interface {

	/**
	 * Enqueue stylesheet
	 *
	 * @param string $handle Name of the stylesheet.
	 *
	 * @return void
	 */
	public static function enqueue_style( $handle ): void {
		wp_enqueue_style( self::prefix_it( $handle ) );
	}

	/**
	 * Enqueue script
	 *
	 * @param string $handle Name of the script.
	 *
	 * @return void
	 */
	public static function enqueue_script( $handle ): void {
		wp_enqueue_script( self::prefix_it( $handle ) );
	}

	/**
	 * Prefix the handle
	 *
	 * @param string $handle Name of the asset.
	 *
	 * @return string
	 */
	public static function prefix_it( $handle ): string {
		return ADVADS_SLUG . '-' . $handle;
	}

	/**
	 * Determines whether a script has been added to the queue.
	 *
	 * @param string $handle Name of the script.
	 * @param string $status Optional. Status of the script to check. Default 'enqueued'.
	 *                       Accepts 'enqueued', 'registered', 'queue', 'to_do', and 'done'.
	 *
	 * @return bool
	 */
	public static function script_is( $handle, $status = 'enqueued' ): bool {
		return wp_script_is( self::prefix_it( $handle ), $status );
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ], 0 );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ], 0 );
	}

	/**
	 * Register assets
	 *
	 * @return void
	 */
	public function register_assets(): void {
		$this->register_styles();
		$this->register_scripts();
	}

	/**
	 * Register styles
	 *
	 * @return void
	 */
	public function register_styles(): void {
		$this->register_style( 'ui', 'admin/assets/css/ui.css' );
		$this->register_style( 'admin', 'admin/assets/css/admin.css' );
		$this->register_style( 'ad-positioning', 'modules/ad-positioning/assets/css/ad-positioning.css', [ self::prefix_it( 'admin' ) ] );

		// New CSS files.
		$this->register_style( 'common', 'assets/css/admin/common.css' );
		$this->register_style( 'screen-ads-editing', 'assets/css/admin/screen-ads-editing.css' );
		$this->register_style( 'screen-ads-listing', 'assets/css/admin/screen-ads-listing.css', [] );
		$this->register_style( 'screen-dashboard', 'assets/css/admin/screen-dashboard.css', [ self::prefix_it( 'common' ) ] );
		$this->register_style( 'screen-groups-listing', 'assets/css/admin/screen-groups-listing.css' );
		$this->register_style( 'screen-onboarding', 'assets/css/admin/screen-onboarding.css' );
		$this->register_style( 'screen-placements-listing', 'assets/css/admin/screen-placements-listing.css' );
		$this->register_style( 'screen-settings', 'assets/css/admin/screen-settings.css' );
		$this->register_style( 'screen-tools', 'assets/css/admin/screen-status.css', [ self::prefix_it( 'common' ) ] );
		$this->register_style( 'wp-dashboard', 'assets/css/admin/wp-dashboard.css' );
	}

	/**
	 * Register scripts
	 *
	 * @return void
	 */
	public function register_scripts(): void {
		$this->register_script( 'admin-global', 'admin/assets/js/admin-global.js', [ 'jquery' ], false, true );
		$this->register_script( 'find-adblocker', 'admin/assets/js/advertisement.js' );
		$this->register_script( 'ui', 'admin/assets/js/ui.js', [ 'jquery' ] );
		$this->register_script( 'conditions', 'admin/assets/js/conditions.js', [ 'jquery', self::prefix_it( 'ui' ) ] );
		$this->register_script( 'wizard', 'admin/assets/js/wizard.js', [ 'jquery' ] );
		$this->register_script( 'inline-edit-group-ads', 'admin/assets/js/inline-edit-group-ads.js', [ 'jquery' ], false, false );
		$this->register_script( 'ad-positioning', '/modules/ad-positioning/assets/js/ad-positioning.js', [], false, true );
		$this->register_script( 'admin', 'admin/assets/js/admin.min.js', [ 'jquery', self::prefix_it( 'ui' ), 'jquery-ui-autocomplete', 'wp-util' ], false, false );
		$this->register_script( 'groups', 'admin/assets/js/groups.js', [ 'jquery' ], false, true );
		$this->register_script( 'adblocker-image-data', 'admin/assets/js/adblocker-image-data.js', [ 'jquery' ] );

		// New JS files.
		$this->register_script( 'admin-common', 'assets/js/admin/admin-common.js', [ 'jquery' ], false, true );
		$this->register_script( 'screen-ads-listing', 'assets/js/admin/screen-ads-listing.js', [ 'jquery', 'inline-edit-post', 'wp-util', 'wp-api-fetch' ], false, true );
		$this->register_script( 'screen-ads-editing', 'assets/js/admin/screen-ads-editing.js', [], false, true );
		$this->register_script( 'screen-dashboard', 'assets/js/admin/screen-dashboard.js', [], false, true );
		$this->register_script( 'screen-groups-listing', 'assets/js/admin/screen-groups-listing.js', [ 'wp-api-fetch' ], false, true );
		$this->register_script( 'screen-placements-listing', 'assets/js/admin/screen-placements-listing.js', [ 'wp-util', 'wp-api-fetch', self::prefix_it( 'admin-global' ) ], false, true );
		$this->register_script( 'screen-settings', 'assets/js/admin/screen-settings.js', [], false, true );
		$this->register_script( 'screen-tools', 'assets/js/admin/screen-tools.js', [], false, true );
		$this->register_script( 'wp-dashboard', 'assets/js/admin/wp-dashboard.js', [ 'jquery' ], false, true );
		$onboarding_deps = [
			'jquery',
			'lodash',
			'moment',
			'wp-data',
			'wp-compose',
			'wp-components',
			'wp-api-fetch',
		];
		$this->register_script( 'screen-onboarding', 'assets/js/screen-onboarding.js', $onboarding_deps, false, true );
	}

	/**
	 * Register stylesheet
	 *
	 * @param string           $handle Name of the stylesheet. Should be unique.
	 * @param string|bool      $src    URL of the stylesheet.
	 * @param string[]         $deps   Optional. An array of registered stylesheet handles this stylesheet depends on.
	 * @param string|bool|null $ver    Optional. String specifying stylesheet version number.
	 * @param string           $media  Optional. The media for which this stylesheet has been defined.
	 *
	 * @return void
	 */
	private function register_style( $handle, $src, $deps = [], $ver = false, $media = 'all' ) {
		if ( false === $ver ) {
			$ver = ADVADS_VERSION;
		}

		wp_register_style( self::prefix_it( $handle ), ADVADS_BASE_URL . $src, $deps, $ver, $media );
	}

	/**
	 * Register script
	 *
	 * @param string           $handle    Name of the stylesheet. Should be unique.
	 * @param string|bool      $src       URL of the stylesheet.
	 * @param string[]         $deps      Optional. An array of registered stylesheet handles this stylesheet depends on.
	 * @param string|bool|null $ver       Optional. String specifying stylesheet version number.
	 * @param bool             $in_footer Optional. The media for which this stylesheet has been defined.
	 *
	 * @return void
	 */
	private function register_script( $handle, $src, $deps = [], $ver = false, $in_footer = false ) {
		if ( false === $ver ) {
			$ver = ADVADS_VERSION;
		}

		$new_src = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? $src : str_replace( '.js', '.min.js', $src );
		wp_register_script( self::prefix_it( $handle ), ADVADS_BASE_URL . $src, $deps, $ver, $in_footer );
	}
}
