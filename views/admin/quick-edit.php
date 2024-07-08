<?php
/**
 * Quick edit fields
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0
 *
 * @var array $privacy_options privacy module options.
 */

global $wp_locale;

?>
<fieldset class="inline-edit-col-right advads-quick-edit" disabled>
	<label><input value="1" type="checkbox" name="debugmode"><?php esc_html_e( 'Debug mode', 'advanced-ads' ); ?></label>
</fieldset>
<fieldset class="inline-edit-col-right inline-edit-expiry advads-quick-edit" disabled>
	<label><input type="checkbox" name="enable_expiry" value="1"/><?php esc_html_e( 'Set expiry date', 'advanced-ads' ); ?></label>
	<div class="expiry-inputs advads-datetime">
		<?php \AdvancedAds\Admin\Quick_Bulk_Edit::print_date_time_inputs(); ?>
	</div>
</fieldset>
<?php if ( isset( $privacy_options['enabled'] ) ) : ?>
	<fieldset class="inline-edit-col-right advads-quick-edit" disabled>
		<label><input type="checkbox" name="ignore_privacy" value="1"/><?php esc_html_e( 'Ignore privacy settings', 'advanced-ads' ); ?></label>
	</fieldset>
<?php endif; ?>
