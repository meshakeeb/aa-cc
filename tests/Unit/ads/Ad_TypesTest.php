<?php
namespace Tests\PHPUnit\Ads;

use AdvancedAds\Ads\Ad_Types as Types;
use AdvancedAds\Interfaces\Ad_Type;
use ReflectionClass;

class Ad_Types extends \lucatume\WPBrowser\TestCase\WPTestCase
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var Types
	 */
	protected $adTypes;

	public function setUp() :void
	{
		// Before...
		parent::setUp();

		$this->adTypes = new Types();

		$reflectionClass = new ReflectionClass($this->adTypes);
		$method = $reflectionClass->getMethod('register_default_types');
		$method->setAccessible(true);

		$method->invoke($this->adTypes);
	}

	// Test registering default types
	public function testRegisterDefaultTypes() {
		$registeredTypes = $this->adTypes->get_types();

		// Check if default types are registered
		$this->assertArrayHasKey('content', $registeredTypes);
		$this->assertArrayHasKey('dummy', $registeredTypes);
		$this->assertArrayHasKey('group', $registeredTypes);
		$this->assertArrayHasKey('image', $registeredTypes);
		$this->assertArrayHasKey('plain', $registeredTypes);
	}

	// Test checking for a specific type
	public function testHasType() {
		$hasType = $this->adTypes->has_type('content');

		$this->assertTrue($hasType);
		// Add more assertions based on the expected behavior of checking for a type
	}

	// Test getting a specific type
	public function testGetType() {
		$getType = $this->adTypes->get_type('content');

		$this->assertInstanceOf( Ad_Type::class, $getType);
		// Add more assertions based on the expected behavior of getting a type
	}
}
