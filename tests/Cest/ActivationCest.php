<?php
namespace Tests\Cest;

use Tests\Support\CestTester;

class ActivationCest {

    public function test_it_deactivates_activates_correctly(CestTester $I): void {
        $I->loginAsAdmin();
        $I->amOnPluginsPage();

		// Deactivate
		$I->seePluginActivated('advanced-ads');
		$I->deactivatePlugin('advanced-ads');

        // Activate
		$I->seePluginDeactivated('advanced-ads');
        $I->activatePlugin('advanced-ads');
		$I->seePluginActivated('advanced-ads');
    }
}
