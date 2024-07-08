<?php
/**
 * Admin Notices.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Notices.
 */
class Admin_Notices implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'all_admin_notices', [ $this, 'create_first_ad' ] );
	}

	/**
	 * Show instructions to create first ad above the ad list
	 *
	 * @return void
	 */
	public function create_first_ad(): void {
		$screen = get_current_screen();
		if ( ! isset( $screen->id ) || 'edit-advanced_ads' !== $screen->id ) {
			return;
		}

		$counts = WordPress::get_count_ads();

		// Only display if there are no more than 2 ads.
		if ( 3 > $counts ) {
			include ADVADS_ABSPATH . 'views/notices/create-first-ad.php';
		}
	}
}
