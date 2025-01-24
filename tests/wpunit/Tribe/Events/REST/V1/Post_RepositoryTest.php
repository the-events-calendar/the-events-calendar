<?php

namespace Tribe\Events\REST\V1;

use Prophecy\Argument;
use Tribe\Events\Test\Testcases\Events_TestCase;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Cost_Utils as Cost_Utils;
use Tribe__Events__Main as Main;
use Tribe__Events__REST__V1__Messages as Messages;
use Tribe__Events__REST__V1__Post_Repository as Post_Repository;
use Tribe__Events__Main as TEC;

class Post_RepositoryTest extends Events_TestCase {
	use With_Uopz;

	protected $backups = [
		'tec.cost-utils',
	];

	/**
	 * @var \Tribe__REST__Messages_Interface
	 */
	protected $messages;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->messages = new Messages();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Post_Repository::class, $sut );
	}

	/**
	 * @test
	 * it should return a WP_Error when trying to get event data for non existing post
	 */
	public function it_should_return_a_wp_error_when_trying_to_get_event_data_for_non_existing_post() {
		$sut = $this->make_instance();

		$data = $sut->get_event_data( 22131 );

		/** @var \WP_Error $data */
		$this->assertWPError( $data );
		$this->assertEquals( $this->messages->get_message( 'event-not-found' ), $data->get_error_message() );
	}

	/**
	 * @test
	 * it should return a WP_Error when trying to get event data for non event
	 */
	public function it_should_return_a_wp_error_when_trying_to_get_event_data_for_non_event() {
		$sut = $this->make_instance();

		$data = $sut->get_event_data( $this->factory()->post->create() );

		/** @var \WP_Error $data */
		$this->assertWPError( $data );
		$this->assertEquals( $this->messages->get_message( 'event-not-found' ), $data->get_error_message() );
	}

	/**
	 * @test
	 * it should return an event array representation if event
	 */
	public function it_should_return_an_event_array_representation_if_event() {
		$event = $this->factory()->event->create();

		$sut  = $this->make_instance();
		$data = $sut->get_event_data( $event );

		$this->assertInternalType( 'array', $data );
	}

	/**
	 * @test
	 * it should return the array representation of an event if trying to get an event data
	 */
	public function it_should_return_the_array_representation_of_an_event_if_trying_to_get_an_event_data() {
		$event = $this->factory()->event->create();

		$sut  = $this->make_instance();
		$data = $sut->get_data( $event );

		$this->assertInternalType( 'array', $data );
		$this->assertEquals( $event, $data['id'] );
	}

	/**
	 * @test
	 * it should return a WP Error if trying to get venue data for a non existing post
	 */
	public function it_should_return_a_wp_error_if_trying_to_get_venue_data_for_a_non_existing_post() {
		$sut  = $this->make_instance();
		$data = $sut->get_venue_data( 214234 );

		$this->assertWPError( $data );
		$this->assertEquals( $this->messages->get_message( 'venue-not-found' ), $data->get_error_message() );
	}

	/**
	 * @test
	 * it should return a WP Error if trying to get venue data for not a venue and not an event
	 */
	public function it_should_return_a_wp_error_if_trying_to_get_venue_data_for_not_a_venue_and_not_an_event() {
		$sut  = $this->make_instance();
		$data = $sut->get_venue_data( $this->factory()->post->create() );

		$this->assertWPError( $data );
		$this->assertEquals( $this->messages->get_message( 'venue-not-found' ), $data->get_error_message() );
	}

	/**
	 * @test
	 * it should return venue array representation if trying to get venue data for a venue
	 */
	public function it_should_return_venue_array_representation_if_trying_to_get_venue_data_for_a_venue() {
		$venue = $this->factory()->venue->create();
		$event = $this->factory()->event->create( [ 'venue' => $venue ] );

		$sut  = $this->make_instance();
		$data = $sut->get_venue_data( $event );

		$this->assertInternalType( 'array', $data );
		$this->assertEquals( $venue, $data['id'] );
	}

	/**
	 * @test
	 * it should return a WP_Error if trying to get venue data for an event with not venue
	 */
	public function it_should_return_a_wp_error_if_trying_to_get_venue_data_for_an_event_with_not_venue() {
		$event = $this->factory()->event->create();

		$sut  = $this->make_instance();
		$data = $sut->get_venue_data( $event );

		$this->assertWPError( $data );
		$this->assertEquals( $this->messages->get_message( 'event-no-venue' ), $data->get_error_message() );
	}


	/**
	 * @test
	 * it should return venue array representation if trying to get venue data for an event with venue
	 */
	public function it_should_return_venue_array_representation_if_trying_to_get_venue_data_for_an_event_with_venue() {
		$venue = $this->factory()->venue->create();

		$sut  = $this->make_instance();
		$data = $sut->get_venue_data( $venue );

		$this->assertInternalType( 'array', $data );
		$this->assertEquals( $venue, $data['id'] );
	}

	/**
	 * @test
	 * it should return a WP Error if trying to get organizer data for a non existing post
	 */
	public function it_should_return_a_wp_error_if_trying_to_get_organizer_data_for_a_non_existing_post() {
		$sut  = $this->make_instance();
		$data = $sut->get_organizer_data( 214234 );

		$this->assertWPError( $data );
		$this->assertEquals( $this->messages->get_message( 'organizer-not-found' ), $data->get_error_message() );
	}

	/**
	 * @test
	 * it should return a WP Error if trying to get organizer data for not a organizer and not an event
	 */
	public function it_should_return_a_wp_error_if_trying_to_get_organizer_data_for_not_a_organizer_and_not_an_event() {
		$sut  = $this->make_instance();
		$data = $sut->get_organizer_data( $this->factory()->post->create() );

		$this->assertWPError( $data );
		$this->assertEquals( $this->messages->get_message( 'organizer-not-found' ), $data->get_error_message() );
	}

	/**
	 * @test
	 * it should return organizer array representation if trying to get organizer data for a organizer
	 */
	public function it_should_return_organizer_array_representation_if_trying_to_get_organizer_data_for_a_organizer() {
		$organizer = $this->factory()->organizer->create();

		$sut  = $this->make_instance();
		$data = $sut->get_organizer_data( $organizer );

		$this->assertInternalType( 'array', $data );
		$this->assertEquals( $organizer, $data['id'] );
	}

	/**
	 * @test
	 * it should return a WP_Error if trying to get organizer data for an event with not organizer
	 */
	public function it_should_return_a_wp_error_if_trying_to_get_organizer_data_for_an_event_with_not_organizer() {
		$event = $this->factory()->event->create();

		$sut  = $this->make_instance();
		$data = $sut->get_organizer_data( $event );

		$this->assertWPError( $data );
		$this->assertEquals( $this->messages->get_message( 'event-no-organizer' ), $data->get_error_message() );
	}


	/**
	 * @test
	 * it should return organizer array representation if trying to get organizer data for an event with organizer
	 */
	public function it_should_return_organizer_array_representation_if_trying_to_get_organizer_data_for_an_event_with_organizer() {
		$organizer = $this->factory()->organizer->create();
		$event     = $this->factory()->event->create( [ 'organizers' => [ $organizer ] ] );

		$sut  = $this->make_instance();
		$data = $sut->get_organizer_data( $event );

		$this->assertInternalType( 'array', $data );
		$this->assertEquals( $organizer, $data[0]['id'] );
	}

	/**
	 * @test
	 * it should return an event data if trying to get data for an event
	 */
	public function it_should_return_an_event_data_if_trying_to_get_data_for_an_event() {
		$event = $this->factory()->event->create();

		$sut  = $this->make_instance();
		$data = $sut->get_data( $event );

		$this->assertInternalType( 'array', $data );
		$this->assertEquals( $event, $data['id'] );
	}

	/**
	 * @test
	 * it should return an organizer data if trying to get data for an organizer
	 */
	public function it_should_return_an_organizer_data_if_trying_to_get_data_for_an_organizer() {
		$organizer = $this->factory()->organizer->create();

		$sut  = $this->make_instance();
		$data = $sut->get_data( $organizer );

		$this->assertInternalType( 'array', $data );
		$this->assertEquals( $organizer, $data['id'] );
	}

	/**
	 * @test
	 * it should return an venue data if trying to get data for an venue
	 */
	public function it_should_return_an_venue_data_if_trying_to_get_data_for_an_venue() {
		$venue = $this->factory()->venue->create();

		$sut  = $this->make_instance();
		$data = $sut->get_data( $venue );

		$this->assertInternalType( 'array', $data );
		$this->assertEquals( $venue, $data['id'] );
	}

	/**
	 * @test
	 * it should return empty array for venue if no venue is assigned to event
	 */
	public function it_should_return_empty_array_for_venue_if_no_venue_is_assigned_to_event() {
		$event = $this->factory()->event->create();

		$sut = $this->make_instance();

		$data = $sut->get_event_data( $event );

		$this->assertArrayHasKey( 'venue', $data );
		$this->assertEquals( [], $data['venue'] );
	}

	/**
	 * @test
	 * it should return empty array for organizers if no organizers is assigned to event
	 */
	public function it_should_return_empty_array_for_organizers_if_no_organizers_is_assigned_to_event() {
		$event = $this->factory()->event->create();

		$sut = $this->make_instance();

		$data = $sut->get_event_data( $event );

		$this->assertArrayHasKey( 'organizer', $data );
		$this->assertEquals( [], $data['organizer'] );
	}

	/**
	 * @test
	 * it should return empty array for tags if no tags are assigned to event
	 */
	public function it_should_return_empty_array_for_tags_if_no_tags_are_assigned_to_event() {
		$event = $this->factory()->event->create();

		$sut = $this->make_instance();

		$data = $sut->get_event_data( $event );

		$this->assertArrayHasKey( 'tags', $data );
		$this->assertEquals( [], $data['tags'] );
	}

	/**
	 * @test
	 * it should return empty array for categories if no categories are assigned to event
	 */
	public function it_should_return_empty_array_for_categories_if_no_categories_are_assigned_to_event() {
		$event = $this->factory()->event->create();

		$sut = $this->make_instance();

		$data = $sut->get_event_data( $event );

		$this->assertArrayHasKey( 'categories', $data );
		$this->assertEquals( [], $data['categories'] );
	}


	/**
	 * @test
	 * it should include event tags in the response
	 */
	public function it_should_include_event_tags_in_the_response() {
		// need to be able to assign terms to use `tax_input`
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->factory()->term->create( [ 'slug' => 'tag-1', 'taxonomy' => 'post_tag' ] );
		$this->factory()->term->create( [ 'slug' => 'tag-2', 'taxonomy' => 'post_tag' ] );
		$event = $this->factory()->event->create( [ 'tax_input' => [ 'post_tag' => 'tag-1,tag2' ] ] );

		$sut = $this->make_instance();

		$data = $sut->get_event_data( $event );

		$this->assertArrayHasKey( 'tags', $data );
		$this->assertCount( 2, $data['tags'] );
	}

	/**
	 * @test
	 * it should include event categories in the response
	 */
	public function it_should_include_event_categories_in_the_response() {
		// need to be able to assign terms to use `tax_input`
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$cat_1 = $this->factory()->term->create( [ 'slug' => 'cat-1', 'taxonomy' => Main::TAXONOMY ] );
		$cat_2 = $this->factory()->term->create( [ 'slug' => 'cat-2', 'taxonomy' => Main::TAXONOMY ] );
		$event = $this->factory()->event->create( [ 'tax_input' => [ Main::TAXONOMY => [ $cat_1, $cat_2 ] ] ] );

		$sut = $this->make_instance();

		$data = $sut->get_event_data( $event );

		$this->assertArrayHasKey( 'categories', $data );
		$this->assertCount( 2, $data['categories'] );
	}

	/**
	 * @test
	 * it should return the event website if set
	 */
	public function it_should_return_the_event_website_if_set() {
		// need to be able to assign terms to use `tax_input`
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event = $this->factory()->event->create( [ 'meta_input' => [ '_EventURL' => 'http://example.com' ] ] );

		$sut = $this->make_instance();

		$data = $sut->get_event_data( $event );

		$this->assertArrayHasKey( 'website', $data );
		$this->assertEquals( 'http://example.com', $data['website'] );
	}

	/**
	 * @test
	 * it should use the event permalink as website if event website is empty
	 */
	public function it_should_use_the_event_permalink_as_website_if_event_website_is_empty() {
		$event = $this->factory()->event->create();
		delete_post_meta( $event, '_EventURL' );

		$sut = $this->make_instance();

		$data = $sut->get_event_data( $event );

		$this->assertArrayHasKey( 'website', $data );
		$this->assertEquals( get_the_permalink( $event ), $data['website'] );
	}

	public function cost_details() {
		return [
			[ [ 0 => 0 ], [ 0 => 0 ] ],
			[ [], [] ],
			[ [ 0 => 0 ], [ 0 ] ],
			[ [ 0 => 0, 12 => 12 ], [ 0, 12 ] ],
			[ [ 0 => 0, 12 => 12, 23 => 23 ], [ 0, 12, 23 ] ],
			[ [ 23 => 23, 12 => 12, 0 => 0 ], [ 0, 12, 23 ] ],
		];
	}

	/**
	 * @test
	 * it should set the cost details to an array of values if an events has more than one price components
	 * @dataProvider cost_details
	 */
	public function it_should_set_the_cost_details_to_an_array_of_values_if_an_events_has_more_than_one_price_components(
		$costs,
		$expected
	) {
		// need to be able to assign terms to use `meta_input`
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event_id = $this->factory()->event->create( [
			'meta_input' => [
				'_EventCurrencySymbol'   => '$',
				'_EventCurrencyPosition' => 'prefix'
			]
		] );
		/** @var Cost_Utils $cost_utils */
		$cost_utils = $this->prophesize( Cost_Utils::class );
		$cost_utils->get_formatted_event_cost( Argument::any(), true )->willReturn( 'foo cost' );
		$cost_utils->get_event_costs( Argument::any() )->will( function () use ( $costs ) {
			return $costs;
		} );
		$cost_utils->parse_separators( Argument::any() )->willReturn( [ '.', ',' ] );
		tribe_singleton( 'tec.cost-utils', $cost_utils->reveal() );

		$sut  = $this->make_instance();
		$data = $sut->get_event_data( $event_id );

		$this->assertArrayHasKey( 'cost', $data );
		$this->assertEquals( 'foo cost', $data['cost'] );
		$this->assertArrayHasKey( 'cost_details', $data );
		$this->assertEquals( '$', $data['cost_details']['currency_symbol'] );
		$this->assertEquals( 'prefix', $data['cost_details']['currency_position'] );
		$this->assertEquals( $expected, $data['cost_details']['values'] );
	}

	/**
	 * @return Post_Repository
	 */
	private function make_instance() {
		return new Post_Repository( $this->messages );
	}

	public function cost_strings() {
		return [
			'float_w_dot_separator'   => [ '25.55', 25.55 ],
			'float_w_comma_separator' => [ '25,55', 25.55, ',', '.' ],
			'int'                     => [ '23', 23 ],
		];
	}

	/**
	 * @test
	 * it should properly format cost values
	 * @dataProvider cost_strings
	 */
	public function it_should_properly_format_cost_values( $cost_string, $expected_cost, $decimal_point = '.', $thousands_sep = ',' ) {
		// need to be able to assign terms to use `meta_input`
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event_id = $this->factory()->event->create( [
			'meta_input' => [
				'_EventCost'             => $cost_string,
				'_EventCurrencySymbol'   => '$',
				'_EventCurrencyPosition' => 'prefix'
			]
		] );
		global $wp_locale;
		$wp_locale->number_format['decimal_point'] = $decimal_point;
		$wp_locale->number_format['thousands_sep'] = $thousands_sep;

		$sut  = $this->make_instance();
		$data = $sut->get_event_data( $event_id );

		$this->assertArrayHasKey( 'cost', $data );
		$this->assertEquals( [ $expected_cost ], $data['cost_details']['values'] );
	}

	public function hide_from_listings_values() {
		return [
			[ null, false ],
			[ false, false ],
			[ 'no', false ],
			[ '0', false ],
			[ 'false', false ],
			[ 0, false ],
			[ 'yes', true ],
		];
	}

	/**
	 * @test
	 * it should handle an event hide from upcoming value
	 * @dataProvider hide_from_listings_values
	 */
	public function it_should_handle_an_event_hide_from_upcoming_value( $hide, $expected ) {
		if ( null !== $hide ) {
			$id = $this->factory()->event->create( [ 'meta_input' => [ '_EventHideFromUpcoming' => $hide ] ] );
		} else {
			$id = $this->factory()->event->create();
		}

		$sut  = $this->make_instance();
		$data = $sut->get_event_data( $id );

		$this->assertArrayHasKey( 'hide_from_listings', $data );
		$this->assertEquals( $expected, $data['hide_from_listings'] );
	}

	public function menu_order_and_sticky_values() {
		return [
			[ '-1', true ],
			[ - 1, true ],
			[ 0, false ],
			[ - 2, false ],
			[ 1, false ],
			[ 2, false ],
		];
	}

	/**
	 * @test
	 * it should hide an event sticky value
	 * @dataProvider menu_order_and_sticky_values
	 */
	public function it_should_hide_an_event_sticky_value( $menu_order, $expected ) {
		$id = $this->factory()->event->create( [ 'menu_order' => $menu_order ] );

		$sut  = $this->make_instance();
		$data = $sut->get_event_data( $id );

		$this->assertArrayHasKey( 'sticky', $data );
		$this->assertEquals( $expected, $data['sticky'] );
	}

	public function featured_values() {
		return [
			[ '1', true ],
			[ 1, true ],
			[ true, true ],
			[ 0, false ],
			[ 'yes', false ],
			[ 'no', false ],
			[ false, false ],
		];
	}

	/**
	 * @test
	 * it should handle an event featured value
	 * @dataProvider featured_values
	 */
	public function it_should_handle_an_event_featured_value( $featured, $expected ) {
		if ( null !== $featured ) {
			$id = $this->factory()->event->create( [ 'meta_input' => [ '_tribe_featured' => $featured ] ] );
		} else {
			$id = $this->factory()->event->create();
		}

		$sut  = $this->make_instance();
		$data = $sut->get_event_data( $id );

		$this->assertArrayHasKey( 'featured', $data );
		$this->assertEquals( $expected, $data['featured'] );
	}

	/**
	 * It should provide JSON LD data for events when the context matches
	 *
	 * @test
	 */
	public function should_provide_json_ld_data_for_events_when_the_context_matches() {
		add_filter( 'tribe_rest_event_json_ld_data_contexts', function () {
			return [ 'foo', 'bar' ];
		} );

		$sut = $this->make_instance();

		$id = $this->factory()->event->create();

		$data = $sut->get_event_data( $id, 'foo' );

		$this->assertArrayHasKey( 'json_ld', $data );

		$data = $sut->get_event_data( $id, 'bar' );

		$this->assertArrayHasKey( 'json_ld', $data );

		$data = $sut->get_event_data( $id, 'single' );

		$this->assertArrayNotHasKey( 'json_ld', $data );
	}

	/**
	 * It should provide JSON LD data for venues when the context matches
	 *
	 * @test
	 */
	public function should_provide_json_ld_data_for_venues_when_the_context_matches() {
		add_filter( 'tribe_rest_venue_json_ld_data_contexts', function () {
			return [ 'foo', 'bar' ];
		} );

		$sut = $this->make_instance();

		$id = $this->factory()->venue->create();

		$data = $sut->get_venue_data( $id, 'foo' );

		$this->assertArrayHasKey( 'json_ld', $data );

		$data = $sut->get_venue_data( $id, 'bar' );

		$this->assertArrayHasKey( 'json_ld', $data );

		$data = $sut->get_venue_data( $id, 'single' );

		$this->assertArrayNotHasKey( 'json_ld', $data );
	}

	/**
	 * It should provide JSON LD data for organizers when the context matches
	 *
	 * @test
	 */
	public function should_provide_json_ld_data_for_organizers_when_the_context_matches() {
		add_filter( 'tribe_rest_organizer_json_ld_data_contexts', function () {
			return [ 'foo', 'bar' ];
		} );

		$sut = $this->make_instance();

		$id = $this->factory()->organizer->create();

		$data = $sut->get_organizer_data( $id, 'foo' );

		$this->assertArrayHasKey( 'json_ld', $data );

		$data = $sut->get_organizer_data( $id, 'bar' );

		$this->assertArrayHasKey( 'json_ld', $data );

		$data = $sut->get_organizer_data( $id, 'single' );

		$this->assertArrayNotHasKey( 'json_ld', $data );
	}

	public function multiple_costs_data_set() {
		return [
			'euro_dot_decimal_sep'   => [ '€6.50 - €8.50', [ 6.5, 8.5 ] ],
			'euro_comma_decimal_sep' => [ '5,50 7,50', [ 5.5, 7.5 ] ],
		];
	}

	/**
	 * It should correctly parse cost values w/ multiple costs
	 *
	 * @test
	 * @dataProvider multiple_costs_data_set
	 */
	public function should_correctly_parse_cost_values_w_multiple_costs( $cost_meta, $expected ) {
		global $wp_locale;
		$event = tribe_events()->set_args( [
			'title'      => 'Test event',
			'start_date' => 'tomorrow 9am',
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
			'_EventCost' => $cost_meta,
		] )->create();

		$repository = $this->make_instance();
		$event_data = $repository->get_event_data( $event->ID );
		$parsed     = $event_data['cost_details']['values'];

		$this->assertEquals( $expected, $parsed );
	}

	/**
	 * @after
	 */
	public function reregister_taxonomies(): void {
		TEC::instance()->register_taxonomy();
	}

	public function test_get_event_data_will_work_when_cat_tax_unregistered(): void {
		unregister_taxonomy( TEC::TAXONOMY );

		$event = tribe_events()->set_args( [
			'title'      => 'Test event',
			'start_date' => 'tomorrow 9am',
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();

		$repository = $this->make_instance();
		$event_data = $repository->get_event_data( $event->ID );

		$this->assertEquals( [], $event_data['categories'] );
	}

	public function test_get_terms_will_work_correctly_with_bad_terms():void{
		$event      = tribe_events()->set_args( [
			'title'      => 'Test event',
			'start_date' => 'tomorrow 9am',
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$cat_term_2 = static::factory()->term->create_and_get( [
			'taxonomy' => TEC::TAXONOMY,
			'name'     => 'Category 2',
		] );
		$this->set_fn_return(
			'wp_get_post_terms',
			[
				null,
				$cat_term_2,
				new \WP_Error( 'error', 'error' ),
			]
		);

		$repository = $this->make_instance();
		$terms      = $repository->get_terms( $event->ID, TEC::TAXONOMY );

		$this->assertCount( 1, $terms );
		$this->assertEquals( $cat_term_2->term_id, $terms[0]['id'] );
	}
}
