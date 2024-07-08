<?php
/**
 * Admin Screen Options.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

use WP_Screen;
use AdvancedAds\Framework\Interfaces\Integration_Interface;
use AdvancedAds\Utilities\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Screen Options.
 */
class Screen_Options implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'screen_settings', [ $this, 'add_screen_options' ], 10, 2 );
		add_action( 'wp_loaded', [ $this, 'save_screen_options' ] );
		add_action( 'load-edit.php', [ $this, 'set_screen_options' ] );
	}

	/**
	 * Return true if the current screen is the ad or placement list.
	 *
	 * @return bool
	 */
	private function is_screen(): bool {
		return Conditional::is_screen( [ 'edit-advanced_ads', 'edit-advanced_ads_plcmnt' ] );
	}

	/**
	 * Register custom screen options on the ad overview page.
	 *
	 * @param string    $options Screen options HTML.
	 * @param WP_Screen $screen  Screen object.
	 *
	 * @return string
	 */
	public function add_screen_options( $options, WP_Screen $screen ) {
		if ( ! $this->is_screen() ) {
			return $options;
		}

		$show_filters = boolval( $screen->get_option( 'show-filters' ) );

		// If the default WordPress screen options don't exist, we have to force the submit button to show.
		add_filter( 'screen_options_show_submit', '__return_true' );

		ob_start();
		require ADVADS_ABSPATH . 'views/admin/screen-options.php';

		return $options . ob_get_clean();
	}

	/**
	 * Add the screen options to the WP_Screen options
	 *
	 * @return void
	 */
	public function set_screen_options(): void {
		if ( ! $this->is_screen() ) {
			return;
		}

		$screen_options = get_user_meta( get_current_user_id(), 'advanced-ads-ad-list-screen-options', true );
		if ( ! is_array( $screen_options ) ) {
			return;
		}

		foreach ( $screen_options as $option_name => $value ) {
			add_screen_option( $option_name, $value );
		}
	}

	/**
	 * Save the screen option setting.
	 *
	 * @return void
	 */
	public function save_screen_options() {
		if ( ! isset( $_POST['advanced-ads-screen-options'] ) || ! is_array( $_POST['advanced-ads-screen-options'] ) ) {
			return;
		}

		check_admin_referer( 'screen-options-nonce', 'screenoptionnonce' );

		$user = wp_get_current_user();

		if ( ! $user ) {
			return;
		}

		update_user_meta(
			$user->ID,
			'advanced-ads-ad-list-screen-options',
			[ 'show-filters' => ! empty( $_POST['advanced-ads-screen-options']['show-filters'] ) ]
		);
	}
}
