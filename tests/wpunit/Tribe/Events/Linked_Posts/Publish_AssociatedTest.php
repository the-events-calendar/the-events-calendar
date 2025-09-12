<?php

namespace Tribe\Events\Linked_Posts;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe__Events__Main;
use Tribe__Events__Organizer;
use Tribe__Events__Venue;

class Publish_AssociatedTest extends WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		$this->factory()->event     = new Event();
		$this->factory()->organizer = new Organizer();
		$this->factory()->venue     = new Venue();
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function it_should_publish_non_private_linked_posts() {
		$venue_1_id     = $this->factory()->venue->create(
			[
				'post_status' => 'draft',
			]
		);
		$venue_2_id     = $this->factory()->venue->create(
			[
				'post_status' => 'private',
			]
		);
		$venue_3_id     = $this->factory()->venue->create(
			[
				'post_status' => 'pending',
			]
		);
		$organizer_1_id = $this->factory()->organizer->create(
			[
				'post_status' => 'draft',
			]
		);
		$organizer_2_id = $this->factory()->organizer->create(
			[
				'post_status' => 'future',
				'post_date'   => date( 'Y-m-d 00:00:00', strtotime( '+1 month' ) ),
			]
		);
		$organizer_3_id = $this->factory()->organizer->create(
			[
				'post_status' => 'trash',
			]
		);

		$event_args = [
			'post_status' => 'publish',
		];

		$event_id = $this->factory()->event->create( $event_args );

		$meta_ids = [
			'venue'     => [
				$venue_1_id,
				$venue_2_id,
				$venue_3_id,
			],
			'organizer' => [
				$organizer_1_id,
				$organizer_2_id,
				$organizer_3_id,
			],
		];

		foreach ( $meta_ids['venue'] as $id ) {
			add_post_meta( $event_id, '_EventVenueID', $id );
		}

		foreach ( $meta_ids['organizer'] as $id ) {
			add_post_meta( $event_id, '_EventOrganizerID', $id );
		}

		/** @var Tribe__Events__Main $main */
		$main = tribe( 'tec.main' );

		$event       = tribe_events()->where( 'ID', $event_id )->first();
		$venue_1     = get_post( $venue_1_id );
		$venue_2     = get_post( $venue_2_id );
		$venue_3     = get_post( $venue_3_id );
		$organizer_1 = get_post( $organizer_1_id );
		$organizer_2 = get_post( $organizer_2_id );
		$organizer_3 = get_post( $organizer_3_id );

		$this->assertEquals( 'draft', $venue_1->post_status );
		$this->assertEquals( 'private', $venue_2->post_status );
		$this->assertEquals( 'pending', $venue_3->post_status );
		$this->assertEquals( 'draft', $organizer_1->post_status );
		$this->assertEquals( 'future', $organizer_2->post_status );
		$this->assertEquals( 'trash', $organizer_3->post_status );

		$main->publishAssociatedTypes( $event_id, $event );

		$venue_1     = get_post( $venue_1_id );
		$venue_2     = get_post( $venue_2_id );
		$venue_3     = get_post( $venue_3_id );
		$organizer_1 = get_post( $organizer_1_id );
		$organizer_2 = get_post( $organizer_2_id );
		$organizer_3 = get_post( $organizer_3_id );

		$this->assertEquals( 'publish', $venue_1->post_status );
		$this->assertEquals( 'private', $venue_2->post_status );
		$this->assertEquals( 'publish', $venue_3->post_status );
		$this->assertEquals( 'publish', $organizer_1->post_status );
		$this->assertEquals( 'publish', $organizer_2->post_status );
		$this->assertEquals( 'publish', $organizer_3->post_status );
	}

	/**
	 * Provides post statuses for venues/organizers before publishing.
	 *
	 * @return \Generator
	 */
	public function post_status_provider() {
		yield 'draft goes publish' => [
			[ 'post_status' => 'draft' ],
			'publish',
		];

		yield 'pending goes publish' => [
			[ 'post_status' => 'pending' ],
			'publish',
		];

		yield 'future goes publish' => [
			[
				'post_status' => 'future',
				'post_date'   => date( 'Y-m-d 00:00:00', strtotime( '+1 month' ) ),
			],
			'publish',
		];

		yield 'private stays private' => [
			[ 'post_status' => 'private' ],
			'private',
		];

		yield 'trash goes publish' => [
			[ 'post_status' => 'trash' ],
			'publish',
		];
	}

	/**
	 * @test
	 * @dataProvider post_status_provider
	 */
	public function it_should_publish_or_skip_based_on_initial_status( $post_args, $expected_status ) {
		$venue_id     = $this->factory()->venue->create( $post_args );
		$organizer_id = $this->factory()->organizer->create( $post_args );

		$event_id = $this->factory()->event->create( [ 'post_status' => 'publish' ] );

		// Link both venue and organizer to the event
		add_post_meta( $event_id, '_EventVenueID', $venue_id );
		add_post_meta( $event_id, '_EventOrganizerID', $organizer_id );

		/** @var Tribe__Events__Main $main */
		$main  = tribe( 'tec.main' );
		$event = tribe_events()->where( 'ID', $event_id )->first();

		// Assert pre-publish status
		$this->assertEquals( $post_args['post_status'], get_post_status( $venue_id ) );
		$this->assertEquals( $post_args['post_status'], get_post_status( $organizer_id ) );

		// Publish linked posts
		$main->publishAssociatedTypes( $event_id, $event );

		// Assert expected post-publish status
		$this->assertEquals( $expected_status, get_post_status( $venue_id ) );
		$this->assertEquals( $expected_status, get_post_status( $organizer_id ) );
	}

	/**
	 * @test
	 * @dataProvider post_status_provider
	 */
	public function it_should_generate_slug_for_organizer_on_publish( $post_args, $expected_status ) {
		$post_args['post_title'] = 'My Test Organizer';
		$post_args['post_name']  = ''; // Start with no slug

		$organizer_id = $this->factory()->organizer->create( $post_args );
		$event_id     = $this->factory()->event->create( [ 'post_status' => 'publish' ] );

		add_post_meta( $event_id, '_EventOrganizerID', $organizer_id );

		/** @var Tribe__Events__Main $main */
		$main  = tribe( 'tec.main' );
		$event = tribe_events()->where( 'ID', $event_id )->first();

		// Publish
		$main->publishAssociatedTypes( $event_id, $event );

		// Slug should be generated if expected status is publish
		$post = get_post( $organizer_id );
		if ( $expected_status === 'publish' ) {
			$this->assertNotEmpty( $post->post_name );
			$this->assertEquals( sanitize_title( $post->post_title ), $post->post_name );
		}
	}

	/**
	 * @test
	 */
	public function it_should_generate_unique_and_fallback_slugs_on_publish() {
		// Organizer with a real title but no slug
		$organizer_with_title_id = $this->factory()->organizer->create(
			[
				'post_title'  => 'Duplicate Title',
				'post_name'   => '', // Start empty
				'post_status' => 'draft',
			]
		);

		// Organizer with the same title, should force unique slug
		$organizer_duplicate_id = $this->factory()->organizer->create(
			[
				'post_title'  => 'Duplicate Title',
				'post_name'   => '', // Start empty
				'post_status' => 'draft',
			]
		);

		// Organizer with no title, should force fallback title
		$organizer_no_title_id = $this->factory()->organizer->create(
			[
				'post_title'  => '',
				'post_name'   => '', // Start empty
				'post_status' => 'draft',
			]
		);

		// Create the event to link organizers to
		$event_id = $this->factory()->event->create( [ 'post_status' => 'publish' ] );

		add_post_meta( $event_id, '_EventOrganizerID', $organizer_with_title_id );
		add_post_meta( $event_id, '_EventOrganizerID', $organizer_duplicate_id );
		add_post_meta( $event_id, '_EventOrganizerID', $organizer_no_title_id );

		/** @var Tribe__Events__Main $main */
		$main  = tribe( 'tec.main' );
		$event = tribe_events()->where( 'ID', $event_id )->first();

		// Publish organizers through our updated logic
		$main->publishAssociatedTypes( $event_id, $event );

		// Fetch posts after publishing
		$organizer_with_title = get_post( $organizer_with_title_id );
		$organizer_duplicate  = get_post( $organizer_duplicate_id );
		$organizer_no_title   = get_post( $organizer_no_title_id );

		// 1. Slug should be generated from the title
		$this->assertNotEmpty( $organizer_with_title->post_name );
		$this->assertStringStartsWith( 'duplicate-title', $organizer_with_title->post_name );

		// 2. Duplicate title should get a unique slug
		$this->assertNotEquals( $organizer_with_title->post_name, $organizer_duplicate->post_name );
		$this->assertStringStartsWith( 'duplicate-title', $organizer_duplicate->post_name );

		// 3. No title should get a fallback slug (organizer-ID)
		$this->assertNotEmpty( $organizer_no_title->post_name );
		$this->assertStringStartsWith( 'organizer-', $organizer_no_title->post_name );
	}

	/**
	 * @test
	 */
	public function it_should_generate_unique_and_fallback_slugs_for_venues_on_publish() {
		// Venue with a real title but no slug
		$venue_with_title_id = $this->factory()->venue->create(
			[
				'post_title'  => 'Duplicate Venue Title',
				'post_name'   => '', // Start empty
				'post_status' => 'draft',
			]
		);

		// Venue with the same title, should force unique slug
		$venue_duplicate_id = $this->factory()->venue->create(
			[
				'post_title'  => 'Duplicate Venue Title',
				'post_name'   => '', // Start empty
				'post_status' => 'draft',
			]
		);

		// Venue with no title, should force fallback title
		$venue_no_title_id = $this->factory()->venue->create(
			[
				'post_title'  => '',
				'post_name'   => '', // Start empty
				'post_status' => 'draft',
			]
		);

		// Create the event to link venues to
		$event_id = $this->factory()->event->create( [ 'post_status' => 'publish' ] );

		add_post_meta( $event_id, '_EventVenueID', $venue_with_title_id );
		add_post_meta( $event_id, '_EventVenueID', $venue_duplicate_id );
		add_post_meta( $event_id, '_EventVenueID', $venue_no_title_id );

		/** @var Tribe__Events__Main $main */
		$main  = tribe( 'tec.main' );
		$event = tribe_events()->where( 'ID', $event_id )->first();

		// Publish venues through our updated logic
		$main->publishAssociatedTypes( $event_id, $event );

		// Fetch posts after publishing
		$venue_with_title = get_post( $venue_with_title_id );
		$venue_duplicate  = get_post( $venue_duplicate_id );
		$venue_no_title   = get_post( $venue_no_title_id );

		// 1. Slug should be generated from the title
		$this->assertNotEmpty( $venue_with_title->post_name );
		$this->assertStringStartsWith( 'duplicate-venue-title', $venue_with_title->post_name );

		// 2. Duplicate title should get a unique slug
		$this->assertNotEquals( $venue_with_title->post_name, $venue_duplicate->post_name );
		$this->assertStringStartsWith( 'duplicate-venue-title', $venue_duplicate->post_name );

		// 3. No title should get a fallback slug (venue-ID)
		$this->assertNotEmpty( $venue_no_title->post_name );
		$this->assertStringStartsWith( 'venue-', $venue_no_title->post_name );
	}

	/**
	 * @test
	 */
	public function it_should_early_return_for_non_published_posts() {
		$event_id = $this->factory()->event->create( [ 'post_status' => 'draft' ] );
		$venue_id = $this->factory()->venue->create( [ 'post_status' => 'draft' ] );

		add_post_meta( $event_id, '_EventVenueID', $venue_id );

		/** @var Tribe__Events__Main $main */
		$main = tribe( 'tec.main' );
		$event = tribe_events()->where( 'ID', $event_id )->first();

		// Should not publish the venue
		$main->publishAssociatedTypes( $event_id, $event );

		$this->assertEquals( 'draft', get_post_status( $venue_id ) );
	}

	/**
	 * @test
	 */
	public function it_should_handle_empty_linked_posts() {
		$event_id = $this->factory()->event->create( [ 'post_status' => 'publish' ] );

		/** @var Tribe__Events__Main $main */
		$main = tribe( 'tec.main' );
		$event = tribe_events()->where( 'ID', $event_id )->first();

		// Should not throw any errors
		$main->publishAssociatedTypes( $event_id, $event );

		$this->assertTrue( true ); // If we get here without errors, test passes
	}

	/**
	 * @test
	 */
	public function it_should_restore_action_hooks_after_publishing() {
		$event_id = $this->factory()->event->create( [ 'post_status' => 'publish' ] );
		$venue_id = $this->factory()->venue->create( [ 'post_status' => 'draft' ] );

		add_post_meta( $event_id, '_EventVenueID', $venue_id );

		/** @var Tribe__Events__Main $main */
		$main = tribe( 'tec.main' );
		$event = tribe_events()->where( 'ID', $event_id )->first();

		// Verify actions are initially present
		$this->assertNotFalse( has_action( 'save_post_' . Tribe__Events__Venue::POSTTYPE, [ $main, 'save_venue_data' ] ) );
		$this->assertNotFalse( has_action( 'save_post_' . Tribe__Events__Organizer::POSTTYPE, [ $main, 'save_organizer_data' ] ) );

		$main->publishAssociatedTypes( $event_id, $event );

		// Verify actions are restored
		$this->assertNotFalse( has_action( 'save_post_' . Tribe__Events__Venue::POSTTYPE, [ $main, 'save_venue_data' ] ) );
		$this->assertNotFalse( has_action( 'save_post_' . Tribe__Events__Organizer::POSTTYPE, [ $main, 'save_organizer_data' ] ) );
	}

	/**
	 * Provides test cases for different post status combinations.
	 *
	 * @return \Generator
	 */
	public function post_status_combination_provider() {
		yield 'draft venue and organizer' => [
			[ 'post_status' => 'draft' ],
			[ 'post_status' => 'draft' ],
			'publish',
			'publish',
		];

		yield 'pending venue and organizer' => [
			[ 'post_status' => 'pending' ],
			[ 'post_status' => 'pending' ],
			'publish',
			'publish',
		];

		yield 'future venue and organizer' => [
			[ 'post_status' => 'future', 'post_date' => date( 'Y-m-d 00:00:00', strtotime( '+1 month' ) ) ],
			[ 'post_status' => 'future', 'post_date' => date( 'Y-m-d 00:00:00', strtotime( '+1 month' ) ) ],
			'publish',
			'publish',
		];

		yield 'private venue and organizer' => [
			[ 'post_status' => 'private' ],
			[ 'post_status' => 'private' ],
			'private',
			'private',
		];

		yield 'trash venue and organizer' => [
			[ 'post_status' => 'trash' ],
			[ 'post_status' => 'trash' ],
			'publish',
			'publish',
		];
	}

	/**
	 * @test
	 * @dataProvider post_status_combination_provider
	 */
	public function it_should_handle_venue_and_organizer_status_combinations( $venue_args, $organizer_args, $expected_venue_status, $expected_organizer_status ) {
		$venue_id = $this->factory()->venue->create( $venue_args );
		$organizer_id = $this->factory()->organizer->create( $organizer_args );
		$event_id = $this->factory()->event->create( [ 'post_status' => 'publish' ] );

		add_post_meta( $event_id, '_EventVenueID', $venue_id );
		add_post_meta( $event_id, '_EventOrganizerID', $organizer_id );

		/** @var Tribe__Events__Main $main */
		$main = tribe( 'tec.main' );
		$event = tribe_events()->where( 'ID', $event_id )->first();

		$main->publishAssociatedTypes( $event_id, $event );

		$this->assertEquals( $expected_venue_status, get_post_status( $venue_id ) );
		$this->assertEquals( $expected_organizer_status, get_post_status( $organizer_id ) );
	}

	/**
	 * Provides test cases for slug generation scenarios.
	 *
	 * @return \Generator
	 */
	public function slug_generation_provider() {
		yield 'venue with title' => [
			'venue',
			'Test Venue Title',
			'test-venue-title',
		];

		yield 'organizer with title' => [
			'organizer',
			'Test Organizer Title',
			'test-organizer-title',
		];

		yield 'venue with empty title' => [
			'venue',
			'',
			'venue-', // Will be followed by ID
		];

		yield 'organizer with empty title' => [
			'organizer',
			'',
			'organizer-', // Will be followed by ID
		];
	}

	/**
	 * @test
	 * @dataProvider slug_generation_provider
	 */
	public function it_should_generate_slugs_correctly( $post_type, $title, $expected_slug_prefix ) {
		$post_args = [
			'post_status' => 'draft',
			'post_title'  => $title,
			'post_name'   => '', // Start with no slug
		];

		if ( $post_type === 'venue' ) {
			$post_id = $this->factory()->venue->create( $post_args );
			$meta_key = '_EventVenueID';
		} else {
			$post_id = $this->factory()->organizer->create( $post_args );
			$meta_key = '_EventOrganizerID';
		}

		$event_id = $this->factory()->event->create( [ 'post_status' => 'publish' ] );
		add_post_meta( $event_id, $meta_key, $post_id );

		/** @var Tribe__Events__Main $main */
		$main = tribe( 'tec.main' );
		$event = tribe_events()->where( 'ID', $event_id )->first();

		$main->publishAssociatedTypes( $event_id, $event );

		$post = get_post( $post_id );
		$this->assertNotEmpty( $post->post_name );

		if ( ! empty( $title ) ) {
			$this->assertStringStartsWith( $expected_slug_prefix, $post->post_name );
		} else {
			$this->assertStringStartsWith( $expected_slug_prefix, $post->post_name );
		}
	}

	/**
	 * @test
	 */
	public function it_should_handle_invalid_post_ids_gracefully() {
		$event_id = $this->factory()->event->create( [ 'post_status' => 'publish' ] );

		// Add invalid post IDs
		add_post_meta( $event_id, '_EventVenueID', 99999 );
		add_post_meta( $event_id, '_EventOrganizerID', 99998 );

		/** @var Tribe__Events__Main $main */
		$main = tribe( 'tec.main' );
		$event = tribe_events()->where( 'ID', $event_id )->first();

		// Should not throw any errors
		$main->publishAssociatedTypes( $event_id, $event );

		$this->assertTrue( true ); // If we get here without errors, test passes
	}

}
