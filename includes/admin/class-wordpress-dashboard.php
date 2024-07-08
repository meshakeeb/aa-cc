<?php
/**
 * Admin WordPress Dashboard.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Options;
use AdvancedAds\Constants;
use Advanced_Ads_AdSense_Data;
use Advanced_Ads_Admin_Notices;
use AdvancedAds\Assets_Registry;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Utilities\Conditional;
use Advanced_Ads_Overview_Widgets_Callbacks;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress Dashboard.
 */
class WordPress_Dashboard implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'wp_dashboard_setup', [ $this, 'add_adsense_widget' ] );
		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widget' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ], 10, 0 );
		add_action( 'advanced-ads-dashbaord-widget', [ $this, 'widget_header' ], 1 );
		add_action( 'advanced-ads-dashbaord-widget', [ $this, 'widget_footer' ], 100 );
		add_action( 'advanced-ads-dashbaord-widget', [ $this, 'subscribe_buttons' ] );
		add_action( 'advanced-ads-dashbaord-widget', [ $this, 'display_performing_ads' ] );
		add_action( 'advanced-ads-dashbaord-widget', [ $this, 'display_rss_widget' ] );
	}

	/**
	 * Enqueue styles and scripts for current screen
	 *
	 * @return void
	 */
	public function enqueue(): void {
		// Early bail!!
		$wp_screen = get_current_screen();
		if ( 'dashboard' !== $wp_screen->id ) {
			return;
		}

		Assets_Registry::enqueue_style( 'wp-dashboard' );
		Assets_Registry::enqueue_script( 'wp-dashboard' );
	}

	/**
	 * Add dashboard widget with ad stats and additional information
	 *
	 * @return void
	 */
	public function add_dashboard_widget(): void {
		if ( ! Conditional::user_can( 'advanced_ads_see_interface' ) ) {
				return;
		}

		$icon = WordPress::get_svg( 'logo.svg' );
		$icon = '<span class="advads-logo--icon">' . $icon . '</span>';

		wp_add_dashboard_widget(
			'advads-dashboard-widget',
			$icon . '<span class="advads-logo--text">' . __( 'Advanced Ads', 'advanced-ads' ) . '</span>',
			[ $this, 'display_dashboard_widget' ],
			null,
			null,
			'side',
			'high'
		);
	}

	/**
	 * Adds an AdSense widget to the WordPress dashboard.
	 *
	 * @return void
	 */
	public function add_adsense_widget(): void {
		if (
			Advanced_Ads_AdSense_Data::get_instance()->is_setup() &&
			! Advanced_Ads_AdSense_Data::get_instance()->is_hide_stats() &&
			Options::get()->adsense( 'adsense-wp-widget' )
		) {
			wp_add_dashboard_widget(
				'advads-adsense-widget',
				__( 'AdSense Earnings', 'advanced-ads' ),
				[ $this, 'display_adsense_widget' ],
				null,
				null,
				'side'
			);
		}
	}

	/**
	 * Display widget functions
	 *
	 * @return void
	 */
	public function display_dashboard_widget(): void {
		/**
		 * Let developer add KPIs and info into dashabord
		 *
		 * @param WordPress_Dashboard $this Dashabord widget instance.
		 */
		do_action( 'advanced-ads-dashbaord-widget', $this );
	}

	/**
	 * Display the AdSense widget on the WordPress dashboard.
	 *
	 * @return void
	 */
	public function display_adsense_widget(): void {
		Advanced_Ads_Overview_Widgets_Callbacks::render_adsense_stats();
	}

	/**
	 * Widget header
	 *
	 * @return void
	 */
	public function widget_header(): void {
		// Early bail!!
		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		$ads_count = WordPress::get_count_ads();
		echo '<p class="advads-widget-header">';
		printf(
			/* translators: %1$d is the number of ads, %2$s and %3$s are URLs. */
			wp_kses( __( '%1$d Ads | <a href="%2$s">Manage Ads</a> | <a href="%3$s">Create Ad</a>', 'advanced-ads' ), [ 'a' => [ 'href' => [] ] ] ),
			absint( $ads_count ),
			esc_url( admin_url( 'edit.php?post_type=' . Constants::POST_TYPE_AD ) ),
			esc_url( admin_url( 'post-new.php?post_type=' . Constants::POST_TYPE_AD ) )
		);
		echo '</p>';
	}

	/**
	 * Widget footer
	 *
	 * @return void
	 */
	public function widget_footer(): void {
		?>
		<footer>
			<a href="https://wpadvancedads.com/category/tutorials/?utm_source=advanced-ads&utm_medium=link&utm_campaign=dashboard" target="_blank">
				<?php esc_html_e( 'Visit our blog', 'advanced-ads' ); ?> <span class="screen-reader-text"> (opens in a new tab)</span><span aria-hidden="true" class="dashicons dashicons-external"></span>
			</a>
			<a href="https://wpadvancedads.com/manual/?utm_source=advanced-ads&utm_medium=link&utm_campaign=dashboard" target="_blank">
				<?php esc_html_e( 'Help', 'advanced-ads' ); ?> <span class="screen-reader-text"> (opens in a new tab)</span><span aria-hidden="true" class="dashicons dashicons-external"></span>
			</a>
			<a class="go-pro" href="https://wpadvancedads.com/pricing/?utm_source=advanced-ads&utm_medium=link&utm_campaign=dashboard" target="_blank">
				<?php esc_html_e( 'Go Pro', 'advanced-ads' ); ?> <span class="screen-reader-text"> (opens in a new tab)</span><span aria-hidden="true" class="dashicons dashicons-external"></span>
			</a>
		</footer>
		<?php
	}

	/**
	 * Display subscribe buttons
	 *
	 * @return void
	 */
	public function subscribe_buttons(): void {
		echo '<div class="advads-widget-buttons">';
		$this->subscribe_button( 'nl_first_steps', __( 'Get the tutorial via email', 'advanced-ads' ) );
		$this->subscribe_button( 'nl_adsense', __( 'Get AdSense tips via email', 'advanced-ads' ) );
		echo '</div>';
	}

	/**
	 * Display subscribe button
	 *
	 * @param string $notice_id Notice id to check.
	 * @param string $label     Button label.
	 *
	 * @return void
	 */
	private function subscribe_button( $notice_id, $label ): void {
		$options = Advanced_Ads_Admin_Notices::get_instance()->options();
		// Early bail!!
		if ( isset( $options['closed'][ $notice_id ] ) ) {
			return;
		}

		?>
		<button type="button" class="button-primary advads-notices-button-subscribe" data-notice="<?php echo esc_attr( $notice_id ); ?>"><?php echo esc_html( $label ); ?></button>
		<?php
	}

	/**
	 * Display rss widget
	 *
	 * @return void
	 */
	public function display_performing_ads(): void {
		?>
		<h3>
			<?php esc_html_e( 'Best-performing Ads', 'advanced-ads' ); ?>
		</h3>
		<?php
		if ( ! defined( 'AAT_FILE' ) ) {
			?>
			<p>
				<?php esc_html_e( 'No tracking add-on installed.', 'advanced-ads' ); ?>
			</p>
			<p>
				<a class="go-pro" href="https://wpadvancedads.com/pricing/?utm_source=advanced-ads&utm_medium=link&utm_campaign=dashboard" target="_blank">
					<?php esc_html_e( 'Advanced Ads All Access includes the Tracking add-on', 'advanced-ads' ); ?><span aria-hidden="true" class="dashicons dashicons-external"></span>
				</a>
			</p>
			<?php
		}
	}

	/**
	 * Display rss widget
	 *
	 * @return void
	 */
	public function display_rss_widget(): void {
		$cache_key  = 'advads_feed_posts_v2';
		$expires_in = 2 * HOUR_IN_SECONDS;
		$cache      = get_transient( $cache_key );
		if ( false !== $cache ) {
			echo $cache; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		ob_start();
		$this->get_rss_widget();
		$content = ob_get_clean();

		set_transient( $cache_key, $content, $expires_in );

		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Create the rss output of the widget
	 */
	private function get_rss_widget() {
		$posts = $this->get_feed();
		?>
		<h3>
			<?php esc_html_e( 'Latest Blog Posts from Advanced Ads', 'advanced-ads' ); ?>
		</h3>

		<?php if ( empty( $posts ) ) : ?>
			<p>
				<?php esc_html_e( 'Error: the Advanced Ads blog feed could not be downloaded.', 'advanced-ads' ); ?>
			</p>
			<?php
			return;
		endif;
		?>

		<div class="rss-widget">
			<ul>
			<?php foreach ( $posts as $post ) : ?>
				<li>
					<a class="rsswidget" target="_blank" href="<?php echo esc_url( $this->add_utm_params( $post['link'] ) ); ?>">
						<?php echo esc_html( $post['title']['rendered'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Add UTM tags to links. Only add if UTM params are not already present.
	 *
	 * @param string $link Link to append UTM.
	 *
	 * @return string
	 */
	private function add_utm_params( $link ): string {
		$utm_params = [
			'utm_source'   => 'advanced-ads',
			'utm_medium'   => 'rss-link',
			'utm_campaign' => 'dashboard',
		];

		return add_query_arg( $utm_params, $link );
	}

	/**
	 * Get feed from site
	 *
	 * @return bool|array
	 */
	private function get_feed() {
		$response = wp_remote_get( 'https://wpadvancedads.com/wp-json/wp/v2/posts?categories=1&per_page=3' );

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$posts = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $posts ) || ! is_array( $posts ) ) {
			return false;
		}

		return $posts;
	}
}
