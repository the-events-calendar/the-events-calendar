<?php

namespace Tribe\Events;

use Tribe\Events\Tests\Factories\Event;
use Tribe\Events\Tests\Factories\Organizer;
use Tribe\Events\Tests\Factories\Venue;
use Tribe__Events__Main as TEC;

class Event_TrackerTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * The timezone string option value that will be used to set the WordPress site timezone.
	 *
	 * @var string
	 */
	protected $timezone_string = 'Australia/Darwin';

	/**
	 * A backup of the Tracker tracked post types.
	 *
	 * @var array
	 */
	protected $tracked_post_types = [];

	public function tracked_event_post_fields() {
		return [
			'post_author'           => [ 'post_author' ],
			'post_date'             => [ 'post_date' ],
			'post_date_gmt'         => [ 'post_date_gmt' ],
			'post_content'          => [ 'post_content' ],
			'post_title'            => [ 'post_title' ],
			'post_excerpt'          => [ 'post_excerpt' ],
			'post_status'           => [ 'post_status' ],
			'comment_status'        => [ 'comment_status' ],
			'ping_status'           => [ 'ping_status' ],
			'post_password'         => [ 'post_password' ],
			'post_name'             => [ 'post_name' ],
			'to_ping'               => [ 'to_ping' ],
			'pinged'                => [ 'pinged' ],
			'post_content_filtered' => [ 'post_content_filtered' ],
			'post_parent'           => [ 'post_parent' ],
			'guid'                  => [ 'guid' ],
			'menu_order'            => [ 'menu_order' ],
			'post_type'             => [ 'post_type' ],
			'post_mime_type'        => [ 'post_mime_type' ],
			'comment_count'         => [ 'comment_count' ],
			/**
			 * Short of setting them with a query the `post_modified` and `post_modified_gmt`
			 * fields cannot be set w/ a `wp_insert_post` call: any value would be overridden
			 * and replaced w/ the current time; they are here just to make this explicit.
			 */
			// 'post_modified'         => [ 'post_modified' ],
			// 'post_modified_gmt'     => [ 'post_modified_gmt' ],
		];
	}

	/**
	 * It should update an event modified date when updating a post field
	 *
	 * @test
	 *
	 * @dataProvider tracked_event_post_fields
	 *
	 * @param string $field
	 */
	public function should_update_an_event_modified_date_when_updating_a_post_field( string $field ) {
		$event = $this->create_event_modified_yesterday();
		$this->enable_tracking();

		$value = $this->new_value_for_post_field( $event->ID, $field );
		wp_update_post( [ 'ID' => $event->ID, $field => $value ] );

		$this->assert_post_modified_field_changed( $event );
	}

	/**
	 * Creates, sets up and returns an event post instance last modified yesterday.
	 *
	 * @param array $overrides An array of values to override the defaults.
	 *
	 * @return \WP_Post The event post object.
	 */
	protected function create_event_modified_yesterday( array $overrides = [] ): \WP_Post {
		$previous_user = get_current_user_id();

		/**
		 * To add/remove tax terms we have to be a privileged user, so here we create one ad hoc.
		 */
		$user = $this->factory()->user->create( [ 'role' => 'editor' ] );
		wp_set_current_user( $user );

		$utc_yesterday = ( new \DateTime( 'yesterday', new \DateTimeZone( 'UTC' ) ) )->format( 'Y-m-d H:i:s' );
		$wp_yesterday  = $this->convert_to_site_time( $utc_yesterday );
		/** @var \WP_Post $event */
		$event = $this->factory()->event->create_and_get( array_merge( $overrides, [
			'post_date'         => $wp_yesterday,
			'post_date_gmt'     => $utc_yesterday,
			'post_modified'     => $wp_yesterday,
			'post_modified_gmt' => $utc_yesterday,
		] ) );
		$this->assertEquals( $utc_yesterday, $event->post_modified_gmt );
		$this->assertEquals( $wp_yesterday, $event->post_modified );

		wp_set_current_user( $previous_user );

		return $event;
	}

	/**
	 * Converts the input UTC date and time in the localized, according to the
	 * timezone string, date.
	 *
	 * @param string $utc_date The input UTC date in the `Y-m-d H:i:s` format.
	 *
	 * @return string The localized version of the date, according to the `timezone_string`.
	 */
	protected function convert_to_site_time( $utc_date ): string {
		return ( new \DateTime( $utc_date, new \DateTimeZone( 'UTC' ) ) )
			->setTimezone( new \DateTimeZone( $this->timezone_string ) )
			->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Re-enables the global tracker tracking function restoring the tracked post types.
	 */
	protected function enable_tracking() {
		remove_filter( 'tribe_tracker_post_types', '__return_empty_array', 99 );
	}

	/**
	 * Returns a new value for a post field.
	 *
	 * @param int    $event_id The event post ID.
	 * @param string $field    The post field, a column of the `posts` table, to get a new value for.
	 *
	 * @return mixed The field new value.
	 */
	protected function new_value_for_post_field( int $event_id, string $field ) {
		$two_days_ago = date( 'Y-m-d H:i:s', strtotime( '-2 days' ) );
		$map          = [
			'post_author'           => function () {
				return $this->factory()->user->create( [ 'role' => 'editor' ] );
			},
			'post_date'             => $two_days_ago,
			'post_date_gmt'         => $two_days_ago,
			'post_content'          => 'The force awakens',
			'post_title'            => 'A new hope',
			'post_excerpt'          => 'Never tell me the odds!',
			'post_status'           => 'draft',
			'comment_status'        => 'closed',
			'ping_status'           => 'closed',
			'post_password'         => 'h0b1w4nk3n0b1',
			'post_name'             => 'no-i-am-your-father',
			'to_ping'               => 'nope',
			'pinged'                => 'yep',
			/**
			 * Short of setting them with a query the `post_modified` and `post_modified_gmt`
			 * fields cannot be set w/ a `wp_insert_post` call: any value would be overridden
			 * and replaced w/ the current time; they are here just to make this explicit.
			 */
			// 'post_modified'         => $two_days_ago,
			// 'post_modified_gmt'     => $two_days_ago,
			'post_content_filtered' => "<p>Aren't you a little short for a Stormtrooper?</p>",
			'post_parent'           => function () {
				return $this->factory()->post->create();
			},
			'guid'                  => '/index.php?event=endor',
			'menu_order'            => 23,
			'post_type'             => 'post',
			'post_mime_type'        => 'ea/gcal',
			'comment_count'         => function () use ( $event_id ) {
				return count( $this->factory()->comment->create_many( 3, [ 'comment_post_ID' => $event_id ] ) );
			},
		];

		return \is_callable( $map[ $field ] ) ? \call_user_func( $map[ $field ] ) : $map[ $field ];
	}

	/**
	 * Asserts that the event `post_modified` and `post_modified_gmt` fields
	 * have changed as expected and by the same value.
	 *
	 * @param \WP_Post $event The event post object before any update happens.
	 */
	protected function assert_post_modified_field_changed( \WP_Post $event ) {
		clean_post_cache( $event->ID );
		$fresh_event        = get_post( $event->ID );
		$modified_diff      = strtotime( $fresh_event->post_modified ) - strtotime( $event->post_modified );
		$gmt_modified_diff  = strtotime( $fresh_event->post_modified_gmt ) - strtotime( $event->post_modified_gmt );
		$modified_timestamp = ( new \DateTime( $fresh_event->post_modified_gmt, new \DateTimeZone( 'UTC' ) ) )->getTimestamp();
		/**
		 * Let's allow for 10 seconds delta to take processing time into account.
		 * Since the event should have been modified a day ago then this should not
		 * cause any trouble.
		 */
		$this->assertEquals( time(), $modified_timestamp, "The `post_modified_gmt` date should be about now, is {$fresh_event->post_modified_gmt}.", 10 );
		$this->assertGreaterThan( 0, $modified_diff, "The event `post_modified` date did not change as expected, was {$event->post_modified}, is {$fresh_event->post_modified}." );
		$this->assertGreaterThan( 0, $gmt_modified_diff, "The event `post_modified_gmt` date did not change as expected, was {$event->post_modified_gmt}, is {$fresh_event->post_modified_gmt}." );
		$this->assertEquals( $modified_diff, $gmt_modified_diff, "The event `post_modified` and `post_modified_gmt` fields did not change by the same amount: `post_modified` changed by {$modified_diff} seconds, `post_modified_gmt` changed by {$gmt_modified_diff} seconds." );
	}

	/**
	 * It should update an event modified date when adding a custom field
	 *
	 * @test
	 */
	public function should_update_an_event_modified_date_when_adding_a_custom_field() {
		$event = $this->create_event_modified_yesterday();
		$this->enable_tracking();

		add_post_meta( $event->ID, '_ship', 'Millenium Falcon' );

		$this->assert_post_modified_field_changed( $event );
	}

	/**
	 * It should update an event modified date when updating a custom field
	 *
	 * @test
	 */
	public function should_update_an_event_modified_date_when_updating_a_custom_field() {
		$event = $this->create_event_modified_yesterday( [ 'meta_input' => [ '_ship' => 'None' ] ] );
		$this->assertEquals( 'None', get_post_meta( $event->ID, '_ship', true ) );
		$this->enable_tracking();

		update_post_meta( $event->ID, '_ship', 'Millenium Falcon' );

		$this->assert_post_modified_field_changed( $event );
	}

	/**
	 * It should update an event modified date when deleting a custom field
	 *
	 * @test
	 */
	public function should_update_an_event_modified_date_when_deleting_a_custom_field() {
		$event = $this->create_event_modified_yesterday( [ 'meta_input' => [ '_ship' => 'Tie Fighter' ] ] );
		$this->assertEquals( 'Tie Fighter', get_post_meta( $event->ID, '_ship', true ) );
		$this->enable_tracking();

		delete_post_meta( $event->ID, '_ship' );

		$this->assert_post_modified_field_changed( $event );
	}

	/**
	 * It should update an event modified date when adding a tax term to it
	 *
	 * @test
	 */
	public function should_update_an_event_modified_date_when_adding_a_tax_term_to_it() {
		$this->factory()->term->create( [ 'slug' => 'rebel-meeting' ] );
		$event = $this->create_event_modified_yesterday();
		$this->enable_tracking();

		wp_add_object_terms( $event->ID, 'rebel-meeting', TEC::TAXONOMY );

		$this->assert_post_modified_field_changed( $event );
	}

	/**
	 * It should update an event modified date when removing a tax term from it
	 *
	 * @test
	 */
	public function should_update_an_event_modified_date_when_removing_a_tax_term_from_it() {
		$this->factory()->term->create( [ 'slug' => 'rebel-meeting' ] );
		$event = $this->create_event_modified_yesterday( [ 'tax_input' => [ TEC::TAXONOMY => [ 'rebel-meeting' ] ] ] );
		$this->enable_tracking();

		wp_remove_object_terms( $event->ID, 'rebel-meeting', TEC::TAXONOMY );

		$this->assert_post_modified_field_changed( $event );
	}

	/**
	 * It should update the event modified date when the venue is updated
	 *
	 * @test
	 */
	public function should_update_the_event_modified_date_when_the_venue_is_updated() {
		$venue_id = $this->factory()->venue->create();
		$event    = $this->create_event_modified_yesterday( [
			'meta_input' => [
				'_EventVenueID' => $venue_id,
			]
		] );
		$this->enable_tracking();

		wp_update_post( [ 'ID' => $venue_id, 'post_title' => 'Rebel Headquarter' ] );

		$this->assert_post_modified_field_changed( $event );
	}

	/**
	 * It should update the event modified date when an organizer is updated
	 *
	 * @test
	 */
	public function should_update_the_event_modified_date_when_an_organizer_is_updated() {
		$organizer_ids = $this->factory()->organizer->create_many( 2 );
		$event         = $this->create_event_modified_yesterday( [
			'meta_input' => [
				'_EventOrganizerID_Order' => [ $organizer_ids ],
			]
		] );
		// to avoid having them stuffed in an array we add them one by one
		add_post_meta( $event->ID, '_EventOrganizerID', $organizer_ids[0] );
		add_post_meta( $event->ID, '_EventOrganizerID', $organizer_ids[1] );
		$this->enable_tracking();

		wp_update_post( [ 'ID' => $organizer_ids[0], 'post_title' => 'Jyn Erso' ] );

		$this->assert_post_modified_field_changed( $event );
	}

	/**
	 * It should update the event modified field when the venue is deleted
	 *
	 * @test
	 */
	public function should_update_the_event_modified_field_when_the_venue_is_deleted() {
		$venue_id = $this->factory()->venue->create();
		$event    = $this->create_event_modified_yesterday( [
			'meta_input' => [
				'_EventVenueID' => $venue_id,
			]
		] );
		$this->enable_tracking();

		wp_delete_post( $venue_id );

		$this->assert_post_modified_field_changed( $event );
	}

	/**
	 * It should update the event modified field when an organizer is deleted
	 *
	 * @test
	 */
	public function should_update_the_event_modified_field_when_an_organizer_is_deleted() {
		$organizer_ids = $this->factory()->organizer->create_many( 2 );
		$event         = $this->create_event_modified_yesterday( [
			'meta_input' => [
				'_EventOrganizerID_Order' => [ $organizer_ids ],
			]
		] );
		// to avoid having them stuffed in an array we add them one by one
		add_post_meta( $event->ID, '_EventOrganizerID', $organizer_ids[0] );
		add_post_meta( $event->ID, '_EventOrganizerID', $organizer_ids[1] );
		$this->enable_tracking();

		wp_delete_post( $organizer_ids[0] );

		$this->assert_post_modified_field_changed( $event );
	}

	public function tearDown() {
		/**
		 * Let's re-enable tracking when we are done.
		 */
		$this->enable_tracking();
		parent::tearDown();
	}

	public function setUp() {
		parent::setUp();
		$this->factory()->event     = new Event();
		$this->factory()->venue     = new Venue();
		$this->factory()->organizer = new Organizer();
		/**
		 * We'll use the "global" tracker in the tests but, to avoid "noise",
		 * let's make it so that nothing is being tracked before the tests start.
		 */
		$this->disable_tracking();
		update_option( 'timezone_string', $this->timezone_string );
	}

	/**
	 * Disables the global tracker tracking function setting tracked post types
	 * to an empty array.
	 */
	protected function disable_tracking() {
		add_filter( 'tribe_tracker_post_types', '__return_empty_array', 99 );
	}
}