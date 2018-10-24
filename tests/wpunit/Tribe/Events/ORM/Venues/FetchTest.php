<?php

namespace Tribe\Events\ORM\Venues;

use Tribe\Events\Test\Factories\Venue;

class FetchTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->factory()->venue = new Venue();
	}

	/**
	 * It should allow getting venues by address
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_address() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueAddress' => '123 Common Main St' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'address', 'Common' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'address', '%Common%' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'address', '/\d+ Common \w+ St/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by city
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_city() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueCity' => 'Bright Rainbow Village' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'city', 'Rainbow' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'city', '%Rainbow%' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'city', '/\w+ Rainbow \w+/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by country
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_country() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueCountry' => 'United States of America' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'country', 'States' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'country', '%States%' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'country', '/\w+ States \w+ America/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by phone
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_phone() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenuePhone' => '123-555-9999' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'phone', '123' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'phone', '%123%' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'phone', '/\d+-555-\d+/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by postal_code
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_postal_code() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueZip' => '10025' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'postal_code', '25' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'postal_code', '1%25' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'postal_code', '/1\d+25/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by province
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_province() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueProvince' => 'New Brunswick' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'state', 'New' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'state', 'New%' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'state', '/New \w+/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by state
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_state() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueState' => 'New York' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'state', 'New' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'state', 'New%' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'state', '/New \w+/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by state_province
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_state_province() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueStateProvince' => 'New York' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'state_province', 'New' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'state_province', 'New%' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'state_province', '/New \w+/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by website
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_website() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueURL' => 'https://twitter.com/roblovestwitter' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'website', '://twitter.com/' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'website', '%://twitter.com/%' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'website', '/.*:\/\/twitter.com\/.*/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

}
