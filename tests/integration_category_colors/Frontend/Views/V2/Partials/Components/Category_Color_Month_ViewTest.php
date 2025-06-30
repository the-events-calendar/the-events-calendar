<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe__Date_Utils as Dates;
use TEC\Events\Category_Colors\Repositories\Category_Color_Priority_Category_Provider;

class Category_Color_Month_ViewTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	/**
	 * The path to the partial template.
	 *
	 * @var string
	 */
	protected $partial_path = 'month/calendar-body';

	/**
	 * @before
	 */
	public function before() {
		parent::setUp();
		// Create test categories with colors
		$this->create_test_categories();
	}

	/**
	 * Create test categories with color metadata
	 */
	protected function create_test_categories() {
		$categories = [
			'red-category' => [
				'name'     => 'Red Category',
				'primary'  => '#ff0000',
				'priority' => 1,
			],
			'green-category' => [
				'name'     => 'Green Category',
				'primary'  => '#00ff00',
				'priority' => 2,
			],
		];

		foreach ( $categories as $slug => $data ) {
			$term = wp_insert_term( $data['name'], 'tribe_events_cat' );
			if ( ! is_wp_error( $term ) ) {
				update_term_meta( $term['term_id'], '_tec_category_color_primary', $data['primary'] );
				update_term_meta( $term['term_id'], '_tec_category_color_priority', $data['priority'] );
			}
		}
	}

	/**
	 * Test render with event in Month view with category colors
	 */
	public function test_render_with_event_month_view() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		// Add categories to the event
		wp_set_object_terms( $event->ID, ['red-category', 'green-category'], 'tribe_events_cat' );

		// Get the highest priority category
		$priority_provider = new Category_Color_Priority_Category_Provider();
		$highest_priority_category = $priority_provider->get_highest_priority_category( $event );

		$days = $this->get_month_days_with_event( $event );
		$event_date = $event->dates->start->format( 'Y-m-d' );

		$html = $this->get_partial_html( [
			'event' => $event,
			'category_colors_enabled' => true,
			'days' => $days,
			'today_date' => $event_date,
			'grid_date' => $event_date,
			'formatted_grid_date' => $event_date,
			'is_past' => false,
			'is_today' => true,
			'is_future' => false,
			'is_current' => true,
			'is_week_start' => true,
			'is_week_end' => false,
			'is_month_start' => true,
			'is_month_end' => false,
			'year_number' => (int) $event->dates->start->format( 'Y' ),
			'month_number' => (int) $event->dates->start->format( 'm' ),
			'day_number' => (int) $event->dates->start->format( 'j' ),
			'events' => [ $event ],
			'featured_events' => [],
			'multiday_events' => [],
			'found_events' => 1,
			'more_events' => 0,
			'day_url' => tribe_events_get_url( [ 'eventDisplay' => 'day', 'eventDate' => $event_date ] ),
			'category_colors_priority_category' => $highest_priority_category,
		] );

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * Get month days with event
	 */
	protected function get_month_days_with_event( $event ) {
		$period = new \DatePeriod(
			new \DateTime( '2019-07-01 00:00:00' ),
			new \DateInterval( 'P1D' ),
			new \DateTime( '2019-08-04 23:59:00' )
		);
		$days = [];
		/** @var \DateTime $date_object */
		foreach ( $period as $date_object ) {
			$day_date = $date_object->format( 'Y-m-d' );
			$days[ $day_date ] = [
				'date'             => $day_date,
				'is_start_of_week' => 1 === $date_object->format( 'N' ),
				'year_number'      => (int) $date_object->format( 'Y' ),
				'month_number'     => (int) $date_object->format( 'm' ),
				'day_number'       => (int) $date_object->format( 'j' ),
				'events'           => $day_date === $event->dates->start->format( 'Y-m-d' ) ? [ $event ] : [],
				'featured_events'  => [],
				'multiday_events'  => [],
				'found_events'     => $day_date === $event->dates->start->format( 'Y-m-d' ) ? 1 : 0,
				'more_events'      => 0,
				'day_url'          => tribe_events_get_url( [ 'eventDisplay' => 'day', 'eventDate' => $day_date ] ),
			];
		}
		return $days;
	}
} 