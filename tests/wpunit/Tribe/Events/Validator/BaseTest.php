<?php

namespace Tribe\Events\Validator;

use Tribe\Events\Tests\Factories\Event;
use Tribe__Events__Validator__Base as Validator;

class BaseTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should be instantiatable
	 *
	 * @test
	 */
	public function be_instantiatable() {
		$this->assertInstanceOf( Validator::class, $this->make_instance() );
	}

	/**
	 * @return Validator
	 */
	protected function make_instance() {
		return new Validator();
	}

	/**
	 * It should not validate a non int venue ID as venue
	 *
	 * @test
	 */
	public function it_should_not_validate_a_non_int_venue_id_as_venue() {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_venue_id( 'foo' ) );
	}

	/**
	 * It should not validate a venue ID of 0 as venue
	 *
	 * @test
	 */
	public function it_should_not_validate_a_venue_id_of_0_as_venue() {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_venue_id( 0 ) );
	}

	/**
	 * It should not validate an empty venue ID as venue
	 *
	 * @test
	 */
	public function it_should_not_validate_an_empty_venue_id_as_venue() {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_venue_id( '' ) );
	}

	/**
	 * It should not validate a non existing post ID as venue
	 *
	 * @test
	 */
	public function it_should_not_validate_a_non_existing_post_id_as_venue() {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_venue_id( 23 ) );
	}

	/**
	 * It should validate an existing venue ID
	 *
	 * @test
	 */
	public function it_should_validate_an_existing_venue_id() {
		$venue = $this->factory()->post->create( [ 'post_type' => \Tribe__Events__Main::VENUE_POST_TYPE ] );

		$sut = $this->make_instance();

		$this->assertTrue( $sut->is_venue_id( $venue ) );
	}

	/**
	 * It should not validate a post that is not a venue as venue
	 *
	 * @test
	 */
	public function it_should_not_validate_a_post_that_is_not_a_venue_as_venue() {
		$venue = $this->factory()->post->create();

		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_venue_id( $venue ) );
	}

	/**
	 * It should not validate a non int organizer ID as organizer
	 *
	 * @test
	 */
	public function it_should_not_validate_a_non_int_organizer_id_as_organizer() {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_organizer_id( 'foo' ) );
	}

	/**
	 * It should not validate a organizer ID of 0 as organizer
	 *
	 * @test
	 */
	public function it_should_not_validate_a_organizer_id_of_0_as_organizer() {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_organizer_id( 0 ) );
	}

	/**
	 * It should not validate an empty organizer ID as organizer
	 *
	 * @test
	 */
	public function it_should_not_validate_an_empty_organizer_id_as_organizer() {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_organizer_id( '' ) );
	}

	/**
	 * It should not validate a non existing post ID as organizer
	 *
	 * @test
	 */
	public function it_should_not_validate_a_non_existing_post_id_as_organizer() {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_organizer_id( 23 ) );
	}

	/**
	 * It should validate an existing organizer ID
	 *
	 * @test
	 */
	public function it_should_validate_an_existing_organizer_id() {
		$organizer = $this->factory()->post->create( [ 'post_type' => \Tribe__Events__Main::ORGANIZER_POST_TYPE ] );

		$sut = $this->make_instance();

		$this->assertTrue( $sut->is_organizer_id( $organizer ) );
	}

	/**
	 * It should not validate a post that is not a organizer as organizer
	 *
	 * @test
	 */
	public function it_should_not_validate_a_post_that_is_not_a_organizer_as_organizer() {
		$organizer = $this->factory()->post->create();

		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_organizer_id( $organizer ) );
	}

	/**
	 * It should validate multiple organizer IDs
	 *
	 * @test
	 */
	public function it_should_validate_multiple_organizer_i_ds() {
		$organizers = $this->factory()->post->create_many( 3, [ 'post_type' => \Tribe__Events__Main::ORGANIZER_POST_TYPE ] );

		$sut = $this->make_instance();

		$this->assertTrue( $sut->is_organizer_id( $organizers ) );
	}

	/**
	 * It should not validate multiple organizer IDs if one is not valid
	 *
	 * @test
	 */
	public function it_should_not_validate_multiple_organizer_i_ds_if_one_is_not_valid() {
		$organizers = $this->factory()->post->create_many( 2, [ 'post_type' => \Tribe__Events__Main::ORGANIZER_POST_TYPE ] );
		$organizers[] = $this->factory()->post->create();

		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_organizer_id( $organizers ) );
	}

	public function bad_event_categories() {
		return [
			[ 0 ],
			[ '0' ],
			[ 'foo' ],
			[ 23 ], // not present
			[ '23' ], // not present
		];
	}

	/**
	 * Test is_event_category with bad categories
	 *
	 * @test
	 * @dataProvider bad_event_categories
	 */
	public function test_is_event_category_with_bad_categories( $category ) {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_event_category( $category ) );
	}

	/**
	 * Test is_event_category with good categories
	 *
	 * @test
	 */
	public function test_is_event_category_with_good_categories() {
		$category_1 = $this->factory()->term->create( [ 'slug' => 'foo', 'taxonomy' => \Tribe__Events__Main::TAXONOMY ] );
		$category_2 = $this->factory()->term->create( [ 'taxonomy' => \Tribe__Events__Main::TAXONOMY ] );

		$sut = $this->make_instance();

		$this->assertTrue( $sut->is_event_category( $category_1 ) );
		$this->assertTrue( $sut->is_event_category( $category_2 ) );
	}

	/**
	 * Test is_event_category with multiple categories
	 *
	 * @test
	 */
	public function test_is_event_category_with_multiple_categories() {
		$category_1 = $this->factory()->term->create( [ 'slug' => 'foo', 'taxonomy' => \Tribe__Events__Main::TAXONOMY ] );
		$category_2 = $this->factory()->term->create( [ 'taxonomy' => \Tribe__Events__Main::TAXONOMY ] );
		$category_3 = $this->factory()->category->create();

		$sut = $this->make_instance();

		$this->assertTrue( $sut->is_event_category( [ $category_1, $category_2 ] ) );
		$this->assertFalse( $sut->is_event_category( [ $category_1, $category_2, $category_3 ] ) );
	}

	/**
	 * Test is_organizer_id_list
	 *
	 * @test
	 */
	public function test_is_organizer_id_list() {
		$organizers = $this->factory()->post->create_many( 2, [ 'post_type' => \Tribe__Events__Main::ORGANIZER_POST_TYPE ] );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_organizer_id_list( [] ) );
		$this->assertFalse( $sut->is_organizer_id_list( [ ',' ] ) );
		$this->assertTrue( $sut->is_organizer_id_list( reset( $organizers ) ) );
		$this->assertTrue( $sut->is_organizer_id_list( [ reset( $organizers ) ] ) );
		$this->assertTrue( $sut->is_organizer_id_list( implode( ',', $organizers ) ) );
		$this->assertTrue( $sut->is_organizer_id_list( implode( ', ', $organizers ) ) );
		$this->assertTrue( $sut->is_organizer_id_list( implode( ' , ', $organizers ) ) );
		$this->assertTrue( $sut->is_organizer_id_list( implode( ' ,', $organizers ) ) );
		$this->assertFalse( $sut->is_organizer_id_list( implode( ' ,', array_merge( $organizers, [ 23 ] ) ) ) );
	}

	public function post_id_bad_inputs() {
		return [
			[ '' ],
			[ null ],
			[ false ],
			[ 'foo' ],
			[ '23' ],
			[ 23 ],
			[ 0 ],
			[ '0' ],
		];
	}

	/**
	 * Test is_event_id with bad inputs
	 *
	 * @test
	 * @dataProvider post_id_bad_inputs
	 */
	public function test_is_event_id_with_bad_inputs( $input ) {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_event_id( $input ) );
	}

	/**
	 * Test is_event_id
	 *
	 * @test
	 */
	public function test_is_event_id() {
		$id = $this->factory()->event->create();

		$sut = $this->make_instance();

		$this->assertTrue( $sut->is_event_id( $id ) );
		$this->assertTrue( $sut->is_event_id( '' . $id ) );
	}

	/**
	 * Test is_event_slug with the wrong slug
	 *
	 * @test
	 */
	public function test_is_event_slug_with_bad_inputs() {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_event_slug( 'nope-doesnt-exist' ) );
	}

	/**
	 * Test is_event_slug
	 *
	 * @test
	 */
	public function test_is_event_slug() {
		$id = $this->factory()->event->create();

		$post = get_post( $id );

		$sut = $this->make_instance();

		$this->assertTrue( $sut->is_event_slug( $post->post_name ) );
	}

	function setUp() {
		parent::setUp();
		$this->factory()->event = new Event();
	}
}
