<?php // phpcs:ignoreFile

use AdvancedAds\Constants;
use AdvancedAds\Utilities\WordPress;

/**
 * Class Advanced_Ads_Admin_Ad_Type
 */
class Advanced_Ads_Admin_Ad_Type {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Register hooks function related to the ad type
	 */
	private function __construct() {
		add_action( 'delete_post', [ $this, 'delete_ad' ] );
		add_action( 'edit_form_top', [ $this, 'edit_form_above_title' ] );
		add_action( 'dbx_post_sidebar', [ $this, 'edit_form_end' ] );
		add_action( 'post_submitbox_misc_actions', [ $this, 'add_submit_box_meta' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'use_code_editor' ] );
		add_filter( 'gettext', [ $this, 'replace_cheating_message' ], 20, 2 );
		add_filter( 'get_user_option_user-settings', [ $this, 'reset_view_mode_option' ] );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Prepare the ad post type to be removed
	 *
	 * @param int $post_id id of the post.
	 */
	public function delete_ad( $post_id ) {
		global $wpdb;

		if ( ! current_user_can( 'delete_posts' ) ) {
			return;
		}

		if ( $post_id > 0 ) {
			$post_type = get_post_type( $post_id );
			if ( $post_type === Constants::POST_TYPE_AD ) {
				/**
				 * Images uploaded to an image ad type get the `_advanced-ads_parent_id` meta key from WordPress automatically
				 * the following SQL query removes that meta data from any attachment when the ad is removed.
				 */
				$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %d", '_advanced-ads_parent_id', $post_id ) );
			}
		}
	}

	/**
	 * Add information above the ad title
	 *
	 * @param object $post WordPress post type object.
	 *
	 * @since 1.5.6
	 */
	public function edit_form_above_title( $post ) {
		if ( ! isset( $post->post_type ) || $post->post_type !== Constants::POST_TYPE_AD ) {
			return;
		}

		// highlight Dummy ad if this is the first ad.
		if ( ! WordPress::get_count_ads() ) {
			?>
			<style>.advanced-ads-type-list-dummy {
					font-weight: bold;
				}</style>
			<?php
		}

		// display general and wizard information.
		include ADVADS_ABSPATH . 'admin/views/ad-info-top.php';
		// Don’t show placement options if this is an ad translated with WPML since the placement might exist already.
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$trid         = apply_filters( 'wpml_element_trid', null, $post->ID );
			$translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'Advanced_Ads' );
			if ( count( $translations ) > 1 ) {
				return;
			}
		}

		$ad         = wp_advads_get_ad( $post->ID );
		$placements = wp_advads_get_placements();

