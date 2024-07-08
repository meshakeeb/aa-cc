<?php
/**
 * The class is responsible for adding menu and submenu pages for the plugin in the WordPress admin area.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin;

use Advanced_Ads_Checks;
use Advanced_Ads_Ad_Health_Notices;
use AdvancedAds\Constants;
use AdvancedAds\Admin\Pages;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Admin Menu.
 */
class Admin_Menu implements Integration_Interface {

	/**
	 * Hold screens
	 *
	 * @var array
	 */
	private $screens = [];

	/**
	 * Hold screen hooks
	 *
	 * @var array
	 */
	private $screen_ids = null;

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_menu', [ $this, 'add_pages' ] );
		add_action( 'admin_head', [ $this, 'highlight_menu_item' ] );
		add_filter( 'admin_body_class', [ $this, 'add_body_class' ] );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_pages(): void {
		foreach ( $this->get_screens() as $renderer ) {
			$renderer->register_screen();
		}

		$this->register_forward_links();

		/**
		 * Allows extensions to insert sub menu pages.
		 *
		 * @since untagged Added the `$hidden_page_slug` parameter.
		 *
		 * @param string $plugin_slug      The slug slug used to add a visible page.
		 * @param string $hidden_page_slug The slug slug used to add a hidden page.
		 */
		do_action( 'advanced-ads-submenu-pages', ADVADS_SLUG, 'advanced_ads_hidden_page_slug' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
	}

	/**
	 * Register forward links
	 *
	 * @return void
	 */
	private function register_forward_links(): void {
		global $submenu;

		$has_ads      = WordPress::get_count_ads();
		$notices      = Advanced_Ads_Ad_Health_Notices::get_number_of_notices();
		$notice_alert = '&nbsp;<span class="update-plugins count-' . $notices . '"><span class="update-count">' . $notices . '</span></span>';

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( current_user_can( Conditional::user_cap( 'advanced_ads_manage_options' ) ) ) {
			$submenu['advanced-ads'][] = [
				__( 'Support', 'advanced-ads' ),
				Conditional::user_cap( 'advanced_ads_manage_options' ),
				admin_url( 'admin.php?page=advanced-ads-settings#top#support' ),
				__( 'Support', 'advanced-ads' ),
			];

			if ( $has_ads ) {
				$submenu['advanced-ads'][0][0] .= $notice_alert;
			} else {
				$submenu['advanced-ads'][1][0] .= $notice_alert;
			}

			// Link to license tab if they are invalid.
			if ( Advanced_Ads_Checks::licenses_invalid() ) {
				$submenu['advanced-ads'][] = [
					__( 'Licenses', 'advanced-ads' )
						. '&nbsp;<span class="update-plugins count-1"><span class="update-count">!</span></span>',
					Conditional::user_cap( 'advanced_ads_manage_options' ),
					admin_url( 'admin.php?page=advanced-ads-settings#top#licenses' ),
					__( 'Licenses', 'advanced-ads' ),
				];
			}
		}
		// phpcs:enable
	}

	/**
	 * Get screens
	 *
	 * @return array
	 */
	public function get_screens(): array {
		if ( ! empty( $this->screens ) ) {
			return $this->screens;
		}

		$this->screens['dashboard']  = new Pages\Dashboard();
		$this->screens['ads']        = new Pages\Ads();
		$this->screens['groups']     = new Pages\Groups();
		$this->screens['placements'] = new Pages\Placements();
		$this->screens['settings']   = new Pages\Settings();
		$this->screens['tools']      = new Pages\Tools();
		$this->screens['onboarding'] = new Pages\Onboarding();

		return $this->screens;
	}

	/**
	 * Get screen ids
	 *
	 * @return array
	 */
	public function get_screen_ids(): array {
		if ( null !== $this->screen_ids ) {
			return $this->screen_ids;
		}

		$screens = $this->get_screens();

		foreach ( $screens as $screen ) {
			$this->screen_ids[] = $screen->get_hook();
		}

		return $this->screen_ids;
	}

	/**
	 * Highlights the 'Advanced Ads->Ads' item in the menu when an ad edit page is open
	 *
	 * @see the 'parent_file' and the 'submenu_file' filters for reference
	 *
	 * @return void
	 */
	public function highlight_menu_item(): void {
		global $parent_file, $submenu_file, $post_type;

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( Constants::POST_TYPE_AD === $post_type ) {
			$parent_file  = ADVADS_SLUG;
			$submenu_file = 'edit.php?post_type=' . Constants::POST_TYPE_AD;
		}
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Add a custom class to the body tag of Advanced Ads screens.
	 *
	 * @param string $classes Space-separated class list.
	 *
	 * @return string
	 */
	public function add_body_class( string $classes ): string {
		$screen_ids = $this->get_screen_ids();
		$wp_screen  = get_current_screen();

		if ( in_array( $wp_screen->id, $screen_ids, true ) ) {
			$classes .= ' advads-page';
		}

		return $classes;
	}
}
