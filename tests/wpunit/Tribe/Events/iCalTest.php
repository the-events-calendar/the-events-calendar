<?php

namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;
use Tribe__Events__API;
use Tribe__Events__iCal as iCal;
use WP_Post;

class iCalTest extends WPTestCase {
	protected $post_example_settings;

	public function setUp() {
		// before
		parent::setUp();

		$this->factory()->event = new Event();

		// your set up methods here
		$this->post_example_settings = array(
			'post_author'           => 3,
			'post_title'            => 'Test event',
			'post_content'          => 'This is event content!',
			'post_status'           => 'publish',
			'EventAllDay'           => false,
			'EventHideFromUpcoming' => true,
			'EventOrganizerID'      => 5,
			'EventVenueID'          => 8,
			'EventShowMapLink'      => true,
			'EventShowMap'          => true,
			'EventStartDate'        => '2012-01-01',
			'EventEndDate'          => '2012-01-03',
			'EventStartHour'        => '01',
			'EventStartMinute'      => '15',
			'EventStartMeridian'    => 'am',
			'EventEndHour'          => '03',
			'EventEndMinute'        => '25',
			'EventEndMeridian'      => 'pm',
		);
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

		$this->assertInstanceOf( iCal::class, $sut );
	}

	/**
	 * @return iCal
	 */
	protected function make_instance() {
		return new iCal();
	}

	public function count_scenarios() {
		return [
			// events total count, posts per page, default export count, expected
			[ 1, 10, 15, 1 ],
			[ 5, 10, 15, 5 ],
			[ 12, 10, 15, 12 ],
			[ 15, 10, 15, 15 ],
			[ 17, 10, 15, 15 ],
			[ 17, 20, 15, 15 ],
			[ 13, 20, 15, 13 ],
			[ 13, 20, 11, 11 ],
			[ 13, 1, 11, 11 ],
			[ 13, - 1, 7, 7 ],
			[ 13, 15, 1, 1 ],
		];
	}

	/**
	 * @test
	 * it should export the expected number of events in respect to default count, events count and posts_per_page
	 * @dataProvider count_scenarios
	 */
	public function it_should_export_the_expected_number_of_events_in_respect_to_default_value_events_count_and_posts_per_page(
		$events_total_count,
		$posts_per_page,
		$default_export_count,
		$expected
	) {
		$post_type = \Tribe__Events__Main::POSTTYPE;

		for ( $i = 0; $i < $events_total_count; $i ++ ) {
			$meta = [ '_EventStartDate' => date( \Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( '+' . ( $i + 1 ) . ' days' ) ) ];
			$this->factory()->event->create( [ 'post_type' => $post_type, 'meta_input' => $meta ] );
		}

		/** @var \WP_Query $wp_query */
		global $wp_query;

		// run the query, simulates what would happen on the page
		$wp_query = tribe_get_events( [ 'posts_per_page' => $posts_per_page ], true );

		$sut = $this->make_instance();
		$sut->set_feed_default_export_count( $default_export_count );
		$content = $sut->generate_ical_feed( null, false );

		$this->assertEventsCount( $expected, $content );
	}

	/**
	 * @param $count
	 * @param $content
	 */
	protected function assertEventsCount( $count, $content ) {
		preg_match_all( '/BEGIN:VEVENT/', $content, $matches );
		$this->assertCount( $count, $matches[0] );
	}

	public function count_scenarios_with_filter() {
		return [
			// events total count, posts per page, default export count, filter count, expected
			[ 1, 10, 15, 5, 1 ],
			[ 5, 10, 15, 5, 5 ],
			[ 12, 10, 15, 11, 11 ],
			[ 15, 10, 15, 15, 15 ],
			[ 17, 10, 15, 10, 10 ],
		];
	}

	/**
	 * @test
	 * it should export the expected number of events in respect to default count, events count, posts_per_page and filter value
	 * @dataProvider count_scenarios_with_filter
	 */
	public function it_should_export_the_expected_number_of_events_in_respect_to_context_and_filter(
		$events_total_count,
		$posts_per_page,
		$default_export_count,
		$filter_count,
		$expected
	) {
		$post_type = \Tribe__Events__Main::POSTTYPE;

		for ( $i = 0; $i < $events_total_count; $i ++ ) {
			$meta = [ '_EventStartDate' => date( \Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( '+' . ( $i + 1 ) . ' days' ) ) ];
			$this->factory()->event->create( [ 'post_type' => $post_type, 'meta_input' => $meta ] );
		}

		/** @var \WP_Query $wp_query */
		global $wp_query;

		// run the query, simulates what would happen on the page
		$wp_query = tribe_get_events( [ 'posts_per_page' => $posts_per_page ], true );

		add_filter(
			'tribe_ical_feed_posts_per_page', function () use ( $filter_count ) {
			return $filter_count;
		} );

		$sut = $this->make_instance();
		$sut->set_feed_default_export_count( $default_export_count );
		$content = $sut->generate_ical_feed( null, false );

		$this->assertEventsCount( $expected, $content );
	}

