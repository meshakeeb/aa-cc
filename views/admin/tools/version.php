<?php
/**
 * Render version management page
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

use AdvancedAds\Admin\Version_Control;

$versions = get_transient( Version_Control::VERSIONS_TRANSIENT );
?>
<h3>
	<?php esc_html_e( 'Rollback to Previous Version', 'advanced-ads' ); ?>
</h3>
<p>
	<?php
	printf(
	/* translators: %s: current version */
		esc_html__( 'Experiencing an issue with Advanced Ads version %s? Rollback to a previous version before the issue appeared.', 'advanced-ads' ),
		esc_attr( wp_advads()->get_version() )
	)
	?>
</p>
<form method="post" action="" id="alternative-version">
	<input type="hidden" name="nonce" id="version-control-nonce" value="<?php echo esc_attr( wp_create_nonce( 'advads-version-control' ) ); ?>"/>
	<table class="form-table">
		<tbody>
		<tr>
			<th scope="row"><?php esc_html_e( 'Available versions', 'advanced-ads' ); ?></th>
			<td>
				<p>
					<label>
						<select name="version" id="plugin-version"<?php echo ! $versions ? ' disabled' : ''; ?> class="!border">
							<?php if ( ! $versions ) : ?>
								<option value="">--<?php esc_html_e( 'Fetching versions', 'advanced-ads' ); ?>--</option>
							<?php else : ?>
								<?php foreach ( $versions['order'] as $index => $version ) : ?>
									<option value="<?php echo esc_attr( $version . '|' . $versions['versions'][ $version ] ); ?>"<?php selected( $index, 0 ); ?>><?php echo esc_html( $version ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</label>
					<button type="submit" id="install-version" class="button button-primary"<?php echo ! $versions ? ' disabled' : ''; ?>><?php esc_html_e( 'Reinstall', 'advanced-ads' ); ?></button>
					<span class="spinner"></span>
				</p>
				<p class="text-sm italic text-red-600"><?php esc_html_e( 'Warning: It is advised that you backup your database before installing another version.', 'advanced-ads' ); ?></p>
			</td>
		</tr>
		</tbody>
	</table>
</form>
<h3 class="mt-10"><?php esc_html_e( 'Become a Beta Tester', 'advanced-ads' ); ?></h3>
<p class="max-w-screen-lg">
	<?php
	printf(
		wp_kses(
			// translators: link to newsletter page.
			__( 'Turn-on Beta Tester, to get notified when a new beta version of Advanced Ads or Advanced Ads Pro is available. The Beta version will not install automatically. You always have the option to ignore it. <a href="%s" target="_blank">Click here</a> to join our first-to-know email updates.', 'advanced-ads' ),
			[
				'a' => [
					'href'   => true,
					'target' => true,
				],
			]
		),
		'https://wpadvancedads.com/newsletter/'
	);
	?>
</p>
<table class="form-table">
	<tbody>
	<tr>
		<th scope="row"><?php esc_html_e( 'Beta Tester', 'advanced-ads' ); ?></th>
		<td>
			<p>
				<label>
					<select id="beta-tester" class="!border">
						<option value="disabled"><?php esc_html_e( 'Disabled', 'advanced-ads' ); ?></option>
						<option value="enabled"><?php esc_html_e( 'Enabled', 'advanced-ads' ); ?></option>
					</select>
				</label>
			</p>
			<p class="text-sm italic text-red-600"><?php esc_html_e( 'Please Note: We do not recommend updating to a beta version on production sites.', 'advanced-ads' ); ?></p>
		</td>
	</tr>
	</tbody>
</table>
