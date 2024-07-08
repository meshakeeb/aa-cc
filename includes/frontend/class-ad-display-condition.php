<?php
/**
 * This class is responsible for handling the display conditions of ads.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Frontend;

use Advanced_Ads_Utils;
use AdvancedAds\Options;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Str;
use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Page display condition class.
 */
class Ad_Display_Condition {
	/**
	 * Identifier that supplement the disabled reason.
	 *
	 * @var int|string
	 */
	public $disabled_id = '';

	/**
	 * Reason why are disabled.
	 *
	 * @var string
	 */
	public $disabled_reason = '';

	/**
	 * Check if ads are disabled.
	 *
	 * @return bool
	 */
	public function is_ad_disabled(): bool {
		return defined( 'ADVADS_ADS_DISABLED' ) && ADVADS_ADS_DISABLED;
	}

	/**
	 * Get the disabled id.
	 *
	 * @return int|string
	 */
	public function get_disabled_id() {
		return $this->disabled_id;
	}

	/**
	 * Get the reason why ads are disabled.
	 *
	 * @return string
	 */
	public function get_disabled_reason(): string {
		return $this->disabled_reason;
	}

	/**
	 * Check if the current user is a bot prepopulating the cache
	 * Ads should be loaded for the bot, because they should show up on the cached site
	 *
	 * @return bool
	 */
	public function is_cache_bot(): bool {
		$user_agent = Params::server( 'HTTP_USER_AGENT', '' );
		if ( '' !== $user_agent ) {
			$current = sanitize_text_field( wp_unslash( $user_agent ) );

			// WP Rocket.
			if ( Str::contains( 'wprocketbot', $current ) ) {
				return true;
			}

			// WP Super Cache.
			$wp_useragent = apply_filters( 'http_headers_useragent', 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) );
			if ( $current === $wp_useragent ) {
				return true;
			}

			// LiteSpeed Cache: `lscache_runner` and `lscache_walker` user agents.
			if ( Str::contains( 'lscache_', $current ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Runs checks and set disabled constants accordingly.
	 *
	 * @return void
	 */
	public function run_checks(): void {
		global $post, $wp_the_query;

		// Early bail!!
		if ( defined( 'ADVADS_ADS_DISABLED' ) ) {
			return;
		}

		$options = Options::instance()->get( 'advanced-ads' );
		$checks  = [
			// Check if ads are disabled completely.
			'all'          => [
				'check' => ! empty( $options['disabled-ads']['all'] ),
				'args'  => [ 'all' ],
			],
			// Check if ads are disabled in REST API.
			'rest'         => [
				'check' => defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $options['disabled-ads']['rest-api'] ),
				'args'  => [ 'rest-api' ],
			],
			// Check if ads are disabled from 404 pages.
			'error404'     => [
				'check' => $wp_the_query->is_404() && ! empty( $options['disabled-ads']['404'] ),
				'args'  => [ '404' ],
			],
			// Check if ads are disabled from non singular pages (often = archives).
			'archive'      => [
				'check' => ! $wp_the_query->is_singular() && ! empty( $options['disabled-ads']['archives'] ),
				'args'  => [ 'archive' ],
			],
			// Check if ads are disabled in Feed.
			'feed'         => [
				'check' => $wp_the_query->is_feed() && ( $options['disabled-ads']['feed'] ?? false ),
				'args'  => [ 'feed' ],
			],
			'current_page' => [ $this, 'check_current_page' ],
			'posts_page'   => [ $this, 'check_posts_page' ],
			'shop'         => [ $this, 'check_shop' ],
			'user_roles'   => [ $this, 'check_user_roles' ],
			'bots'         => [ $this, 'check_bots' ],
			'ip_addresses' => [ $this, 'check_ip_addresses' ],
		];

		foreach ( $checks as $check ) {
			if ( isset( $check['check'] ) && $check['check'] ) {
				$this->disable_ads( ...$check['args'] );
				return;
			}

			if ( is_callable( $check ) ) {
				$truthness = call_user_func( $check );
				if ( $truthness ) {
					return;
				}
			}
		}
	}

	/**
	 * Check if ads are disabled on the current page.
	 *
	 * @return bool
	 */
	private function check_current_page(): bool {
		global $post, $wp_the_query;

		if ( $wp_the_query->is_singular() && isset( $post->ID ) ) {
			$settings = get_post_meta( $post->ID, '_advads_ad_settings', true );

			if ( ! empty( $settings['disable_ads'] ) ) {
				$this->disable_ads( 'page', $post->ID );
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if ads are disabled on "Posts page" set on the WordPress Reading settings page.
	 *
	 * @return bool
	 */
	private function check_posts_page(): bool {
		global $wp_the_query;

		if ( $wp_the_query->is_posts_page ) {
			$settings = get_post_meta( $wp_the_query->queried_object_id, '_advads_ad_settings', true );

			if ( ! empty( $settings['disable_ads'] ) ) {
				$this->disable_ads( 'page', $wp_the_query->queried_object_id );
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if ads are disabled on WooCommerce shop page (and currently on shop page).
	 *
	 * @return bool
	 */
	private function check_shop(): bool {
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			$shop_id  = wc_get_page_id( 'shop' );
			$settings = get_post_meta( $shop_id, '_advads_ad_settings', true );
			if ( ! empty( $settings['disable_ads'] ) ) {
				$this->disable_ads( 'page', $shop_id );
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if ads are disabled for current user role.
	 *
	 * @return bool
	 */
	private function check_user_roles(): bool {
		$options        = Options::instance()->get( 'advanced-ads' );
		$current_user   = wp_get_current_user();
		$hide_for_roles = isset( $options['hide-for-user-role'] )
			? Advanced_Ads_Utils::maybe_translate_cap_to_role( $options['hide-for-user-role'] )
			: [];

		if (
			$hide_for_roles && is_user_logged_in() && is_array( $current_user->roles ) &&
			array_intersect( $hide_for_roles, $current_user->roles )
		) {
			$this->disable_ads( 'user-role' );
			return true;
		}

		return false;
	}

	/**
	 * Check if ads are disabled for bots.
	 *
	 * @return bool
	 */
	private function check_bots(): bool {
		$options = Options::instance()->get( 'advanced-ads' );
		if (
			isset( $options['block-bots'] ) && $options['block-bots'] &&
			! $this->is_cache_bot() && Conditional::is_ua_bot()
		) {
			$this->disable_ads();
			return true;
		}

		return false;
	}

	/**
	 * Check if ads are disabled for IP addresses.
	 *
	 * @return bool
	 */
	private function check_ip_addresses(): bool {
		$options = Options::instance()->get( 'advanced-ads' );

		if ( isset( $options['hide-for-ip-address']['enabled'] ) ) {
			$ip_addresses = isset( $options['hide-for-ip-address']['ips'] )
				? explode( "\n", $options['hide-for-ip-address']['ips'] )
				: [];
			$ip_addresses = array_map( 'trim', $ip_addresses );
			$user_ip      = get_user_ip_address();

			if ( $user_ip && ! empty( $ip_addresses ) && in_array( $user_ip, $ip_addresses, true ) ) {
				$this->disable_ads( 'ip-address' );
				return true;
			}
		}

		return false;
	}

	/**
	 * Disable ads.
	 *
	 * @param string     $reason Reason why are disabled.
	 * @param int|string $id Identifier that supplement the disabled reason.
	 */
	private function disable_ads( $reason = null, $id = null ) {
		$this->disabled_id     = $id;
		$this->disabled_reason = $reason;
		define( 'ADVADS_ADS_DISABLED', true );
	}
}
