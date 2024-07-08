<?php
/**
 * Utilities Sanitize.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * Utilities Sanitize.
 */
class Sanitize {
	/**
	 * Sanitize the frontend prefix to result in valid HTML classes.
	 * See https://www.w3.org/TR/selectors-3/#grammar for valid tokens.
	 *
	 * @param string $prefix The HTML class to sanitize.
	 * @param string $fallback The fallback if the class is invalid.
	 *
	 * @return string
	 */
	public static function frontend_prefix( $prefix, $fallback = '' ): string {
		$prefix   = sanitize_html_class( $prefix );
		$nonascii = '[^\0-\177]';
		$unicode  = '\\[0-9a-f]{1,6}(\r\n|[ \n\r\t\f])?';
		$escape   = sprintf( '%s|\\[^\n\r\f0-9a-f]', $unicode );
		$nmstart  = sprintf( '[_a-z]|%s|%s', $nonascii, $escape );
		$nmchar   = sprintf( '[_a-z0-9-]|%s|%s', $nonascii, $escape );

		if ( ! preg_match( sprintf( '/-?(?:%s)(?:%s)*/i', $nmstart, $nmchar ), $prefix, $matches ) ) {
			return $fallback;
		}

		return $matches[0];
	}
}
