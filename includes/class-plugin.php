<?php
/**
 * The plugin bootstrap.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds;

use AdvancedAds\Rest;
use AdvancedAds\Admin;
use AdvancedAds\Ads\Ads;
use AdvancedAds\Frontend;
use AdvancedAds\Groups\Groups;
use AdvancedAds\Installation\Install;
use AdvancedAds\Placements\Placements;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin.
 */
class Plugin extends Framework\Loader {

	use Traits\Extras;

	/**
	 * The ads container
	 *
	 * @var Ads
	 */
	public $ads = null;

	/**
	 * The groups container
	 *
	 * @var Groups
	 */
	public $groups = null;

	/**
	 * The placements container
	 *
	 * @var Placements
	 */
	public $placements = null;

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Plugin
	 */
	public static function get(): Plugin {
		static $instance;

		if ( null === $instance ) {
			$instance = new Plugin();
			$instance->setup();
		}

		return $instance;
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	public function get_version(): string {
		return ADVADS_VERSION;
	}

	/**
	 * Bootstrap plugin.
	 *
	 * @return void
	 */
	private function setup(): void {
		$this->define_constants();
		$this->includes_functions();
		$this->includes();
		$this->includes_rest();
		$this->includes_admin();
		$this->includes_frontend();

		/**
		 * Old loading strategy
		 *
		 * TODO: need to remove it in future.
		 */
		// Public-Facing and Core Functionality.
		\Advanced_Ads::get_instance();
		\Advanced_Ads_ModuleLoader::loadModules( ADVADS_ABSPATH . 'modules/' ); // enable modules, requires base class.

		// Dashboard and Administrative Functionality.
		if ( is_admin() ) {
			\Advanced_Ads_Admin::get_instance();
		}

		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ], -1 );

