<?php // phpcs:ignoreFile

use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Utilities\Conditional;

/**
 * Functions around promoting upgrades
 *
 * @package Advanced Ads
 */
class Advanced_Ads_Admin_Upgrades {

	/**
	 * Advanced_Ads_Admin_Upgrades constructor.
	 */
	public function __construct() {
		// Show notice in Ad Parameters when someone uses an Ad Manager ad in the plain text code field.
		add_filter( 'advanced-ads-ad-notices', [ $this, 'ad_notices' ], 10, 3 );
		// Show AMP options on ad edit page of AdSense ads.
		add_action( 'advanced-ads-gadsense-extra-ad-param', [ $this, 'adsense_type_amp_options' ] );
		// Add Duplicate link to ad overview list.
		add_filter( 'post_row_actions', [ $this, 'render_duplicate_link' ], 10, 2 );
		// Add Duplicate link to post submit box.
		add_action( 'post_submitbox_start', [ $this, 'render_duplicate_link_in_submit_box' ] );
	}

	/**
	 * Show an upgrade link
	 *
	 * @param string $title link text.
	 * @param string $url target URL.
	 * @param string $utm_campaign utm_campaign value to attach to the URL.
	 */
	public static function upgrade_link( $title = '', $url = '', $utm_campaign = 'upgrade' ) {
		$title = ! empty( $title ) ? $title : __( 'Upgrade', 'advanced-ads' );
		$url   = ! empty( $url ) ? $url : 'https://wpadvancedads.com/add-ons/';

		$url = add_query_arg( [
			'utm_source'   => 'advanced-ads',
			'utm_medium'   => 'link',
			'utm_campaign' => $utm_campaign,
		], $url );

		include ADVADS_ABSPATH . 'admin/views/upgrades/upgrade-link.php';
	}

	/**
	 * Show an Advanced Ads Pro upsell pitch
	 *
	 * @param string $utm_campaign utm_campaign value to attach to the URL.
	 * @deprecated use upgrade_link()
	 */
	public static function pro_feature_link( $utm_campaign = '' ) {
		self::upgrade_link(
			__( 'Pro Feature', 'advanced-ads' ),
			'https://wpadvancedads.com/advanced-ads-pro/',
			$utm_campaign
		);
	}

	/**
	 * Show notices in the Ad Parameters meta box
	 *
	 * @param array $notices Notices.
	 * @param array $box current meta box.
	 * @param Ad    $ad post object.
	 *
	 * @return array
	 */
	public function ad_notices( $notices, $box, $ad ) {
		// Show notice when someone uses an Ad Manager ad in the plain text code field.
		if ( ! defined( 'AAGAM_VERSION' ) && 'ad-parameters-box' === $box['id'] ) {
			if ( $ad->is_type( 'plain' ) && strpos( $ad->get_content(), 'div-gpt-ad-' ) ) {
				$notices[] = [
					'text' => sprintf(
					// Translators: %1$s opening a tag, %2$s closing a tag.
						esc_html__( 'This looks like a Google Ad Manager ad. Use the %1$sGAM Integration%2$s.', 'advanced' ),
						'<a href="https://wpadvancedads.com/add-ons/google-ad-manager/?utm_source=advanced-ads&utm_medium=link&utm_campaign=upgrade-ad-parameters-gam" target="_blank">',
						'</a>'
					) . ' ' . __( 'A quick and error-free way of implementing ad units from your Google Ad Manager account.', 'advanced-ads' ),
				];
			}
		}

		return $notices;
	}

	/**
	 * AMP options for AdSense ads in the Ad Parameters on the ad edit page.
	 */
	public function adsense_type_amp_options() {
		if ( ! defined( 'AAR_VERSION' ) && Advanced_Ads_Checks::active_amp_plugin() ) {
			include ADVADS_ABSPATH . 'admin/views/upgrades/adsense-amp.php';
		}
	}

	/**
	 * Add the link to action list for post_row_actions
	 *
	 * @param array   $actions list of existing actions.
	 * @param WP_Post $post Post object.
	 *
	 * @return array with actions.
	 */
	public function render_duplicate_link( $actions, $post ) {
		if (
			 ! defined( 'AAP_VERSION' )
			 && Constants::POST_TYPE_AD === $post->post_type
			 && Conditional::user_can( 'advanced_ads_edit_ads' )
		) {
			$actions['copy-ad'] = $this->create_duplicate_link();
		}

		return $actions;
	}

	/**
	 * Add the link to the submit box on the ad edit screen.
	 */
	public function render_duplicate_link_in_submit_box() {
		global $post;
		if (
			! defined( 'AAP_VERSION' )
			 && $post->filter === 'edit' // only for already saved ads.
			 && Constants::POST_TYPE_AD === $post->post_type
			 && Conditional::user_can( 'advanced_ads_edit_ads' )
		) {
			?>
			<div>
				<?php echo wp_kses_post( $this->create_duplicate_link() ); ?>
			</div>
			<?php
		}
	}

	/**
	 * Generate text and upgrade link for the Duplicate function
	 */
	public function create_duplicate_link() {
		ob_start();
		self::upgrade_link( null, 'https://wpadvancedads.com/checkout/?edd_action=add_to_cart&download_id=1742', 'duplicate-ad' );

		return sprintf(
			'%1$s (%2$s)',
			esc_html__( 'Duplicate', 'advanced-ads' ),
			trim( ob_get_clean() )
		);
	}
}
