<?php
namespace Tests\PHPUnit\Ads;

use AdvancedAds\Ads\Ad_Factory;
use AdvancedAds\Ads\Ad_Repository as Ads_Repository;

class Ad_Repository extends \lucatume\WPBrowser\TestCase\WPTestCase
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	public function setUp() :void
	{
		// Before...
		parent::setUp();

		// Create an instance of Ad_Repository or use mocks/stubs if needed
		$this->factory    = new Ad_Factory();
		$this->repository = new Ads_Repository();
	}

	// Test to create a plain ad.
	public function testCreatePlainAd() :void {
		// Create.
		$ad = $this->factory->create_ad( 'plain' );
		$ad->set_title( 'Plain ad from Unit Testing' );
		$ad->set_description( 'This is the personal note' );
		$ad->set_content( 'This is the content of the plain ad.' );
		$ad_id = $ad->save();
		$this->assertIsInt($ad_id);
		$this->seeInDatabase( 'wp_posts', [ 'ID' => $ad_id, 'post_title' => 'Plain ad from Unit Testing' ] );
	}

	// Test to create and read plain ad.
	public function testCreateAndReadAdByNewId() :void {
		// Create.
		$ad = $this->factory->create_ad( 'plain' );
		$ad->set_title( 'Plain ad from Unit Testing' );
		$ad->set_description( 'This is the personal note' );
		$ad->set_content( 'This is the content of the plain ad.' );
		$ad_id = $ad->save();
		$this->assertIsInt($ad_id);

		// Read.
		$new_ad = $this->factory->get_ad( $ad_id );
		$this->assertSame( 'Plain ad from Unit Testing', $new_ad->get_title() );
		$this->assertSame( 'This is the personal note', $new_ad->get_description() );
		$this->assertSame( 'This is the content of the plain ad.', $new_ad->get_content() );
	}

	// Test to create, update and than read a plain ad.
	public function testUpdatePlainAd() :void {
		// Create.
		$ad = $this->factory->create_ad( 'plain' );
		$ad->set_title( 'Plain ad from Unit Testing' );
		$ad->set_description( 'This is the personal note' );
		$ad->set_content( 'This is the content of the plain ad.' );
		$ad_id = $ad->save();
		$this->assertIsInt($ad_id);

		// Read.
		$new_ad = $this->factory->get_ad( $ad_id );
		$this->assertSame( 'Plain ad from Unit Testing', $new_ad->get_title() );
		$this->assertSame( 'This is the personal note', $new_ad->get_description() );
		$this->assertSame( 'This is the content of the plain ad.', $new_ad->get_content() );

		// Update.
		$new_ad->set_title( 'Title updated' );
		$new_ad->save();

		// Read again and verify update.
		$updated_ad = $this->factory->get_ad( $new_ad->get_id() );
		$this->assertSame( 'Title updated', $new_ad->get_title() );
		$this->assertSame( 'Title updated', $updated_ad->get_title() );
	}
}