	public function bad_filter_counts() {
		return [
			[ 0 ],
			[ 'foo' ],
			[ new \stdClass() ],
			[ array( 'foo' => 'bar' ) ],
		];
	}

	/**
	 * @test
	 * it should set the count size back to default if filter value is not an int
	 * @dataProvider bad_filter_counts
	 */
	public function it_should_set_the_count_size_back_to_default_if_filter_value_is_not_an_int( $filter_count ) {
		$events_total_count = 10;
		$posts_per_page = 20;
		$default_export_count = 7;
		$post_type = \Tribe__Events__Main::POSTTYPE;

		for ( $i = 0; $i < $events_total_count; $i ++ ) {
			$meta = [ '_EventStartDate' => date( \Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( '+' . ( $i + 1 ) . ' days' ) ) ];
			$this->factory()->post->create( [ 'post_type' => $post_type, 'meta_input' => $meta ] );
		}

		/** @var \WP_Query $wp_query */
		global $wp_query;

		// run the query, simulates what would happen on the page
		$wp_query = tribe_get_events( [ 'posts_per_page' => $posts_per_page ], true );

		add_filter(
			'tribe_ical_feed_posts_per_page', function () use ( $filter_count ) {
			return $filter_count;
		} );

		$sut = $this->make_instance();
		$sut->set_feed_default_export_count( $default_export_count );
		$content = $sut->generate_ical_feed( null, false );

		$this->assertEventsCount( $default_export_count, $content );
	}

	/**
	 * Check to make sure that get_ical_link function works as expected
	 */
	public function test_get_ical_link_home() {
		$ical_link = $this->make_instance()->get_ical_link();
		$ical_link_home = $this->make_instance()->get_ical_link( 'home' );

		$this->assertEquals( $ical_link, $ical_link_home, 'Check that events home is the default' );
	}

	/**
	 * Check to make sure that get_ical_link function works as expected
	 *
	 * @uses $post_example_settings
	 */
	public function test_get_ical_link_single() {
		global $post;
		$post = get_post( Tribe__Events__API::createEvent( $this->post_example_settings ) );
		$this->assertTrue( $post instanceof WP_Post, 'Check that post creates properly' );

		$ical_link_single_via_object = $this->make_instance()->get_ical_link( 'single' );

		$this->assertNotEmpty( filter_var( $ical_link_single_via_object, FILTER_VALIDATE_URL ), 'Checking that we get back a valid URL from object' );

		$ical_link_single_via_function = tribe_get_single_ical_link();
		$this->assertNotEmpty( filter_var( $ical_link_single_via_function, FILTER_VALIDATE_URL ), 'Checking that we get back a valid URL from function' );

		$this->assertEquals( $ical_link_single_via_object, $ical_link_single_via_function, 'Check that the function and object get the same result' );
	}

	/**
	 * Check to make sure that month_view_ical_link function works as expected with no date.
	 */
	public function test_get_month_view_ical_link_no_date() {
		$ical_link_via_object = $this->make_instance()->month_view_ical_link();

		$this->assertNotEmpty( filter_var( $ical_link_via_object, FILTER_VALIDATE_URL ), 'Checking that we get back a valid URL from object.' );

		$manual_link = untrailingslashit( home_url() ) . '/events/' . \Tribe__Date_Utils::build_date_object()->format( \Tribe__Date_Utils::DBYEARMONTHTIMEFORMAT ) . '/?ical=1';

		$this->assertEquals( $ical_link_via_object, $manual_link, 'Check that the object gets the same results as a manual build.' );
	}

	/**
	 * Check to make sure that month_view_ical_link function works as expected with a date supplied.
	 */
	public function test_get_month_view_ical_link_with_date() {
		$ical_link_via_object = $this->make_instance()->month_view_ical_link( '2020-10' );

		$this->assertNotEmpty( filter_var( $ical_link_via_object, FILTER_VALIDATE_URL ), 'Checking that we get back a valid URL from object.' );

		$manual_link = untrailingslashit( home_url() ) . '/events/2020-10/?ical=1';

		$this->assertEquals( $ical_link_via_object, $manual_link, 'Check that the object gets the same results as a manual build.' );
	}

