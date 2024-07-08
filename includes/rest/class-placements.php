<?php
/**
 * Rest Forms.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Rest;

use AdvancedAds\Constants;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Interfaces\Routes_Interface;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * Placement screen modal forms handling
 */
class Placements implements Routes_Interface {
	/**
	 * Registers routes with WordPress.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			Constants::REST_BASE,
			'/placement',
			[

				[
					'methods'             => \WP_REST_Server::ALLMETHODS,
					'callback'            => [ $this, 'call_endpoint' ],
					'permission_callback' => function () {
						return Conditional::user_can( 'advanced_ads_manage_placements' );
					},
				],
			]
		);
	}

	/**
	 * Call the appropriate endpoint handler
	 *
	 * @param WP_REST_Request $request the request object.
	 *
	 * @return array
	 */
	public function call_endpoint( $request ) {
		switch ( $request->get_method() ) {
			case 'POST':
				return $this->create( $request );
			case 'PUT':
				return $this->update( $request );
			default:
		}

		return [ 'error' => __( 'No endpoint found', 'advanced-ads' ) ];
	}

	/**
	 * Create placement
	 *
	 * @param WP_REST_Request $request the request object.
	 *
	 * @return mixed
	 */
	public function create( $request ) {
		$body = json_decode( $request->get_body(), JSON_UNESCAPED_UNICODE );
		parse_str( $body['fields'], $payload );

		if ( ! wp_verify_nonce( sanitize_key( $payload['nonce'] ), 'advads-create-placement' ) ) {
			return [ 'error' => __( 'Not authorized create', 'advanced-ads' ) ];
		}

		$placement_data = wp_unslash( $payload['advads'] );

		if ( ! isset( $placement_data['placement'] ) ) {
			return [ 'error' => __( 'No placement data provided', 'advanced-ads' ) ];
		}

		$placement_data = wp_unslash( $placement_data['placement'] );
		$placement      = wp_advads_create_new_placement( $placement_data['type'] ?? 'default' );
		$placement->set_props( $placement_data );
		$placement->save();

		return apply_filters(
			'advanced-ads-placements-updated',
			[
				'action'         => 'create',
				'placement_data' => $placement->get_data(),
				'reload'         => true,
			],
			$placement
		);
	}

	/**
	 * Update placement
	 *
	 * @param WP_REST_Request $request the request object.
	 *
	 * @return mixed
	 */
	public function update( $request ) {
		$body = json_decode( $request->get_body(), JSON_UNESCAPED_UNICODE );
		parse_str( $body['fields'], $payload );

		if ( ! wp_verify_nonce( sanitize_key( $payload['nonce'] ), 'advads-update-placement' ) ) {
			return [ 'error' => __( 'Not authorized update', 'advanced-ads' ) ];
		}

		$placement = wp_advads_get_placement( (int) $payload['post_ID'] );

		if ( ! $placement ) {
			return [ 'error' => __( 'Placement not found', 'advanced-ads' ) ];
		}

		$placement->set_title( sanitize_text_field( $payload['post_title'] ) );

		if ( isset( $payload['advads']['placements'] ) ) {
			$placement_data = wp_unslash( $payload['advads']['placements'] );
			$placement->set_item( $placement_data['item'] );
			$placement->set_props( $placement_data['options'] );
		}

		$placement->save();

		// Allow add-ons trigger a page refresh or show errors if needed.
		return apply_filters(
			'advanced-ads-placements-updated',
			[
				'action'         => 'update',
				'placement_data' => $placement->get_data(),
				'reload'         => false,
			],
			$placement
		);
	}
}
