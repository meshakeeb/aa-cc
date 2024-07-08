<?php
/**
 * Render the ad type column content in the ad list.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var string  $size_string Ad size string.
 * @var Ad      $ad          Ad instance.
 */

$type_object = $ad->get_type_object();
?>
<span class="advads-ad-list-tooltip">
	<span class="advads-ad-list-tooltip-content">
		<strong><?php echo esc_html( $type_object->get_title() ); ?></strong><br/>
		<?php if ( ! empty( $size_string ) ) : ?>
			<span class="advads-ad-size"><?php echo esc_html( $size_string ); ?></span>
		<?php endif; ?>
	</span>
	<a href="<?php echo esc_url( get_edit_post_link( $ad->get_id() ) ); ?>">
		<img src="<?php echo esc_url( $type_object->get_image() ); ?>" alt="<?php echo esc_attr( $type_object->get_title() ); ?>" title="<?php echo esc_attr( $type_object->get_title() ); ?>" width="50">
	</a>
</span>