		// Load it all.
		$this->ads->initialize();
		$this->groups->initialize();
		$this->placements->initialize();
		$this->load();
	}

	/**
	 * When WordPress has loaded all plugins, trigger the `advanced-ads-loaded` hook.
	 *
	 * @since 1.47.0
	 *
	 * @return void
	 */
	public function on_plugins_loaded(): void {
		/**
		 * Action trigger after loading finished.
		 *
		 * @since 1.47.0
		 */
		do_action( 'advanced-ads-loaded' );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		$locale = get_user_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'advanced-ads' );

		unload_textdomain( 'advanced-ads' );
		if ( false === load_textdomain( 'advanced-ads', WP_LANG_DIR . '/plugins/advanced-ads-' . $locale . '.mo' ) ) {
			load_textdomain( 'advanced-ads', WP_LANG_DIR . '/advanced-ads/advanced-ads-' . $locale . '.mo' );
		}

		load_plugin_textdomain( 'advanced-ads', false, dirname( ADVADS_PLUGIN_BASENAME ) . '/languages' );
	}

	/**
	 * Define Advanced Ads constant
	 *
	 * @return void
	 */
	private function define_constants(): void {
		$this->define( 'ADVADS_ABSPATH', dirname( ADVADS_FILE ) . '/' );
		$this->define( 'ADVADS_PLUGIN_BASENAME', plugin_basename( ADVADS_FILE ) );
		$this->define( 'ADVADS_BASE_URL', plugin_dir_url( ADVADS_FILE ) );
		$this->define( 'ADVADS_SLUG', 'advanced-ads' );
		// name for group & option in settings.
		$this->define( 'ADVADS_SETTINGS_ADBLOCKER', ADVADS_SLUG . '-adblocker' );

		// Deprecated Constants.
		/**
		 * ADVADS_BASE
		 *
		 * @deprecated 1.47.0 use ADVADS_PLUGIN_BASENAME now.
		 */
		define( 'ADVADS_BASE', ADVADS_PLUGIN_BASENAME );

		/**
		 * ADVADS_BASE_PATH
		 *
		 * @deprecated 1.47.0 use ADVADS_ABSPATH now.
		 */
		define( 'ADVADS_BASE_PATH', ADVADS_ABSPATH );

		/**
		 * ADVADS_BASE_DIR
		 *
		 * @deprecated 1.47.0 Avoid global declaration of the constant used exclusively in `load_text_domain` function; use localized declaration instead.
		 */
		define( 'ADVADS_BASE_DIR', dirname( ADVADS_PLUGIN_BASENAME ) );

		/**
		 * ADVADS_URL
		 *
		 * @deprecated 1.47.0 Deprecating the constant in favor of using the direct URL to circumvent costly `esc_url` function; please update code accordingly.
		 */
		define( 'ADVADS_URL', 'https://wpadvancedads.com/' );
	}

	/**
	 * Includes core files used in admin and on the frontend.
	 *
	 * @return void
	 */
	private function includes(): void {
		$this->ads        = new Ads();
		$this->groups     = new Groups();
		$this->placements = new Placements();

		// Common.
		$this->register_initializer( Install::class );
		$this->register_integration( Entities::class );
		$this->register_integration( Assets_Registry::class );
		$this->register_integration( Framework\JSON::class, 'json', [ 'advancedAds' ] );
		$this->register_integration( Compatibility\Compatibility::class );
		$this->register_integration( Post_Data::class );
		$this->register_integration( Crons\Ads::class );
		$this->register_integration( Shortcodes::class, 'shortcodes' );
	}

	/**
	 * Includes files used on the frontend.
	 *
	 * @return void
	 */
	private function includes_frontend(): void {
		// Early bail!!
		if ( is_admin() ) {
			return;
		}

		$this->register_integration( Frontend\Debug_Ads::class );
		$this->register_integration( Frontend\Ad_Renderer::class, 'renderer' );
		$this->register_integration( Frontend\Manager::class, 'frontend' );
		$this->register_integration( Frontend\Scripts::class );
	}

	/**
	 * Includes files used in admin.
	 *
	 * @return void
	 */
	private function includes_admin(): void {
		// Early bail!!
		if ( ! is_admin() ) {
			return;
		}

		$this->register_initializer( Upgrades::class );
		$this->register_integration( Admin\Action_Links::class );
		$this->register_integration( Admin\Admin_Menu::class, 'screens' );
		$this->register_integration( Admin\Admin_Notices::class );
		$this->register_integration( Admin\Assets::class );
		$this->register_integration( Admin\Header::class );
		$this->register_integration( Admin\Marketing::class );
		$this->register_integration( Admin\Metabox_Ad::class );
		$this->register_integration( Admin\Metabox_Ad_Settings::class );
		$this->register_integration( Admin\Post_Types::class );
		$this->register_integration( Admin\Screen_Options::class );
		$this->register_integration( Admin\Shortcode_Creator::class );
		$this->register_integration( Admin\TinyMCE::class );
		$this->register_integration( Admin\WordPress_Dashboard::class );
		$this->register_integration( Admin\Quick_Bulk_Edit::class );
		$this->register_integration( Importers\Manager::class, 'importers' );
		$this->register_integration( Admin\AJAX::class );
		$this->register_integration( Admin\Version_Control::class );
	}

	/**
	 * Includes rest api files used in admin and on the frontend.
	 *
	 * @return void
	 */
	private function includes_rest(): void {
		$this->register_route( Rest\Groups::class );
		$this->register_route( Rest\Quick_Edit::class );
		$this->register_route( Rest\Placements::class );
		$this->register_route( Rest\OnBoarding::class );
	}

	/**
	 * Includes the necessary functions files.
	 *
	 * @return void
	 */
	private function includes_functions(): void {
		require_once ADVADS_ABSPATH . 'includes/functions.php';
		require_once ADVADS_ABSPATH . 'includes/functions-core.php';
		require_once ADVADS_ABSPATH . 'includes/functions-conditional.php';
		require_once ADVADS_ABSPATH . 'includes/functions-ad.php';
		require_once ADVADS_ABSPATH . 'includes/functions-group.php';
		require_once ADVADS_ABSPATH . 'includes/functions-placement.php';
		require_once ADVADS_ABSPATH . 'includes/cap_map.php';
	}
}