	/**
	 * It should generate the iCal content
	 *
	 * @dataProvider ical_content_provider
	 *
	 * @test
	 */
	public function should_generate_the_ical_content( $args, $expected = '' ) {
		$event = $this->factory()->event->create( $args );

		$sut = $this->make_instance();

		$this->assertContains( $expected, $sut->generate_ical_feed( get_post( $event ), false ) );
	}

	public function ical_content_provider() {
		return [
			'UTC time zone' => [
				[
				'when' => '2019-01-01',
				'duration' => DAY_IN_SECONDS,
				'meta_input' => [ '_EventTimezone' => 'UTC' ]
				],
				"BEGIN:VTIMEZONE\r\nTZID:UTC\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:+0000\r\nTZOFFSETTO:+0000\r\nTZNAME:UTC\r\nDTSTART:20180101T000000\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
			],
			'America/New_York time zone' => [
				[
					'when' => '2019-03-08',
					'duration' => DAY_IN_SECONDS * 5,
					'meta_input' => [ '_EventTimezone' => 'America/New_York' ]
				],
				"BEGIN:VTIMEZONE\r\nTZID:America/New_York\r\nBEGIN:DAYLIGHT\r\nTZOFFSETFROM:-0500\r\nTZOFFSETTO:-0400\r\nTZNAME:EDT\r\nDTSTART:20180311T070000\r\nEND:DAYLIGHT\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:-0400\r\nTZOFFSETTO:-0500\r\nTZNAME:EST\r\nDTSTART:20181104T060000\r\nEND:STANDARD\r\nBEGIN:DAYLIGHT\r\nTZOFFSETFROM:-0500\r\nTZOFFSETTO:-0400\r\nTZNAME:EDT\r\nDTSTART:20190310T070000\r\nEND:DAYLIGHT\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:-0400\r\nTZOFFSETTO:-0500\r\nTZNAME:EST\r\nDTSTART:20191103T060000\r\nEND:STANDARD\r\nBEGIN:DAYLIGHT\r\nTZOFFSETFROM:-0500\r\nTZOFFSETTO:-0400\r\nTZNAME:EDT\r\nDTSTART:20200308T070000\r\nEND:DAYLIGHT\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:-0400\r\nTZOFFSETTO:-0500\r\nTZNAME:EST\r\nDTSTART:20201101T060000\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
			],
			'Europe/Berlin time zone' => [
				[
					'when' => '2019-11-02',
					'duration' => DAY_IN_SECONDS * 5,
					'meta_input' => [ '_EventTimezone' => 'Europe/Berlin' ]
				],
				"BEGIN:VTIMEZONE\r\nTZID:Europe/Berlin\r\nBEGIN:DAYLIGHT\r\nTZOFFSETFROM:+0100\r\nTZOFFSETTO:+0200\r\nTZNAME:CEST\r\nDTSTART:20180325T010000\r\nEND:DAYLIGHT\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:+0200\r\nTZOFFSETTO:+0100\r\nTZNAME:CET\r\nDTSTART:20181028T010000\r\nEND:STANDARD\r\nBEGIN:DAYLIGHT\r\nTZOFFSETFROM:+0100\r\nTZOFFSETTO:+0200\r\nTZNAME:CEST\r\nDTSTART:20190331T010000\r\nEND:DAYLIGHT\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:+0200\r\nTZOFFSETTO:+0100\r\nTZNAME:CET\r\nDTSTART:20191027T010000\r\nEND:STANDARD\r\nBEGIN:DAYLIGHT\r\nTZOFFSETFROM:+0100\r\nTZOFFSETTO:+0200\r\nTZNAME:CEST\r\nDTSTART:20200329T010000\r\nEND:DAYLIGHT\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:+0200\r\nTZOFFSETTO:+0100\r\nTZNAME:CET\r\nDTSTART:20201025T010000\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
			],
			'Asia/Dubai time zone (does not observe DST)' => [
				[
					'when' => '2019-06-15',
					'duration' => DAY_IN_SECONDS * 2,
					'meta_input' => [ '_EventTimezone' => 'Asia/Dubai' ]
				],
				"BEGIN:VTIMEZONE\r\nTZID:Asia/Dubai\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:+0400\r\nTZOFFSETTO:+0400\r\nTZNAME:+04\r\nDTSTART:20180101T000000\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
			],
		];
	}

