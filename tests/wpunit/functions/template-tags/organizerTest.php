<?php

namespace TEC\Test\functions\template_tags;

use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Testcases\Events_TestCase;

class organizerTest extends Events_TestCase {

	/**
	 * It should allow getting found posts when querying organizers
	 *
	 * @test
	 */
	public function should_allow_getting_found_posts_when_querying_organizers() {
		$this->factory()->organizer->create_many( 5 );
		$this->assertEquals( 5, tribe_get_organizers( false, - 1, true, [ 'found_posts' => true ] ) );
	}

	public function truthy_and_falsy_values() {
		return [
			[ 'true', true ],
			[ 'true', true ],
			[ '0', false ],
			[ '1', true ],
			[ 0, false ],
			[ 1, true ],
		];
	}

	/**
	 * It should allow truthy and falsy values for the found_posts argument
	 *
	 * @test
	 * @dataProvider truthy_and_falsy_values
	 */
	public function should_allow_truthy_and_falsy_values_for_the_found_posts_argument( $found_posts, $bool ) {
		$this->factory()->organizer->create_many( 5 );
		$this->assertEquals(
			tribe_get_organizers( false, - 1, true, [ 'found_posts' => $found_posts ] ),
			tribe_get_organizers( false, - 1, true, [ 'found_posts' => $bool ] )
		);
	}

	/**
	 * It should override posts_per_page and paged arguments when using found_posts
	 *
	 * @test
	 */
	public function should_override_posts_per_page_and_paged_arguments_when_using_found_posts() {
		$this->factory()->organizer->create_many( 5 );
		$found_posts = tribe_get_organizers( false, - 1, true, [ 'found_posts' => true, 'paged' => 2 ] );
		$this->assertEquals( 5, $found_posts );
	}

	/**
	 * It should return 0 when no posts are found and found_posts is set
	 *
	 * @test
	 */
	public function should_return_0_when_no_posts_are_found_and_found_posts_is_set() {
		$found_posts = tribe_get_organizers( false, - 1, true, [ 'found_posts' => true ] );
		$this->assertEquals( 0, $found_posts );
	}

	/**
	 * Test multiple combinations possibles for the sensitization of organizer lists when an ordered list is presented.
	 *
	 * @since 4.6.15
	 */
	public function test_tribe_sanitize_organizers() {
		$this->expected_deprecated[] = 'tribe_sanitize_organizers';

		// Test empty values
		$this->assertEquals( [], tribe_sanitize_organizers() );
		$this->assertEquals( [], tribe_sanitize_organizers( [], [] ) );
		// Return original value if no order value is present
		$this->assertEquals( [ 1, 2, 3 ], tribe_sanitize_organizers( [ 1, 2, 3 ] ) );
		// Test only values that are not ordered
		$this->assertEquals( [ 1, 2, 3 ], tribe_sanitize_organizers( [ 1, 2, 3 ], [] ) );
		// Same values on current and ordered side
		$this->assertEquals( [ 1, 2, 3 ], tribe_sanitize_organizers( [ 1, 2, 3 ], [ 1, 2, 3 ] ) );
		// Test values that are not part of the ordered list
		$this->assertEquals( [ 4 ], tribe_sanitize_organizers( [ 4 ], [ 1, 2, 3 ] ) );
		// Make sure order is respected if a new values are present
		$this->assertEquals( [ 1, 2, 3, 4, 5, 6 ], tribe_sanitize_organizers( [ 4, 2, 5, 1, 3, 6 ], [ 1, 2, 3 ] ) );
		// Test only a single ordered value the remaining are placed are entered
		$this->assertEquals( [ 2, 1, 3, 4, 5, 6 ], tribe_sanitize_organizers( [ 1, 2, 3, 4, 5, 6 ], [ 2 ] ) );
	}

	/**
	 * It should allow getting an organizer decorated post object
	 *
	 * @test
	 */
	public function should_allow_getting_an_organizer_decorated_post_object() {
		$this->assertNull( tribe_get_organizer_object( PHP_INT_MAX ) );

		$organizer_id = $this->given_an_organizer();
		$organizer    = tribe_get_organizer_object( $organizer_id );

		$this->assertInstanceOf( \WP_Post::class, $organizer );
		$this->assertEquals( 'Indiana Jones', $organizer->post_title );
		$this->assertEquals( '11223344', $organizer->phone );
		$this->assertEquals( 'http://the.org/anizer', $organizer->website );
		$this->assertEquals( 'indy@the.org', $organizer->email );
	}

	protected function given_an_organizer() {
		$organizer_id = ( new Organizer() )->create( [
			'post_title' => 'Indiana Jones',
			'meta_input' => [
				'_OrganizerPhone'   => '11223344',
				'_OrganizerWebsite' => 'http://the.org/anizer',
				'_OrganizerEmail'   => 'indy@the.org',
			]
		] );

		return $organizer_id;
	}

	/**
	 * It should return the data of the global organizer when set
	 *
	 * @test
	 */
	public function should_return_the_data_of_the_global_organizer_when_set() {
		$organizer_id       = $this->given_an_organizer();
		$GLOBALS['post']    = get_post( $organizer_id );
		$GLOBALS['post_id'] = $organizer_id;

		$organizer = tribe_get_organizer_object( null );

		$this->assertInstanceOf( \WP_Post::class, $organizer );
		$this->assertEquals( 'Indiana Jones', $organizer->post_title );
		$this->assertEquals( '11223344', $organizer->phone );
		$this->assertEquals( 'http://the.org/anizer', $organizer->website );
		$this->assertEquals( 'indy@the.org', $organizer->email );
	}

	/**
	 * It should allow getting the organizer as associative_array
	 *
	 * @test
	 */
	public function should_allow_getting_the_organizer_as_associative_array() {
		$organizer_id = $this->given_an_organizer();

		$organizer = tribe_get_organizer_object( $organizer_id, ARRAY_A );

		$this->assertInternalType( 'array', $organizer );
		$this->assertCount( 0, array_filter( array_keys( $organizer ), 'is_int' ) );
		$this->assertEquals( 'Indiana Jones', $organizer['post_title'] );
		$this->assertEquals( '11223344', $organizer['phone'] );
		$this->assertEquals( 'http://the.org/anizer', $organizer['website'] );
		$this->assertEquals( 'indy@the.org', $organizer['email'] );
	}

	/**
	 * It should allow getting the organizer as an array
	 *
	 * @test
	 */
	public function should_allow_getting_the_organizer_as_an_array() {
		$organizer_id = $this->given_an_organizer();

		$organizer = tribe_get_organizer_object( $organizer_id, ARRAY_N );

		$this->assertInternalType( 'array', $organizer );
		$this->assertCount( count( $organizer ), array_filter( array_keys( $organizer ), 'is_int' ) );
	}
}
