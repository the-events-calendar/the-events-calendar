<?php

class iCal_Test extends \Codeception\TestCase\WPTestCase {

	public function test_exists() {
		$this->assertTrue( class_exists( 'Tribe__Events__iCal' ), 'Check that Tribe__Events__iCal exists' );
	}

	/**
	 * Check to make sure that get_ical_link function works as expected
	 */
	public function test_get_ical_link_home() {
		$ical_link = tribe( 'tec.iCal' )->get_ical_link();
		$ical_link_home = tribe( 'tec.iCal' )->get_ical_link( 'home' );

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

		$ical_link_single_via_object = tribe( 'tec.iCal' )->get_ical_link( 'single' );

		$this->assertNotEmpty( filter_var( $ical_link_single_via_object, FILTER_VALIDATE_URL ), 'Checking that we get back a valid URL from object' );

		$ical_link_single_via_function = tribe_get_single_ical_link();
		$this->assertNotEmpty( filter_var( $ical_link_single_via_function, FILTER_VALIDATE_URL ), 'Checking that we get back a valid URL from function' );

		$this->assertEquals( $ical_link_single_via_object, $ical_link_single_via_function, 'Check that the function and object get the same result' );
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
			$this->factory()->post->create( [ 'post_type' => $post_type, 'meta_input' => $meta ] );
		}

		/** @var \WP_Query $wp_query */
		global $wp_query;

		// run the query, simulates what would happen on the page
		$wp_query = new \WP_Query( [ 'post_type' => $post_type, 'posts_per_page' => $posts_per_page ] );

		$sut = $this->make_instance();
		$sut->set_feed_default_export_count( $default_export_count );
		$content = $sut->generate_ical_feed( null, false );

		$this->assertEventsCount( $expected, $content );
	}

	/**
	 * @return iCal
	 */
	protected function make_instance() {
		return new iCal();
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
			$this->factory()->post->create( [ 'post_type' => $post_type, 'meta_input' => $meta ] );
		}

		/** @var \WP_Query $wp_query */
		global $wp_query;

		// run the query, simulates what would happen on the page
		$wp_query = new \WP_Query( [ 'post_type' => $post_type, 'posts_per_page' => $posts_per_page ] );

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
		$wp_query = new \WP_Query( [ 'post_type' => $post_type, 'posts_per_page' => $posts_per_page ] );

		add_filter(
			'tribe_ical_feed_posts_per_page', function () use ( $filter_count ) {
			return $filter_count;
		} );

		$sut = $this->make_instance();
		$sut->set_feed_default_export_count( $default_export_count );
		$content = $sut->generate_ical_feed( null, false );

		$this->assertEventsCount( $default_export_count, $content );
	}
}