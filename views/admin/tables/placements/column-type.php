<?php
/**
 * Render the placement type column content in the placement table.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var Placement $placement Placement instance.
 */

$placement_type = $placement->get_type_object();
?>
<div class="advads-form-type">
	<img src="<?php echo esc_url( $placement_type->get_image() ); ?>" alt="<?php echo esc_attr( $placement_type->get_title() ); ?>" title="<?php echo esc_attr( $placement_type->get_title() ); ?>">
</div>
