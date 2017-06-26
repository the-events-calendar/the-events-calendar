<?php

namespace Tribe\Events;

use Tribe\Events\Tests\Testcases\Events_TestCase;
use Tribe__Events__Venue as Venue;

class VenueTest extends Events_TestCase {
	/**
	 * It should allow searching like in title
	 * @test
	 */
	public function it_should_allow_searching_like_in_title() {
		$venue_1 = $this->factory()->venue->create( [ 'post_title' => 'Venue Foo' ] );
		$venue_2 = $this->factory()->venue->create( [ 'post_title' => 'Venue Bar' ] );
		$venue_3 = $this->factory()->venue->create( [ 'post_title' => 'Venue Baz' ] );

		$sut = $this->make_instance();

		$this->assertCount( 3, $sut->find_like( 'Venue' ) );
		$this->assertCount( 3, $sut->find_like( 'venue' ) );
		$this->assertCount( 1, $sut->find_like( 'venue fo' ) );
		$this->assertCount( 1, $sut->find_like( 'Venue Foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Foo Venue' ) );
		$this->assertCount( 1, $sut->find_like( 'foo venue' ) );
	}

	/**
	 * @return Venue
	 */
	protected function make_instance() {
		return tribe( 'tec.linked-posts.venue' );
	}

}