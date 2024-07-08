<?php
/**
 * Tools screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use AdvancedAds\Assets_Registry;
use AdvancedAds\Abstracts\Screen;
use AdvancedAds\Utilities\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Tools.
 */
class Tools extends Screen {

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		$hook = add_submenu_page(
			ADVADS_SLUG,
			__( 'Status and Tools', 'advanced-ads' ),
			__( 'Status &amp; Tools', 'advanced-ads' ),
			Conditional::user_cap( 'advanced_ads_manage_options' ),
			ADVADS_SLUG . '-tools',
			[ $this, 'display' ]
		);

		$this->set_hook( $hook );
		$this->set_tabs(
			[
				'importers' => [
					'label'    => __( 'Import & Export', 'advanced-ads' ),
					'filename' => 'views/admin/tools/importers.php',
				],
				'version'   => [
					'label'    => __( 'Version Control', 'advanced-ads' ),
					'filename' => 'views/admin/tools/version.php',
				],
				'tools'     => [
					'label'    => __( 'Database Tools', 'advanced-ads' ),
					'filename' => 'views/admin/tools/tools.php',
				],
				'status'    => [
					'label'    => __( 'System Status', 'advanced-ads' ),
					'filename' => 'views/admin/tools/status.php',
				],
			]
		);
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		Assets_Registry::enqueue_script( 'admin-common' );
		Assets_Registry::enqueue_style( 'screen-tools' );
		Assets_Registry::enqueue_script( 'screen-tools' );
	}

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void {
		include_once ADVADS_ABSPATH . 'views/admin/screens/tools.php';
	}
}
