<?php
/**
 * Groups page.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var WP_List_Table|false $wp_list_table The groups list table
 * @var WP_Taxonomy         $taxonomy      Ad group taxonomy
 */

use AdvancedAds\Modal;
use AdvancedAds\Entities;
use AdvancedAds\Framework\Utilities\Params;

$is_search = Params::get( 's' );
?>
<span class="wp-header-end"></span>
<div class="wrap">
	<?php
	ob_start();
	if ( empty( $wp_list_table->items ) ) :
		?>
		<p>
			<?php
			echo esc_html( Entities::get_group_description() );
			?>
			<a href="https://wpadvancedads.com/manual/ad-groups/?utm_source=advanced-ads&utm_medium=link&utm_campaign=groups" target="_blank" class="advads-manual-link"><?php esc_html_e( 'Manual', 'advanced-ads' ); ?></a>
		</p>
		<?php
	endif;

	require ADVADS_ABSPATH . 'views/admin/screens/group-form.php';

	Modal::create(
		[
			'modal_slug'       => 'group-new',
			'modal_content'    => ob_get_clean(),
			'modal_title'      => __( 'New Ad Group', 'advanced-ads' ),
			'close_validation' => 'advads_validate_new_form',
		]
	);
	?>
	<div id="ajax-response"></div>

	<div id="advads-ad-group-list">
		<?php $wp_list_table->display(); ?>
	</div>
</div>
<?php
// Trigger the group form when no groups exist and we are not currently searching.
if ( empty( $wp_list_table->items ) && ! $is_search ) :
	?>
	<script>
		window.location.hash = '#modal-group-new';
	</script>
	<?php
endif;
