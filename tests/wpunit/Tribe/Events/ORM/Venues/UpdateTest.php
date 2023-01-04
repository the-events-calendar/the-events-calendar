<?php

namespace Tribe\Events\ORM\Venues;

use Tribe\Events\Test\Factories\Venue;

class UpdateTest extends \Codeception\TestCase\WPTestCase {

	public function setUp(): void {
		parent::setUp();
		$this->factory()->venue = new Venue();
	}

	public function meta_key_aliases() {
		return [
			'venue'         => [ 'venue', 'post_title', 'A venue' ],
			'address'       => [ 'address', '_VenueAddress', '123 Main St' ],
			'city'          => [ 'city', '_VenueCity', 'Euless' ],
			'state'         => [ 'state', '_VenueState', 'TX' ],
			'province'      => [ 'province', '_VenueProvince', 'TX' ],
			'stateprovince' => [ 'stateprovince', '_VenueStateProvince', 'TX' ],
			'zip'           => [ 'zip', '_VenueZip', '76039' ],
			'postal_code'   => [ 'postal_code', '_VenueZip', '76039' ],
			'country'       => [ 'country', '_VenueCountry', 'US' ],
			'phone'         => [ 'phone', '_VenuePhone', '123-123-4567' ],
			'website'       => [ 'website', '_VenueURL', 'https://test.com' ],
		];
	}

	/**
	 * It should allow updating some venue meta with aliases
	 *
	 * @test
	 * @dataProvider meta_key_aliases
	 */
	public function should_allow_updating_some_venue_meta_with_aliases( $alias, $meta_key, $value ) {
		$venue = $this->factory()->venue->create();

		tribe_venues()
			->where( 'id', $venue )
			->set( $alias, $value )->save();

		$venue = get_post( $venue );

		$this->assertEquals( $value, $venue->{$meta_key} );
	}
}
