<?php

namespace Tribe\Events\ORM\Organizers;

use Tribe\Events\Test\Factories\Organizer;

class UpdateTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
		$this->factory()->organizer = new Organizer();
	}

	public function meta_key_aliases() {
		return [
			'organizer' => [ 'organizer', 'post_title', 'An organizer' ],
			'phone'     => [ 'phone', '_OrganizerPhone', '123-123-4567' ],
			'website'   => [ 'website', '_OrganizerWebsite', 'https://test.com' ],
			'email'     => [ 'email', '_OrganizerEmail', 'bigcheese@test.com' ],
		];
	}

	/**
	 * It should allow updating some organizer meta with aliases
	 *
	 * @test
	 * @dataProvider meta_key_aliases
	 */
	public function should_allow_updating_some_organizer_meta_with_aliases( $alias, $meta_key, $value ) {
		$organizer = $this->factory()->organizer->create();

		tribe_organizers()
			->where( 'id', $organizer )
			->set( $alias, $value )->save();

		$organizer = get_post( $organizer );

		$this->assertEquals( $value, $organizer->{$meta_key} );
	}
}