	/**
	 * It should parse the event details
	 *
	 * @test
	 */
	public function should_parse_the_event_details() {
		$args = [
			'post_title' => 'Long words with "quotes" on it',
			'post_content' => "Sample
Text
WITH
multiple lines",
		];
		$event = $this->factory()->event->create( $args );

		$sut = $this->make_instance();
		$event = get_post( $event );
		$ical = $sut->generate_ical_feed( $event, false );

		$this->assertContains( "SUMMARY:" . wp_strip_all_tags( $args['post_title'] ), $ical );

		$content = apply_filters( 'the_content', tribe( 'editor.utils' )->exclude_tribe_blocks( $event->post_content ) );

		$content =  str_replace(
			[  ',', "\n", "\r"  ],
			[  '\,', '\n', '' ],
			wp_strip_all_tags( str_replace( '</p>', '</p> ', $content ) )
		);

		$this->assertContains( "DESCRIPTION:" . $content, $ical );
	}

	public function event_timezones() {
		return [
			[ 'UTC' ],
			[ 'America/New_York' ],
			[ 'Europe/Paris' ],
		];
	}

	/**
	 * @test
	 * @dataProvider event_timezones
	 */
	public function should_parse_the_event_dates( $timezone_name ) {

		$format = 'Ymd\THis';
		$event  = $this->factory()->event->starting_on( '2021-01-10 09:00:00' )
										 ->with_timezone( $timezone_name )
		                                 ->lasting( 5 * HOUR_IN_SECONDS )
		                                 ->create();

		$sut   = $this->make_instance();
		$event = get_post( $event );
		$ical  = $sut->generate_ical_feed( $event, false );

		$start           = tribe_get_start_date( $event->ID, false, 'U' );
		$end             = tribe_get_end_date( $event->ID, false, 'U' );
		$start_timestamp = \Tribe__Date_Utils::build_date_object( $start )->format( $format );
		$end_timestamp   = \Tribe__Date_Utils::build_date_object( $end )->format( $format );

		$this->assertContains( "DTSTART;TZID={$timezone_name}:{$start_timestamp}", $ical );
		$this->assertContains( "DTEND;TZID={$timezone_name}:{$end_timestamp}", $ical );
	}

	public function protected_content_data_provider() {
		return [
			'should see valid event'                        =>
				[
					[
						'when'     => '2019-01-01',
						'duration' => DAY_IN_SECONDS,
					],
					true, // should see title
					true, // should see post content
					false, // should be empty
				],
			'should not see a draft'                        =>
				[
					[
						'when'        => '2019-01-01',
						'duration'    => DAY_IN_SECONDS,
						'post_status' => 'draft'
					],
					false, // should see title
					false, // should see post content
					true, // should be empty
				],
			'should not see private'                        =>
				[
					[
						'when'        => '2019-01-01',
						'duration'    => DAY_IN_SECONDS,
						'post_status' => 'private'
					],
					false, // should see title
					false, // should see post content
					true, // should be empty
				],
			'should not see password protected description' =>
				[
					[
						'when'          => '2019-01-01',
						'duration'      => DAY_IN_SECONDS,
						'post_status'   => 'publish',
						'post_password' => wp_hash_password( "bob" )
					],
					true, // should see title
					false, // should see post content
					false, // should be empty
				],
		];
	}

	/**
	 * Should not print events that are hidden for the current user.
	 *
	 * @dataProvider protected_content_data_provider
	 * @test
	 */
	public function should_protect_against_event_access_levels( $args, $should_have_title, $should_have_content, $should_be_empty ) {
		// Ensure no funky cache/memoize stuff.
		$title   = 'Franks Free Fridders ' . uniqid();
		$content = 'The best fridders in frankfurt ' . uniqid();
		// Consistently set our title/content to test against later.
		$args['post_title']   = $title;
		$args['post_content'] = $content;
		$event                = $this->factory()->event->create( $args );
		$sut                  = $this->make_instance();

		// Define the file path and URL of the image you want to create as an attachment
		$file_path       = '/faux/castle.jpg';
		$attachment_data = array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => basename( $file_path ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);
		// Insert the attachment
		$attachment_id = wp_insert_attachment( $attachment_data, $file_path );
		set_post_thumbnail( $event, $attachment_id );

		// Now see that our event has been validated properly.
		$ical = $sut->generate_ical_feed( $event, false );

		if ( $should_have_title ) {
			$this->assertContains( "SUMMARY:$title", $ical );
		} else {
			$this->assertNotContains( "SUMMARY:$title", $ical );
		}
		if ( $should_have_content ) {
			$this->assertContains( "DESCRIPTION:$content", $ical );
			$this->assertContains( "ATTACH;", $ical );
		} else {
			$this->assertNotContains( "DESCRIPTION:$content", $ical );
			$this->assertNotContains( "ATTACH;", $ical );
		}
		if ( $should_be_empty ) {
			$this->assertEquals( '', $ical );
		} else {
			$this->assertNotEquals( '', $ical );
		}
	}

