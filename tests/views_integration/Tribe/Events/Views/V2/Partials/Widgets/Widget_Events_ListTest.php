<?php

namespace Tribe\Events\Views\V2\Partials\Widgets;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Widget_Events_ListTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'widgets/widget-events-list';

	/**
	 * Test render with upcoming events
	 */
	public function test_render_with_upcoming_events() {
		$event1  = $this->get_mock_event( 'events/single/1.json' );
		$event2  = $this->get_mock_event( 'events/single/2.json' );
		$context = [
			'events'                     => [ $event1, $event2 ],
			'json_ld_data'               => '{}',
			'is_initial_load'            => true,
			'rest_url'                   => 'https://rest.tri.be/',
			'rest_nonce'                 => '1122334455',
			'should_manage_url'          => false,
			'container_classes'          => [ 'tribe-common', 'tribe-events', 'tribe-events-widget' ],
			'container_data'             => [],
			'breakpoint_pointer'         => 'aabbccddee',
			'messages'                   => [],
			'hide_if_no_upcoming_events' => false,
			'view_more_url'              => 'https://test.tri.be/',
			'view_more_text'             => 'View More',
			'view_more_title'            => 'View more events.',
			'widget_title'               => 'Upcoming Events',
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}

	/**
	 * Test render with no upcoming events
	 */
	public function test_render_with_no_upcoming_events() {
		$context = [
			'events'                     => [],
			'json_ld_data'               => '{}',
			'is_initial_load'            => true,
			'rest_url'                   => 'https://rest.tri.be/',
			'rest_nonce'                 => '1122334455',
			'should_manage_url'          => false,
			'container_classes'          => [ 'tribe-common', 'tribe-events', 'tribe-events-widget' ],
			'container_data'             => [],
			'breakpoint_pointer'         => 'aabbccddee',
			'messages'                   => [
				'notice' => [
					'There are no upcoming events.',
				],
			],
			'hide_if_no_upcoming_events' => false,
			'view_more_url'              => 'https://test.tri.be/',
			'view_more_text'             => 'View More',
			'view_more_title'            => 'View more events.',
			'widget_title'               => 'Upcoming Events',
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}

	/**
	 * Test render with hide if no upcoming events
	 */
	public function test_render_with_hide_if_no_upcoming_events() {
		$context = [
			'events'                     => [],
			'json_ld_data'               => '{}',
			'is_initial_load'            => true,
			'rest_url'                   => 'https://rest.tri.be/',
			'rest_nonce'                 => '1122334455',
			'should_manage_url'          => false,
			'container_classes'          => [ 'tribe-common', 'tribe-events', 'tribe-events-widget' ],
			'container_data'             => [],
			'breakpoint_pointer'         => 'aabbccddee',
			'messages'                   => [
				'notice' => [
					'There are no upcoming events.',
				],
			],
			'hide_if_no_upcoming_events' => true,
			'view_more_url'              => 'https://test.tri.be/',
			'view_more_text'             => 'View More',
			'view_more_title'            => 'View more events.',
			'widget_title'               => 'Upcoming Events',
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}
}
