<?php
/**
 * Upgrades.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds;

use AdvancedAds\Framework\Updates;
use AdvancedAds\Framework\Interfaces\Initializer_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Upgrades.
 */
class Upgrades implements Initializer_Interface {

	const DB_VERSION = '1.52.1';

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize(): void {
		// Force run the upgrades.
		$is_first_time = empty( get_option( 'advanced_ads_db_version' ) );

		Updates::get()
			->set_folder( ADVADS_ABSPATH . 'upgrades' )
			->set_version( self::DB_VERSION )
			->set_option_name( 'advanced_ads_db_version' )
			->add_updates(
				[
					'1.48.4' => 'upgrade-1.48.4.php',
					'1.48.5' => 'upgrade-1.48.5.php',
					'1.50.0' => 'upgrade-1.50.0.php',
					'1.52.1' => 'upgrade-1.52.1.php',
				]
			)
			->hooks();

		if ( $is_first_time ) {
			Updates::get()->set_version( '1.0.0' );
			add_action( 'admin_init', [ Updates::get(), 'perform_updates' ] );
		}
	}
}
