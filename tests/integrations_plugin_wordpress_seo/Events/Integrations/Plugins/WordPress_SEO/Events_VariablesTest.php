<?php

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;

use Codeception\TestCase\WPTestCase;

/**
 * Class Events_VariablesTest
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */
class Events_VariablesTest extends WPTestCase {

	/**
	 * @var \TEC\Events\Integrations\Plugins\WordPress_SEO\Events_Variables
	 */
	protected $sut;

	/**
	 * Set up the test case.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->sut = new Events_Variables();
		$this->sut->register();

    add_filter( 'tribe_date_format', function() { return 'Y-m-d'; } );
	}

	/**
	 * Clean up after the test case.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function tearDown(): void {
		remove_all_filters( 'tribe_date_format' );
		wp_reset_postdata();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function it_registers_custom_yoast_variables() {
		global $wp_filter;
		$this->assertArrayHasKey( 'wpseo_register_extra_replacements', $wp_filter );
	}

	/**
	 * Creates a test event with all needed meta/properties and sets up global postdata.
	 *
	 * @since TBD
	 *
	 * @return int Event post ID.
	 */
	protected function get_test_event() {
		$venue_id = $this->factory()->post->create([
			'post_type'  => 'tribe_venue',
			'post_title' => 'Test Venue',
			'meta_input' => [
				'_VenueCity'  => 'Test City',
				'_VenueState' => 'CA',
			],
		]);

		$organizer_id = $this->factory()->post->create([
			'post_type'  => 'tribe_organizer',
			'post_title' => 'Test Organizer',
		]);

		$event_id = $this->factory()->post->create([
			'post_type'  => 'tribe_events',
			'meta_input' => [
				'_EventStartDate'    => '2035-08-02 21:30:00',
				'_EventEndDate'      => '2035-08-03 23:30:00',
				'_EventVenueID'      => $venue_id,
				'_EventOrganizerID'  => $organizer_id,
			],
		]);

		global $post;
		$post = get_post($event_id);
		setup_postdata($post);

		return $event_id;
	}

	/**
	 * @test
	 */
	public function it_replaces_event_start_date_variable() {
		$result = $this->sut->get_event_start_date( $this->get_test_event() );
		$this->assertStringContainsString( '2035-08-02', $result );
	}

	/**
	 * @test
	 */
	public function it_replaces_event_end_date_variable() {
		$result = $this->sut->get_event_end_date( $this->get_test_event() );
		$this->assertStringContainsString( '2035-08-03', $result );
	}

	/**
	 * @test
	 */
	public function it_replaces_venue_title_variable() {
		$result = $this->sut->get_venue_title( $this->get_test_event() );
		$this->assertEquals( 'Test Venue', $result );
	}

	/**
	 * @test
	 */
	public function it_replaces_venue_city_variable() {
		$result = $this->sut->get_venue_city( $this->get_test_event() );
		$this->assertEquals( 'Test City', $result );
	}

	/**
	 * @test
	 */
	public function it_replaces_venue_state_variable() {
		$result = $this->sut->get_venue_state( $this->get_test_event() );
		$this->assertEquals( 'CA', $result );
	}

	/**
	 * @test
	 */
	public function it_replaces_organizer_title_variable() {
		$result = $this->sut->get_organizer_title( $this->get_test_event() );
		$this->assertEquals( 'Test Organizer', $result );
	}
}