	/**
	 * Provides `ical` query-string values mimicking scanners and abuse attempts.
	 *
	 * The real `ical` parameter is only checked for presence (`isset`).
	 *
	 * @return array<string, array{0: string, 1: array<int, string>}>
	 */
	public function malicious_ical_value_provider() {
		return [
			'plain_boolean_one'            => [
				'1',
				[],
			],
			'mysql_time_based_sleep'       => [
				"1'));SLEEP(3);-- tec_svul_ical_mysql",
				[ 'SLEEP(3)', 'tec_svul_ical_mysql' ],
			],
			'mssql_time_based_waitfor'     => [
				"1');WAITFOR DELAY '00:00:03';-- tec_svul_ical_mssql",
				[ "WAITFOR DELAY '00:00:03'", 'tec_svul_ical_mssql' ],
			],
			'scanner_style_waitfor'        => [
				"1');WAITFOR DELAY '00:00:3';--",
				[ "WAITFOR DELAY '00:00:3'" ],
			],
			'union_select'                 => [
				"1' UNION SELECT NULL,tec_svul_ical_union,NULL--",
				[ 'tec_svul_ical_union', 'UNION SELECT' ],
			],
			'script_injection'             => [
				'<script>tec_svul_ical_xss</script>',
				[ '<script>', 'tec_svul_ical_xss', '</script>' ],
			],
			'boolean_true_string'          => [
				'true',
				[],
			],
			'long_encoded_style_payload'   => [
				"1));SLEEP(2);-- tec_svul_ical_long " . str_repeat( 'A', 64 ),
				[ 'SLEEP(2)', 'tec_svul_ical_long' ],
			],
		];
	}

	/**
	 * Malicious `ical` values must not reach the feed body or database queries.
	 *
	 * Covers false positives from time-based SQLi scanners: `ical` is a boolean
	 * gate in `Tribe__Events__iCal::do_ical_template()`; export work is done in
	 * `generate_ical_feed()`, which does not interpolate `$_GET['ical']` into SQL.
	 *
	 * We exercise `generate_ical_feed( null, false )` while `$_GET`/`$_REQUEST` are
	 * poisoned, matching what runs after the template gate (without `die()` / `tribe_exit()`).
	 *
	 * @test
	 * @dataProvider malicious_ical_value_provider
	 *
	 * @param string               $ical_value          Value assigned to `$_GET['ical']`.
	 * @param array<int, string>   $forbidden_substrings Substrings that must not appear in output or SQL.
	 */
	public function should_not_reflect_malicious_ical_parameter_in_feed_or_queries( $ical_value, array $forbidden_substrings ) {
		global $wpdb, $wp_query;

		$this->ensure_savequeries_enabled();

		$post_type = \Tribe__Events__Main::POSTTYPE;
		$this->factory()->event->create(
			[
				'post_type'  => $post_type,
				'meta_input' => [
					'_EventStartDate' => gmdate( \Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( '+1 day' ) ),
				],
			]
		);

		$wp_query = tribe_get_events( [ 'posts_per_page' => 10 ], true );

		$original_get      = isset( $_GET['ical'] ) ? $_GET['ical'] : null;
		$original_request  = isset( $_REQUEST['ical'] ) ? $_REQUEST['ical'] : null;

		$query_offset = ( is_array( $wpdb->queries ) ) ? count( $wpdb->queries ) : 0;

		$_GET['ical']     = $ical_value;
		$_REQUEST['ical'] = $ical_value;

		try {
			$sut     = $this->make_instance();
			$content = $sut->generate_ical_feed( null, false );
		} finally {
			if ( null !== $original_get ) {
				$_GET['ical'] = $original_get;
			} else {
				unset( $_GET['ical'] );
			}
			if ( null !== $original_request ) {
				$_REQUEST['ical'] = $original_request;
			} else {
				unset( $_REQUEST['ical'] );
			}
		}

		$this->assertNotEmpty( $content );
		$this->assertContains( 'BEGIN:VCALENDAR', $content );

		foreach ( $forbidden_substrings as $needle ) {
			$this->assertStringNotContainsString(
				$needle,
				$content,
				'iCal output must not contain malicious `ical` payload fragments.'
			);
		}

		$this->assertSubstringsAbsentFromQueriesSince( $forbidden_substrings, $query_offset );
	}

