<?php
/**
 * Bulk edit fields
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0
 *
 * @var array $privacy_options privacy module options.
 */

global $wp_locale;

?>

<fieldset class="inline-edit-col-right advads-bulk-edit">
	<label>
		<span class="title"><?php esc_html_e( 'Debug mode', 'advanced-ads' ); ?></span>
		<select name="debug_mode">
			<option value="">— <?php esc_html_e( 'No Change', 'advanced-ads' ); ?> —</option>
			<option value="on"><?php esc_html_e( 'Enabled', 'advanced-ads' ); ?></option>
			<option value="off"><?php esc_html_e( 'Disabled', 'advanced-ads' ); ?></option>
		</select>
	</label>
</fieldset>
<fieldset class="inline-edit-col-right advads-bulk-edit">
	<label>
		<span class="title"><?php esc_html_e( 'Expiry date', 'advanced-ads' ); ?></span>
		<select name="expiry_date">
			<option value="">— <?php esc_html_e( 'No Change', 'advanced-ads' ); ?> —</option>
			<option value="on"><?php esc_html_e( 'Set', 'advanced-ads' ); ?></option>
			<option value="off"><?php esc_html_e( 'Unset', 'advanced-ads' ); ?></option>
		</select>
	</label>
	<div class="expiry-inputs advads-datetime">
		<?php \AdvancedAds\Admin\Quick_Bulk_Edit::print_date_time_inputs(); ?>
	</div>
</fieldset>
<?php if ( isset( $privacy_options['enabled'] ) ) : ?>
	<fieldset class="inline-edit-col-right advads-bulk-edit">
		<label>
			<span><?php esc_html_e( 'Ignore privacy settings', 'advanced-ads' ); ?></span>
			<select name="ignore_privacy">
				<option value="">— <?php esc_html_e( 'No Change', 'advanced-ads' ); ?> —</option>
				<option value="on"><?php esc_html_e( 'Enabled', 'advanced-ads' ); ?></option>
				<option value="off"><?php esc_html_e( 'Disabled', 'advanced-ads' ); ?></option>
			</select>
		</label>
	</fieldset>
<?php endif; ?>
