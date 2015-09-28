<?php
namespace TEC\Tests\functions\template_tags;

class icalTest extends \Tribe__Events__WP_UnitTestCase {

	protected $backupGlobals = false;
	protected $post_type;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$tec = \Tribe__Events__Main::instance();
		$tec->init();
		$tec->init_ical();
		$tec->init_day_view();
		$this->post_type = $tec::POSTTYPE;
		\Tribe__Events__Pro__Main::instance();
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should return empty string if not passing an event id
	 */
	public function it_should_return_empty_string_if_not_passing_an_event_id() {
		// a new `post`
		$id = $this->factory->post->create();

		$this->assertEquals( '', \tribe_get_recurrence_ical_link( $id ) );
	}

	/**
	 * @test
	 * it should return empty string no global post defined
	 */
	public function it_should_return_empty_string_no_global_post_defined() {
		$this->assertEquals( '', \tribe_get_recurrence_ical_link() );
	}

	/**
	 * @test
	 * it should return empty string if no global post
	 */
	public function it_should_return_empty_string_if_no_global_post() {
		global $post;
		$post = $this->factory->post->create_and_get();

		$this->assertEquals( '', \tribe_get_recurrence_ical_link() );
	}

	/**
	 * @test
	 * it should return empty string if global post is not an event
	 */
	public function it_should_return_empty_string_if_global_post_is_not_an_event() {
		global $post;
		$post = $this->factory->post->create_and_get();

		$this->assertEquals( '', \tribe_get_recurrence_ical_link() );
	}

	/**
	 * @test
	 * it should return link to single event if event is not recurring
	 */
	public function it_should_return_link_to_single_event_if_event_is_not_recurring() {
		$id  = $this->factory->post->create_and_get( [ 'post_type' => $this->post_type ] );
		$url = $this->single_event_link( $id );

		$this->assertEquals( $url, \tribe_get_recurrence_ical_link( $id ) );
	}

	/**
	 * @test
	 * it should return a link to export all events in the series
	 */
	public function it_should_return_a_link_to_export_all_events_in_the_series() {
		$master_id  = $this->factory->post->create( [ 'post_type' => $this->post_type ] );
		$query_args = [
			'post_type'   => $this->post_type,
			'post_parent' => $master_id
		];
		$children   = $this->factory->post->create_many( 5, $query_args );
		$all        = array_merge( [ $master_id ], $children );
		// let's not worry about fixtures when we have filters
		add_filter( 'tribe_is_recurring_event', function ( $recurring, $post_id ) use ( $all ) {
			return in_array( $post_id, $all );
		}, 10, 2 );
		add_filter( 'tribe_get_events', function () use ( $children ) {
			return $children;
		} );

		$expected = $this->single_event_link( $master_id ) . '&event_ids=' . implode( ',', $all );
		$this->assertEquals( $expected, \tribe_get_recurrence_ical_link( $master_id ) );
	}

	/**
	 * @test
	 * it should return a link to ical export all series when passing child id
	 */
	public function it_should_return_a_link_to_ical_export_all_series_when_passing_child_id() {
		$master_id  = $this->factory->post->create( [ 'post_type' => $this->post_type ] );
		$query_args = [
			'post_type'   => $this->post_type,
			'post_parent' => $master_id
		];
		$children   = $this->factory->post->create_many( 5, $query_args );
		$all        = array_merge( [ $master_id ], $children );
		// let's not worry about fixtures when we have filters
		add_filter( 'tribe_is_recurring_event', function ( $recurring, $post_id ) use ( $all ) {
			return in_array( $post_id, $all );
		}, 10, 2 );
		add_filter( 'tribe_get_events', function () use ( $children ) {
			return $children;
		} );

		$expected = $this->single_event_link( $master_id ) . '&event_ids=' . implode( ',', $all );

		foreach ( $children as $child ) {
			$this->assertEquals( $expected, \tribe_get_recurrence_ical_link( $child ) );
		}
	}

	/**
	 * @test
	 * it should allow for filtering the link
	 */
	public function it_should_allow_for_filtering_the_link() {
		$master_id = $this->factory->post->create( [ 'post_type' => $this->post_type ] );
		$test_url  = 'http://google.com';

		add_filter( 'tribe_get_recurrence_ical_link', function ( $link ) use ( $test_url ) {
			return $test_url;
		} );

		$this->assertEquals( $test_url, \tribe_get_recurrence_ical_link( $master_id ) );
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public function single_event_link( $id ) {
		$link = trailingslashit( get_permalink( $id ) );
		$url  = trailingslashit( esc_url_raw( $link ) ) . '?ical=1';

		return $url;
	}
}