	/**
	 * Same guarantees when the scanner sends `ical` on POST (body) rather than GET.
	 *
	 * `do_ical_template()` only checks `$_GET['ical']` and rewrite query vars, so POST-only
	 * requests do not open the iCal template; export code still must not leak POST payloads
	 * into SQL when generating feeds elsewhere.
	 *
	 * @test
	 */
	public function should_not_reflect_malicious_ical_from_post_superglobal_in_feed_or_queries() {
		global $wpdb, $wp_query;

		$this->ensure_savequeries_enabled();

		$post_type = \Tribe__Events__Main::POSTTYPE;
		$this->factory()->event->create(
			[
				'post_type'  => $post_type,
				'meta_input' => [
					'_EventStartDate' => gmdate( \Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( '+2 days' ) ),
				],
			]
		);

		$wp_query = tribe_get_events( [ 'posts_per_page' => 10 ], true );

		$malicious = "1'));SLEEP(3);-- tec_svul_ical_post_body";
		$needles   = [ 'SLEEP(3)', 'tec_svul_ical_post_body' ];

		$original_get     = isset( $_GET['ical'] ) ? $_GET['ical'] : null;
		$original_post    = isset( $_POST['ical'] ) ? $_POST['ical'] : null;
		$original_request = isset( $_REQUEST['ical'] ) ? $_REQUEST['ical'] : null;

		unset( $_GET['ical'] );

		$query_offset = ( is_array( $wpdb->queries ) ) ? count( $wpdb->queries ) : 0;

		$_POST['ical']    = $malicious;
		$_REQUEST['ical'] = $malicious;

		try {
			$sut     = $this->make_instance();
			$content = $sut->generate_ical_feed( null, false );
		} finally {
			if ( null !== $original_get ) {
				$_GET['ical'] = $original_get;
			} else {
				unset( $_GET['ical'] );
			}
			if ( null !== $original_post ) {
				$_POST['ical'] = $original_post;
			} else {
				unset( $_POST['ical'] );
			}
			if ( null !== $original_request ) {
				$_REQUEST['ical'] = $original_request;
			} else {
				unset( $_REQUEST['ical'] );
			}
		}

		$this->assertNotEmpty( $content );
		$this->assertContains( 'BEGIN:VCALENDAR', $content );

		foreach ( $needles as $needle ) {
			$this->assertStringNotContainsString( $needle, $content );
		}

		$this->assertSubstringsAbsentFromQueriesSince( $needles, $query_offset );
	}

	/**
	 * Ensures SAVEQUERIES is on so `$wpdb->queries` is populated for assertions.
	 *
	 * @return void
	 */
	private function ensure_savequeries_enabled() {
		if ( ! defined( 'SAVEQUERIES' ) ) {
			define( 'SAVEQUERIES', true );
		}

		$this->assertTrue(
			(bool) SAVEQUERIES,
			'SAVEQUERIES must be true to assert on $wpdb->queries.'
		);
	}

	/**
	 * Asserts none of the needles appear in SQL recorded after `$offset`.
	 *
	 * @param array<int, string> $needles Distinct substrings that must not appear in executed SQL.
	 * @param int                $offset  Number of queries to skip (only check queries run during the SUT).
	 *
	 * @return void
	 */
	private function assertSubstringsAbsentFromQueriesSince( array $needles, $offset ) {
		if ( [] === $needles ) {
			return;
		}

		global $wpdb;

		$this->assertIsArray(
			$wpdb->queries,
			'Expected $wpdb->queries to be an array; enable SAVEQUERIES in the test environment.'
		);

		$slice = array_slice( $wpdb->queries, $offset );

		foreach ( $slice as $row ) {
			$sql = is_array( $row ) ? $row[0] : $row;
			foreach ( $needles as $needle ) {
				$this->assertStringNotContainsString(
					$needle,
					$sql,
					'Executed SQL must not contain malicious `ical` payload fragments.'
				);
			}
		}
	}
}
