<?php

namespace Tribe\Events\ORM\Venues;

use Tribe__Events__Main as Main;

class CreateTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * It should allow creating an venue
	 *
	 * @test
	 */
	public function should_allow_creating_an_venue() {
		$args  = [
			'title' => 'A test venue',
		];
		$venue = tribe_venues()->set_args( $args )->create();

		$this->assertInstanceOf( \WP_Post::class, $venue );
		$this->assertEquals( $args['title'], $venue->post_title );
		$this->assertEquals( '', $venue->post_content );
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
	 * It should allow creating a venue with aliases
	 *
	 * @test
	 * @dataProvider meta_key_aliases
	 */
	public function should_allow_creating_a_venue_with_aliases( $alias, $meta_key, $value ) {
		$args = [
			'title' => 'A test venue',
			$alias  => $value,
		];

		if ( 'post_title' === $meta_key ) {
			unset( $args['title'] );
		}

		$venue = tribe_venues()->set_args( $args )->create();

		$this->assertInstanceOf( \WP_Post::class, $venue );
		$this->assertEquals( $value, $venue->{$meta_key} );
	}

	/**
	 * It should return false if trying to create venue without min requirements
	 *
	 * @test
	 */
	public function should_return_false_if_trying_to_create_venue_without_min_requirements() {
		$args  = [];
		$venue = tribe_venues()->set_args( $args )->create();

		$this->assertFalse( $venue );
	}
}
