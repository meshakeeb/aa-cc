<?php
/**
 * This class represents the "Slider" group type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Groups\Types;

use AdvancedAds\Groups\Group_Slider;
use AdvancedAds\Interfaces\Group_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Type Slider.
 */
class Slider implements Group_Type {

	/**
	 * Get the unique identifier (ID) of the group type.
	 *
	 * @return string The unique ID of the group type.
	 */
	public function get_id(): string {
		return 'slider';
	}

	/**
	 * Get the class name of the object as a string.
	 *
	 * @return string
	 */
	public function get_classname(): string {
		return Group_Slider::class;
	}

	/**
	 * Get the title or name of the group type.
	 *
	 * @return string The title of the group type.
	 */
	public function get_title(): string {
		return __( 'Ad Slider', 'advanced-ads' );
	}

	/**
	 * Get a description of the group type.
	 *
	 * @return string The description of the group type.
	 */
	public function get_description(): string {
		return '';
	}

	/**
	 * Check if this group type requires premium.
	 *
	 * @return bool True if premium is required; otherwise, false.
	 */
	public function is_premium(): bool {
		return true;
	}

	/**
	 * Get the URL for upgrading to this group type.
	 *
	 * @return string The upgrade URL for the group type.
	 */
	public function get_image(): string {
		return ADVADS_BASE_URL . 'admin/assets/img/groups/slider.svg';
	}
}
