<?php
/**
 * Tools page.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

?>
<div class="wrap advads-wrap">
	<?php
	$this->get_header(
		[
			'title'      => __( 'Status and Tools', 'advanced-ads' ),
			'manual_url' => 'tools',
		]
	);

	$this->get_tabs_menu();
	$this->get_tab_content();
	?>
</div>
