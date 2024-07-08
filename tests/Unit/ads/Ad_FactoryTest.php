<?php
namespace Tests\PHPUnit\Ads;

use AdvancedAds\Ads\Ad_Content;
use AdvancedAds\Ads\Ad_Factory as AdFactory;

class Ad_Factory extends \lucatume\WPBrowser\TestCase\WPTestCase
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	public function setUp() :void
	{
		// Before...
		parent::setUp();

		$this->factory = new AdFactory();
	}

	// Test if plugin is active
	public function testPluginActive(): void {
		$this->assertTrue(is_plugin_active('advanced-ads/advanced-ads.php'));
	}

	// Test creating an ad with an existing ad type
	public function testCreateAdWithExistingType() {
		// Call the create_ad method with an existing ad type
		$result = $this->factory->create_ad('content');

		// Assert that the result is an instance of Ad_Content or an Ad object
		$this->assertInstanceOf(Ad_Content::class, $result);
	}

	// Test creating an ad with an non-existing ad type
	public function testCreateAdWithNonExistingType() {
		// Call the create_ad method with an existing ad type
		$result = $this->factory->create_ad('didnt_exists');

		// Assert that the result is an instance of Ad_Content or an Ad object
		$this->assertFalse($result);
	}

	// Tests if object is of right class
	public function testFactoryCreation(): void {
		$this->assertInstanceOf(AdFactory::class, $this->factory);
	}

	// Test if get_ad() returns an ad object when a valid ID is passed
	public function testGetAdWithValidId() {
		$result  = $this->factory->get_ad(22);
		$this->assertInstanceOf('AdvancedAds\Abstracts\Ad', $result);
	}

	// Test if get_ad() returns false when an invalid ID is passed
	public function testGetAdWithInvalidId() {
		$result  = $this->factory->get_ad(3);
		$this->assertFalse($result);
	}

	// Test if get_ad() returns false when false is passed as ID
	public function testGetAdWithFalseId() {
		$result = $this->factory->get_ad(false);
		$this->assertFalse($result);
	}

	// Test if get_ad() returns an ad object when an Ad object is passed
	public function testGetAdWithAdObject() {
		$mockAd = new \AdvancedAds\Ads\Ad_Dummy(22);
		$result = $this->factory->get_ad($mockAd);
		$this->assertInstanceOf('AdvancedAds\Abstracts\Ad', $result);
	}

	// Test if get_ad() returns an ad object when a WP_Post object is passed
	public function testGetAdWithWPPostObject() {
		$mockPost = \WP_Post::get_instance(22);
		$result = $this->factory->get_ad($mockPost);
		$this->assertInstanceOf('AdvancedAds\Abstracts\Ad', $result);
	}
}
