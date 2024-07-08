<?php
/**
 * The class provides utility functions related to WordPress.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Utilities;

use DateTimeZone;
use Advanced_Ads;
use AdvancedAds\Constants;
use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Utilities WordPress.
 */
class WordPress {

	/**
	 * Debug function
	 *
	 * @return void
	 */
	public static function dd(): void {
		echo '<pre>';
		foreach ( func_get_args() as $arg ) {
			print_r( $arg ); // phpcs:ignore
		}
		echo '</pre>';
		die();
	}

	/**
	 * Function to calculate percentage
	 *
	 * @param int $part  Part of total.
	 * @param int $total Total value.
	 *
	 * @return string
	 */
	public static function calculate_percentage( $part, $total ): string {
		$percentage = ( $part / $total ) * 100;
		return number_format( $percentage, 2 ) . '%';
	}

	/**
	 * Get the current action selected from the bulk actions dropdown.
	 *
	 * @return string|false The action name or False if no action was selected
	 */
	public static function current_action() {
		$action = Params::request( 'action' );
		if ( '-1' !== $action ) {
			return sanitize_key( $action );
		}

		$action = Params::request( 'action2' );
		if ( '-1' !== $action ) {
			return sanitize_key( $action );
		}

		return false;
	}

	/**
	 * Get count of ads
	 *
	 * @param string $status Status need count for.
	 *
	 * @return int
	 */
	public static function get_count_ads( $status = 'any' ): int {
		$counts = (array) wp_count_posts( Constants::POST_TYPE_AD );

		if ( 'any' === $status ) {
			return array_sum( $counts );
		}

		return $counts[ $status ] ?? 0;
	}

	/**
	 * Get site domain
	 *
	 * @param string $part Part of domain.
	 *
	 * @return string
	 */
	public static function get_site_domain( $part = 'host' ): string {
		$domain = wp_parse_url( home_url( '/' ), PHP_URL_HOST );

		if ( 'name' === $part ) {
			$domain = explode( '.', $domain );
			$domain = count( $domain ) > 2 ? $domain[1] : $domain[0];
		}

		return $domain;
	}

	/**
	 * Get SVG content as string
	 *
	 * @param string $file   File name.
	 * @param string $folder Folder name if not default.
	 *
	 * @return string
	 */
	public static function get_svg( $file, $folder = '/assets/img/' ): string {
		$file_url = ADVADS_BASE_URL . $folder . $file;

		return file_get_contents( $file_url ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	/**
	 * Retrieves the timezone of the site as a DateTimeZone object.
	 *
	 * @return DateTimeZone
	 */
	public static function get_timezone(): DateTimeZone {
		static $advads_timezone;

		// Early bail!!
		if ( null !== $advads_timezone ) {
			return $advads_timezone;
		}

		if ( function_exists( 'wp_timezone' ) ) {
			$advads_timezone = wp_timezone();
			return $advads_timezone;
		}

		$date_time_zone = new DateTimeZone( self::get_timezone_string() );

		return $date_time_zone;
	}

	/**
	 * Retrieves the timezone of the site as a string.
	 *
	 * @return string
	 */
	public static function get_timezone_string(): string {
		$timezone_string = get_option( 'timezone_string' );

		if ( $timezone_string ) {
			return $timezone_string;
		}

		$offset  = (float) get_option( 'gmt_offset' );
		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );

		$sign     = ( $offset < 0 ) ? '-' : '+';
		$abs_hour = abs( $hours );
		$abs_mins = abs( $minutes * 60 );

		return sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
	}

	/**
	 * Get literal expression of timezone.
	 *
	 * @return string Human readable timezone name.
	 */
	public static function get_timezone_name(): string {
		$time_zone = self::get_timezone()->getName();
		if ( 'UTC' === $time_zone ) {
			return 'UTC+0';
		}

		if ( 0 === strpos( $time_zone, '+' ) || 0 === strpos( $time_zone, '-' ) ) {
			return 'UTC' . $time_zone;
		}

		/* translators: timezone name */
		return sprintf( __( 'time of %s', 'advanced-ads' ), $time_zone );
	}

	/**
	 * Render icon of the type.
	 *
	 * @param string $icon Icon url.
	 *
	 * @return void
	 */
	public static function render_icon( $icon ): void {
		printf( '<img src="%s" width="50" height="50" />', esc_url( $icon ) );
	}

	/**
	 * Applies image loading optimization attributes to an image HTML tag based on WordPress version.
	 *
	 * @param string $img     HTML image tag.
	 * @param string $context Image context.
	 *
	 * @return string Updated HTML image tag with loading optimization attributes.
	 */
	public static function img_tag_add_loading_attr( $img, $context ) {
		if ( is_array( $context ) ) {
			$context = end( $context );
		}

		// Check if the current WordPress version is compatible.
		if ( is_wp_version_compatible( '6.3' ) ) {
			return wp_img_tag_add_loading_optimization_attrs( $img, $context );
		}

		return wp_img_tag_add_loading_attr( $img, $context ); // phpcs:ignore WordPress.WP.DeprecatedFunctions.wp_img_tag_add_loading_attrFound
	}

	/**
	 * Improve WP_Query performance
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	public static function improve_wp_query( $args ): array {
		$args['no_found_rows']          = true;
		$args['update_post_meta_cache'] = false;
		$args['update_post_term_cache'] = false;

		return $args;
	}

	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $data Data to sanitize.
	 *
	 * @return string|array
	 */
	public static function sanitize_clean( $data ) {
		if ( is_array( $data ) ) {
			return array_map( __CLASS__ . '::sanitize_clean', $data );
		}

		return is_scalar( $data ) ? sanitize_text_field( $data ) : $data;
	}

	/**
	 * Sanitize conditions
	 *
	 * @param array $conditions Conditions to sanitize.
	 *
	 * @return array
	 */
	public static function sanitize_conditions( $conditions ): array {
		return $conditions;
	}
}