		/**
		 * Display ad injection information after ad is created.
		 *
		 * Set `advanced-ads-ad-edit-show-placement-injection` to false if you want to prevent the box from appearing
		 */
		if ( isset( $_GET['message'] ) && 6 === $_GET['message'] && apply_filters( 'advanced-ads-ad-edit-show-placement-injection', true ) ) {
			$latest_post = $this->get_latest_post();
			include ADVADS_ABSPATH . 'admin/views/placement-injection-top.php';
		}
	}

	/**
	 * Add information below the ad edit form
	 *
	 * @param WP_Post $post WordPress Post object.
	 *
	 * @since 1.7.3
	 */
	public function edit_form_end( $post ) {
		if ( $post->post_type !== Constants::POST_TYPE_AD ) {
			return;
		}

		include ADVADS_ABSPATH . 'admin/views/ad-info-bottom.php';
	}

	/**
	 * Add meta values below submit box
	 *
	 * @since 1.3.15
	 */
	public function add_submit_box_meta() {
		global $post, $wp_locale;

		if ( Constants::POST_TYPE_AD !== $post->post_type ) {
			return;
		}

		$ad = wp_advads_get_ad( $post->ID );

		// get time set for ad or current timestamp (both GMT).
		$utc_ts    = $ad->get_expiry_date() ?? time();
		$utc_time  = date_create( '@' . $utc_ts );
		$tz_option = get_option( 'timezone_string' );
		$exp_time  = clone $utc_time;

		if ( $tz_option ) {
			$exp_time->setTimezone( Advanced_Ads_Utils::get_wp_timezone() );
		} else {
			$tz_name       = Advanced_Ads_Utils::get_timezone_name();
			$tz_offset     = substr( $tz_name, 3 );
			$off_time      = date_create( $utc_time->format( 'Y-m-d\TH:i:s' ) . $tz_offset );
			$offset_in_sec = date_offset_get( $off_time );
			$exp_time      = date_create( '@' . ( $utc_ts + $offset_in_sec ) );
		}

		list( $curr_year, $curr_month, $curr_day, $curr_hour, $curr_minute ) = explode( '-', $exp_time->format( 'Y-m-d-H-i' ) );
		$enabled = 1 - empty( $ad->get_expiry_date() );

		include ADVADS_ABSPATH . 'admin/views/ad-submitbox-meta.php';
	}

	/**
	 * Use CodeMirror for plain text input field
	 *
	 * Needs WordPress 4.9 and higher
	 *
	 * @since 1.8.15
	 */
	public function use_code_editor() {
		global $wp_version;
		if ( 'advanced_ads' !== get_current_screen()->id || defined( 'ADVANCED_ADS_DISABLE_CODE_HIGHLIGHTING' ) || - 1 === version_compare( $wp_version, '4.9' ) ) {
			return;
		}

		// Enqueue code editor and settings for manipulating HTML.
		$settings = wp_enqueue_code_editor( [ 'type' => 'application/x-httpd-php' ] );

		// Bail if user disabled CodeMirror.
		if ( false === $settings ) {
			return;
		}

		wp_add_inline_script(
			'code-editor',
			sprintf( 'jQuery( function() { if( jQuery( "#advads-ad-content-plain" ).length && typeof Advanced_Ads_Admin !== "undefined" ){ Advanced_Ads_Admin.editor = wp.codeEditor.initialize( "advads-ad-content-plain", %s ); Advanced_Ads_Admin.editor.codemirror.on("keyup", Advanced_Ads_Admin.check_ad_source); jQuery( function() { Advanced_Ads_Admin.check_ad_source(); } ); } } );', wp_json_encode( $settings ) )
		);
	}

	/**
	 * Whether to show the wizard welcome message or not
	 *
	 * @return bool true, if wizard welcome message should be displayed
	 * @since 1.7.4
	 */
	public function show_wizard_welcome() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return true;
		}

		$hide_wizard = get_user_meta( $user_id, 'advanced-ads-hide-wizard', true );
		global $post;

		return ( ! $hide_wizard && 'edit' !== $post->filter ) ? true : false;
	}

	/**
	 * Whether to start the wizard by default or not
	 *
	 * @since 1.7.4
	 * return bool true, if wizard should start automatically
	 */
	public function start_wizard_automatically() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return true;
		}

		$hide_wizard = get_user_meta( $user_id, 'advanced-ads-hide-wizard', true );

		global $post;

		// true the ad already exists, if the wizard was never started or closed.
		return ( 'edit' !== $post->filter && ( ! $hide_wizard || 'false' === $hide_wizard ) ) ? true : false;
	}

	/**
	 * Replace 'You need a higher level of permission.' message if user role does not have required permissions.
	 *
	 * @param string $translated_text Translated text.
	 * @param string $untranslated_text Text to translate.
	 *
	 * @return string $translation  Translated text.
	 */
	public function replace_cheating_message( $translated_text, $untranslated_text ) {
		global $typenow;

		if ( isset( $typenow ) && 'You need a higher level of permission.' === $untranslated_text && $typenow === Constants::POST_TYPE_AD ) {
			$translated_text = __( 'You don’t have access to ads. Please deactivate and re-enable Advanced Ads again to fix this.', 'advanced-ads' )
							   . '&nbsp;<a href="https://wpadvancedads.com/manual/user-capabilities/?utm_source=advanced-ads&utm_medium=link&utm_campaign=wrong-user-role#You_dont_have_access_to_ads" target="_blank">' . __( 'Get help', 'advanced-ads' ) . '</a>';
		}

		return $translated_text;
	}

	/**
	 * Set the removed post list mode to "List", if it was set to "Excerpt".
	 *
	 * @param string $user_options Query string containing user options.
	 *
	 * @return string
	 */
	public function reset_view_mode_option( $user_options ) {
		return str_replace( '&posts_list_mode=excerpt', '&posts_list_mode=list', $user_options );
	}

	/**
	 * Load latest blog post
	 * @return WP_POST|null
	 */
	public function get_latest_post(){
		$posts = wp_get_recent_posts(["numberposts" => 1]);
		return $posts ? $posts[0] : null;
	}
}
