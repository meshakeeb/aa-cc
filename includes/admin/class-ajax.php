<?php
/**
 * AJAX Ads
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use Advanced_Ads;
use Advanced_Ads_Ad_Health_Notices;
use Advanced_Ads_Privacy;
use Advanced_Ads_Pro;
use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Framework\Interfaces\Integration_Interface;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Frontend\Stats;
use AdvancedAds\Utilities\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend AJAX.
 */
class AJAX implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'wp_ajax_advads_ad_select', [ $this, 'ad_select' ] );
		add_action( 'wp_ajax_nopriv_advads_ad_select', [ $this, 'ad_select' ] );
		add_action( 'wp_ajax_advads-ad-health-notice-push', [ $this, 'ad_health_notice_push' ] );
		add_action( 'wp_ajax_nopriv_advads-ad-health-notice-push', [ $this, 'ad_health_notice_push' ] );

		add_action( 'wp_ajax_advads_dismiss_welcome', [ $this, 'dismiss_welcome' ] );
	}

	/**
	 * Stop showing the welcome after a click on the dismiss icon
	 *
	 * @return void
	 */
	public function dismiss_welcome() {
		Welcome::get()->dismiss();
		wp_send_json_success( 'OK', 200 );
	}

	/**
	 * Simple wp ajax interface for ad selection.
	 */
	public function ad_select() {
		add_filter( 'advanced-ads-output-inline-css', '__return_false' );

		// Allow modules / add-ons to test (this is rather late but should happen before anything important is called).
		do_action( 'advanced-ads-ajax-ad-select-init' );

		$ad_ids      = Params::request( 'ad_ids', [], FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );
		$defered_ads = Params::request( 'deferedAds', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( is_array( $ad_ids ) ) {
			foreach ( $ad_ids as $ad_id ) {
				Stats::get()->add_entity( 'ad', $ad_id, '' );
			}
		}

		if ( $defered_ads ) {
			$response = [];

			$requests_by_blog = [];
			foreach ( $defered_ads as $request ) {
				$blog_id                        = $request['blog_id'] ?? get_current_blog_id();
				$requests_by_blog[ $blog_id ][] = $request;
			}

			foreach ( $requests_by_blog as $blog_id => $requests ) {
				if ( get_current_blog_id() !== $blog_id && is_multisite() ) {
					switch_to_blog( $blog_id );
				}

				foreach ( $requests as $request ) {
					$result              = $this->select_one( $request );
					$result['elementId'] = $request['elementId'] ?? null;
					$response[]          = $result;
				}

				if ( get_current_blog_id() !== $blog_id && is_multisite() ) {
					restore_current_blog();
				}
			}

			wp_send_json( $response );
		}

		$response = $this->select_one( $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_send_json( $response );
	}

	/**
	 * Push an Ad Health notice to the queue in the backend
	 */
	public function ad_health_notice_push() {

		check_ajax_referer( 'advanced-ads-ad-health-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		$key  = ! empty( $_REQUEST['key'] ) ? esc_attr( Params::request( 'key' ) ) : false;
		$attr = Params::request( 'attr', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		// Update or new entry?
		if ( isset( $attr['mode'] ) && 'update' === $attr['mode'] ) {
			Advanced_Ads_Ad_Health_Notices::get_instance()->update( $key, $attr );
		} else {
			Advanced_Ads_Ad_Health_Notices::get_instance()->add( $key, $attr );
		}

		die();
	}

	/**
	 * Check if AJAX ad can be displayed, with consent information sent in request.
	 *
	 * @param bool $can_display Whether this ad can be displayed.
	 * @param Ad   $ad          The ad object.
	 *
	 * @return bool
	 */
	public function can_display_by_consent( $can_display, $ad ) {
		// Early bail!!
		if ( ! $can_display ) {
			return $can_display;
		}

		// If consent is overridden for the ad.
		$privacy_props = $ad->get_prop( 'privacy' );
		if ( ! empty( $privacy_props['ignore-consent'] ) ) {
			return true;
		}

		// If privacy module is not active, we can display.
		if ( empty( Advanced_Ads_Privacy::get_instance()->options()['enabled'] ) ) {
			return true;
		}

		$consent_state = Params::request( 'consent', 'not_allowed' );

		// Consent is either given or not needed.
		if ( in_array( $consent_state, [ 'not_needed', 'accepted' ], true ) ) {
			return true;
		}

		// If there is custom code, don't display the ad (unless it's a group).
		if (
			class_exists( 'Advanced_Ads_Pro' ) &&
			! empty( Advanced_Ads_Pro::get_instance()->get_custom_code( $ad ) ) &&
			! $ad->is_type( 'group' )
		) {
			return false;
		}

		// See if this ad type needs consent.
		return ! Advanced_Ads_Privacy::get_instance()->ad_type_needs_consent( $ad->get_type() );
	}

	/**
	 * Provides a single ad (ad, group, placement) given ID and selection method.
	 *
	 * @param array $request Request.
	 *
	 * @return array
	 */
	private function select_one( $request ) {
		$method    = (string) $request['ad_method'] ?? null;
		$function  = "get_the_$method";
		$id        = (string) $request['ad_id'] ?? null;
		$arguments = $request['ad_args'] ?? [];

		if ( is_string( $arguments ) ) {
			$arguments = stripslashes( $arguments );
			$arguments = json_decode( $arguments, true );
		}

		if ( ! empty( $request['elementId'] ) ) {
			$arguments['cache_busting_elementid'] = $request['elementId'];
		}

		// Report error.
		if ( empty( $id ) || ! function_exists( $function ) ) {
			return [
				'status'  => 'error',
				'message' => 'No valid ID or METHOD found.',
			];
		}

		/**
		 * Filters the received arguments before passing them to to ads/groups/placements.
		 *
		 * @param array $arguments Existing arguments.
		 * @param array $request Request data.
		 */
		$arguments    = apply_filters( 'advanced-ads-ajax-ad-select-arguments', $arguments, $request );
		$advads       = Advanced_Ads::get_instance();
		$previous_ads = $advads->current_ads;
		add_filter( 'advanced-ads-can-display-ad', [ $this, 'can_display_by_consent' ], 10, 2 );
		$content = $function( (int) $id, '', $arguments );

		if ( empty( $content ) ) {
			return [
				'status'  => 'error',
				'message' => 'No displayable ad found for privacy settings.',
			];
		}

		$response = [
			'status'  => 'success',
			'item'    => $content,
			'id'      => $id,
			'method'  => $method,
			'ads'     => array_slice( $advads->current_ads, count( $previous_ads ) ),
			'blog_id' => get_current_blog_id(),
		];

		return apply_filters(
			'advanced-ads-cache-busting-item',
			$response,
			[
				'id'     => $id,
				'method' => $method,
				'args'   => $arguments,
			]
		);
	}
}
