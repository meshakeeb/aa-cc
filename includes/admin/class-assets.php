<?php
/**
 * Assets manages the enqueuing of styles and scripts for the administration area.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Constants;
use Advanced_Ads_AdSense_Admin;
use AdvancedAds\Assets_Registry;
use Advanced_Ads_Display_Conditions;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Assets.
 */
class Assets implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'current_screen' ], 10, 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ], 10, 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 9, 0 );
	}

	/**
	 * Enqueue styles and scripts for current screen
	 *
	 * @return void
	 */
	public function current_screen(): void {
		$screens   = wp_advads()->screens->get_screens();
		$wp_screen = get_current_screen();

		foreach ( $screens as $screen ) {
			if ( $wp_screen->id === $screen->get_hook() ) {
				$screen->enqueue_assets();
			}
		}
	}

	/**
	 * Enqueue styles
	 *
	 * @return void
	 */
	public function enqueue_styles(): void {
		$wp_screen = get_current_screen();

		if ( 'post' === $wp_screen->base && Constants::POST_TYPE_AD === $wp_screen->post_type ) {
			Assets_Registry::enqueue_script( 'screen-ads-editing' );
		}

		// TODO: made them load conditionaly.
		if ( 'dashboard' !== $wp_screen->id ) {
			Assets_Registry::enqueue_style( 'ui' );
			Assets_Registry::enqueue_style( 'admin' );
		}

		if ( 'post' === $wp_screen->base && Constants::POST_TYPE_AD === $wp_screen->post_type ) {
			Assets_Registry::enqueue_style( 'ad-positioning' );
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		global $post;

		$screen = get_current_screen();
		$this->enqueue_endpoints();
		$this->enqueue_site_info();

		// TODO: add conditional loading.
		Assets_Registry::enqueue_script( 'admin-global' );
		Assets_Registry::enqueue_script( 'find-adblocker' );

		$params = [
			'ajax_nonce'           => wp_create_nonce( 'advanced-ads-admin-ajax-nonce' ),
			'create_ad_url'        => esc_url( admin_url( 'post-new.php?post_type=advanced_ads' ) ),
			'create_your_first_ad' => __( 'Create your first ad', 'advanced-ads' ),
		];
		wp_advads_json_add( $params, 'advadsglobal' );

		// TODO: remove later start using global data variable.
		wp_advads_json_add( 'ajax_nonce', wp_create_nonce( 'advanced-ads-admin-ajax-nonce' ), 'advadsglobal' );

		if ( Conditional::is_screen_advanced_ads() ) {
			Assets_Registry::enqueue_script( 'admin' );
			Assets_Registry::enqueue_script( 'conditions' );
			Assets_Registry::enqueue_script( 'wizard' );
			Assets_Registry::enqueue_script( 'adblocker-image-data' );

			$translation_array = [
				'condition_or'                  => __( 'or', 'advanced-ads' ),
				'condition_and'                 => __( 'and', 'advanced-ads' ),
				'after_paragraph_promt'         => __( 'After which paragraph?', 'advanced-ads' ),
				'page_level_ads_enabled'        => Advanced_Ads_AdSense_Admin::get_auto_ads_messages()['enabled'],
				'today'                         => __( 'Today', 'advanced-ads' ),
				'yesterday'                     => __( 'Yesterday', 'advanced-ads' ),
				'this_month'                    => __( 'This Month', 'advanced-ads' ),
				/* translators: 1: The number of days. */
				'last_n_days'                   => __( 'Last %1$d days', 'advanced-ads' ),
				/* translators: 1: An error message. */
				'error_message'                 => __( 'An error occurred: %1$s', 'advanced-ads' ),
				'all'                           => __( 'All', 'advanced-ads' ),
				'no_results'                    => __( 'There were no results returned for this ad. Please make sure it is active, generating impressions and double check your ad parameters.', 'advanced-ads' ),
				'show_inactive_ads'             => __( 'Show inactive ads', 'advanced-ads' ),
				'hide_inactive_ads'             => __( 'Hide inactive ads', 'advanced-ads' ),
				'display_conditions_form_name'  => Advanced_Ads_Display_Conditions::FORM_NAME, // not meant for translation.
				'delete_placement_confirmation' => __( 'Permanently delete this placement?', 'advanced-ads' ),
				'close'                         => __( 'Close', 'advanced-ads' ),
				'close_save'                    => __( 'Close and save', 'advanced-ads' ),
				'save_new_placement'            => __( 'Save new placement', 'advanced-ads' ),
				'confirmation'                  => __( 'Data you have entered has not been saved. Are you sure you want to discard your changes?', 'advanced-ads' ),
				'admin_page'                    => $screen->id,
				'placements_allowed_ads'        => [
					'action' => 'advads-placements-allowed-ads',
					'nonce'  => wp_create_nonce( 'advads-placements-allowed-ads' ),
				],
				'group_forms'                   => [
					'save'         => __( 'Save', 'advanced-ads' ),
					'save_new'     => __( 'Save New Group', 'advanced-ads' ),
					// translators: an ad group title.
					'confirmation' => __( 'You are about to permanently delete %s', 'advanced-ads' ),
				],
			];

			// TODO: remove later start using global data variable.
			wp_advads_json_add( $translation_array, 'advadstxt' );
		}

		if ( Constants::POST_TYPE_AD === $screen->id ) {
			wp_enqueue_media( [ 'post' => $post ] );
		}

		// Ad edit screen.
		if ( 'post' === $screen->base && Constants::POST_TYPE_AD === $screen->post_type ) {
			Assets_Registry::enqueue_script( 'ad-positioning' );
		}
	}

	/**
	 * Global variables: advancedAds
	 */
	private function enqueue_site_info() {
		$endpoints = [
			'blog_id'  => get_current_blog_id(),
			'home_url' => get_home_url(),
		];

		wp_advads_json_add( 'siteInfo', $endpoints );
	}

	/**
	 * Global variables: advancedAds
	 */
	private function enqueue_endpoints() {
		$endpoints = [
			'adminUrl'  => esc_url( admin_url( '/' ) ),
			'ajaxUrl'   => esc_url( admin_url( 'admin-ajax.php' ) ),
			'assetsUrl' => esc_url( ADVADS_BASE_URL ),
			'editAd'    => esc_url( admin_url( 'post.php?action=edit&post=' ) ),
		];

		wp_advads_json_add( 'endpoints', $endpoints );
	}
}
