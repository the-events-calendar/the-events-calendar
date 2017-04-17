<?php
namespace Tribe\Events\Revisions;

use Tribe__Events__Main as Main;
use Tribe__Events__Revisions__Post as Post;

class PostTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Post::class, $sut );
	}

	/**
	 * @return Post
	 */
	private function make_instance() {
		$post = $this->factory()->post->create_and_get( [ 'post_status' => 'publish' ] );

		return new Post( $post );
	}

	public function post_types() {
		return [
			[ 'post', 'Tribe__Events__Revisions__Post' ],
			[ 'page', 'Tribe__Events__Revisions__Post' ],
			[ 'tribe_events', 'Tribe__Events__Revisions__Event' ],
			[ 'tribe_venue', 'Tribe__Events__Revisions__Venue' ],
			[ 'tribe_organizer', 'Tribe__Events__Revisions__Organizer' ],
		];
	}

	/**
	 * @test
	 * it should return the right type of revision object
	 *
	 * @dataProvider post_types
	 */
	public function it_should_return_the_right_type_of_revision_object( $post_type, $expected_class ) {
		if ( in_array( $post_type, [ Main::ORGANIZER_POST_TYPE, Main::VENUE_POST_TYPE ] ) ) {
			$this->markTestSkipped( ucfirst( str_replace( 'tribe_', '', $post_type ) ) . ' revisions are not suported yet!' );
		}
		$id       = $this->factory()->post->create( [ 'post_type' => $post_type, 'post_status' => 'publish' ] );
		$revision = get_post( wp_save_post_revision( $id ) );

		$this->assertInstanceOf( $expected_class, Post::new_from_post( $revision ) );
	}
}