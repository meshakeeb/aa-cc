<?php
namespace Tests\PHPUnit\Ads;

use AdvancedAds\Ads\Ads as AdsSinelton;

class Ads extends \lucatume\WPBrowser\TestCase\WPTestCase
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	// Test initializing the Ads class
	public function testInitialize() {
		$ads = new AdsSinelton();
		$ads->initialize();

		$this->assertInstanceOf('AdvancedAds\Ads\Ad_Factory', $ads->factory);
		$this->assertInstanceOf('AdvancedAds\Ads\Ad_Repository', $ads->repository);
		$this->assertInstanceOf('AdvancedAds\Ads\Ad_Types', $ads->types);
		// Add more assertions based on the expected initialization behavior
	}
}
