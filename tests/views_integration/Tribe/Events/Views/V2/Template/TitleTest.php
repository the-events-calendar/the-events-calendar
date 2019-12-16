<?php

namespace Tribe\Events\Views\V2\Template;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe__Events__Main as TEC;

class TitleTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;
	use With_Post_Remapping;

	public function setUp() {
		parent::setUp();

		// Reset the context.
		tribe_singleton( 'context', 'Tribe__Context' );

		$return_mock_url = static function () {
			return 'http://products.tribe';
		};
		add_filter( 'option_home', $return_mock_url );
	}


	public function test_featured_single_event_title() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$context = tribe_context()->alter( [
			'post_id'         => $event->ID,
			'single'          => true,
			'event_post_type' => true,
			'featured'        => false,
		] );

		codecept_debug( $context );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_featured_event_archive(  ) {
		$context = tribe_context()->alter( [
			'single'          => false,
			'event_post_type' => true,
			'featured'        => true,
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_w_date_wo_posts(  ) {
		$context = tribe_context()->alter( [
			'single'          => false,
			'event_post_type' => true,
			'featured'        => false,
			'event_date'        => '2018-02-01',
		] );

		$title = new Title();
		$title->set_context( $context );
		$title->set_posts( [] );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_w_date_w_posts(  ) {
		$context = tribe_context()->alter( [
			'single'          => false,
			'event_post_type' => true,
			'featured'        => false,
			'event_date'      => '2018-02-02',
		] );
		$event_1  = $this->get_mock_event( 'events/single/1.template.json', [
			'id'         => 23,
			'start_date' => '2018-01-01',
			'end_date'   => '2018-01-01',
		] );
		$event_2   = $this->get_mock_event( 'events/single/1.template.json', [
			'id'         => 89,
			'start_date' => '2018-03-03',
			'end_date'   => '2018-03-03',
		] );

		$title = new Title();
		$title->set_context( $context );
		$title->set_posts( [ $event_1, $event_2 ] );

		$this->assertMatchesSnapshot( $title->build_title() );

		$context = tribe_context()->alter( [
			'single'          => false,
			'event_post_type' => true,
			'featured'        => true,
			'event_date'      => '2018-02-02',
		] );

		$title->set_context( $context );
		$title->set_posts( [ $event_1, $event_2 ] );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_w_past_events(  ) {
		$context = tribe_context()->alter( [
			'single'          => false,
			'event_post_type' => true,
			'event_display' => 'past'
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_month_view(  ) {
		$context = tribe_context()->alter( [
			'single'          => false,
			'event_post_type' => true,
			'event_display' => 'month',
			'event_date' => '2019-02',
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_day_view(  ) {
		$context = tribe_context()->alter( [
			'single'          => false,
			'event_post_type' => true,
			'event_display' => 'day',
			'event_date' => '2019-02-02',
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}

	public function test_w_category(  ) {
		static::factory()->term->create( [ 'taxonomy' => TEC::TAXONOMY, 'slug' => 'test', 'name' => 'test' ] );

		$context = tribe_context()->alter( [
			'single'          => false,
			'event_post_type' => true,
			'taxonomy' => TEC::TAXONOMY,
			TEC::TAXONOMY => 'test',
		] );

		$title = new Title();
		$title->set_context( $context );

		$this->assertMatchesSnapshot( $title->build_title() );
	}
}
