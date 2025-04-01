<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe__Date_Utils as Dates;
use TEC\Events\Category_Colors\Repositories\Category_Color_Priority_Category_Provider;

class Category_Color_List_ViewTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	/**
	 * The path to the partial template.
	 *
	 * @var string
	 */
	protected $partial_path = 'list/event';

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
	 * Test render with event in List view with category colors
	 */
	public function test_render_with_event_list_view() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		// Add categories to the event
		wp_set_object_terms( $event->ID, ['red-category', 'green-category'], 'tribe_events_cat' );

		// Get the highest priority category
		$priority_provider = new Category_Color_Priority_Category_Provider();
		$highest_priority_category = $priority_provider->get_highest_priority_category( $event );

		$request_date = Dates::build_date_object( $event->dates->start_display->sub( new \DateInterval( 'P1D' ) ) );
		$html = $this->get_partial_html( [
			'event' => $event,
			'is_past' => false,
			'request_date' => $request_date,
			'category_colors_enabled' => true,
			'category_colors_priority_category' => $highest_priority_category,
			'categories' => get_the_terms( $event->ID, 'tribe_events_cat' ),
		] );

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * Test that category colors are not applied when disabled
	 */
	public function test_render_with_category_colors_disabled() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		// Add categories to the event
		wp_set_object_terms( $event->ID, ['red-category', 'green-category'], 'tribe_events_cat' );

		$request_date = Dates::build_date_object( $event->dates->start_display->sub( new \DateInterval( 'P1D' ) ) );
		$html = $this->get_partial_html( [
			'event' => $event,
			'is_past' => false,
			'request_date' => $request_date,
			'category_colors_enabled' => false,
			'categories' => get_the_terms( $event->ID, 'tribe_events_cat' ),
		] );

		$this->assertMatchesSnapshot( $html );
	}
} 