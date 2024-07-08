<?php

/**
 * Class Advanced_Ads_Adblock_Finder_Admin
 */
class Advanced_Ads_Adblock_Finder_Admin {
	/**
	 * Advanced_Ads_Adblock_Finder_Admin constructor.
	 */
	public function __construct() {
		// Add module settings to Advanced Ads settings page.
		add_action( 'advanced-ads-settings-init', [ $this, 'settings_init' ], 9, 1 );
	}

	/**
	 * Add settings to settings page.
	 *
	 * @param string $hook Settings page hook.
	 */
	public function settings_init( $hook ) {
		add_settings_section(
			'advanced_ads_adblocker_setting_section',
			__( 'Ad Blocker', 'advanced-ads' ),
			[ $this, 'render_settings_section_callback' ],
			ADVADS_SETTINGS_ADBLOCKER
		);

		add_settings_field(
			'GA-tracking-id',
			__( 'Ad blocker counter', 'advanced-ads' ),
			[ $this, 'render_settings_ga' ],
			ADVADS_SETTINGS_ADBLOCKER,
			'advanced_ads_adblocker_setting_section'
		);
	}

	public function render_settings_section_callback() {}

	/**
	 * Render input for the Google Analytics Tracking ID.
	 */
	public function render_settings_ga() {
		$options = Advanced_Ads::get_instance()->get_adblocker_options();
		$ga_uid  = isset( $options['ga-UID'] ) ? $options['ga-UID'] : '';

		include_once __DIR__ . '/views/setting-ga.php';
	}
}
