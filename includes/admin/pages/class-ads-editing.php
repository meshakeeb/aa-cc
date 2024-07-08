<?php
/**
 * Ads edit screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use AdvancedAds\Constants;
use AdvancedAds\Assets_Registry;

defined( 'ABSPATH' ) || exit;

/**
 * Ads.
 */
class Ads_Editing extends Ads {

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		$this->set_hook( Constants::POST_TYPE_AD );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		$wp_screen = get_current_screen();
		if ( 'post' === $wp_screen->base && 'add' !== $wp_screen->action ) {
			add_action( 'advanced-ads-admin-header-actions', [ $this, 'add_new_ad_button' ] );
		}

		if ( 'post' === $wp_screen->base && Constants::POST_TYPE_AD === $wp_screen->post_type ) {
			Assets_Registry::enqueue_script( 'screen-ads-editing' );
			Assets_Registry::enqueue_style( 'screen-ads-editing' );
		}
	}

	/**
	 * Define header args.
	 *
	 * @return array
	 */
	public function define_header_args(): array {
		return [
			'manual_url'         => 'https://wpadvancedads.com/manual/first-ad/',
			'show_filter_button' => false,
		];
	}
}
