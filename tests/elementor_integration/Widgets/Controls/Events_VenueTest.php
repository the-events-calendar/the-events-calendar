<?php
namespace TEC\Events\Integrations\Plugins\Elementor\Widgets\Controls;

use Codeception\TestCase\WPTestCase;
use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Venue;

/**
 * Tests for the Event Venue widget controls.
 */
class Events_VenueTest extends WPTestCase {
	/**
	 * Helper to get the widget controls after registration.
	 */
	protected function get_widget_controls() {
		$widget = new Event_Venue();

		return $widget->get_controls();
	}

	public function test_registers_content_sections(): void {
		$controls = $this->get_widget_controls();

		// Content sections.
		foreach ( [
			'section_title',
			'venue_name_content_options',
			'venue_address_content_options',
			'venue_phone_content_options',
			'venue_website_content_options',
			'venue_map_content_options',
			'event_query_section',
		] as $section_id ) {
			$this->assertArrayHasKey( $section_id, $controls );
			$this->assertEquals( Controls_Manager::SECTION, $controls[ $section_id ]['type'] );
		}
	}

	public function test_registers_content_controls_and_defaults(): void {
		$controls = $this->get_widget_controls();

		// Core content toggles and fields.
		$expected = [
			'show_venue_header',
			'venue_header_tag',
			'show_venue_name',
			'venue_name_html_tag',
			'show_venue_address',
			'show_venue_address_header',
			'venue_address_header_tag',
			'show_venue_address_map_link',
			'show_venue_phone',
			'show_venue_phone_header',
			'venue_phone_header_tag',
			'link_venue_phone',
			'show_venue_website',
			'show_venue_website_header',
			'venue_website_header_tag',
			'venue_website_link_target',
			'show_venue_map',
		];

		foreach ( $expected as $id ) {
			$this->assertArrayHasKey( $id, $controls );
		}

		// Spot-check key defaults.
		$this->assertEquals( 'no', $controls['show_venue_header']['default'] );
		$this->assertEquals( 'yes', $controls['show_venue_name']['default'] );
		$this->assertEquals( 'yes', $controls['show_venue_address']['default'] );
		$this->assertEquals( 'no', $controls['show_venue_address_header']['default'] );
		$this->assertEquals( 'yes', $controls['show_venue_address_map_link']['default'] );
		$this->assertEquals( 'yes', $controls['show_venue_phone']['default'] );
		$this->assertEquals( 'no', $controls['show_venue_phone_header']['default'] );
		$this->assertEquals( 'yes', $controls['show_venue_website']['default'] );
		$this->assertEquals( 'no', $controls['show_venue_website_header']['default'] );
		$this->assertEquals( 'yes', $controls['show_venue_map']['default'] );
		$this->assertEquals( 'yes', $controls['link_venue_phone']['default'] );
		$this->assertEquals( '_self', $controls['venue_website_link_target']['default'] );
	}

	public function test_registers_style_sections(): void {
		$controls = $this->get_widget_controls();

		// Style sections.
		foreach ( [
			'venue_section_header_styling',
			'venue_name_styling',
			'venue_address_styling',
			'venue_phone_styling',
			'venue_website_styling',
			'styling_section_title', // Map style section id.
		] as $section_id ) {
			$this->assertArrayHasKey( $section_id, $controls );
			$this->assertEquals( Controls_Manager::SECTION, $controls[ $section_id ]['type'] );
		}
	}

	public function test_registers_style_controls(): void {
		$widget = new Event_Venue();

		// Alignment and responsive controls present in style panels.
		foreach ( [
			'venue_header_align',
			'venue_name_align',
			'phone_header_align',
			'phone_number_align',
			'address_header_align',
			'address_venue_map_link_align',
			'website_label_align',
			'website_url_align',
		] as $control_id ) {
			$this->assertNotNull( $widget->get_controls( $control_id ), "Missing style control: {$control_id}" );
		}
	}

	public function test_style_section_conditions(): void {
		$controls = $this->get_widget_controls();

		$this->assertEquals( [ 'show_venue_header' => 'yes' ], $controls['venue_section_header_styling']['condition'] );
		$this->assertEquals( [ 'show_venue_name' => 'yes' ], $controls['venue_name_styling']['condition'] );
		$this->assertEquals( [ 'show_venue_address' => 'yes' ], $controls['venue_address_styling']['condition'] );
		$this->assertEquals( [ 'show_venue_phone' => 'yes' ], $controls['venue_phone_styling']['condition'] );
		$this->assertEquals( [ 'show_venue_website' => 'yes' ], $controls['venue_website_styling']['condition'] );
		$this->assertEquals( [ 'show_venue_map' => 'yes' ], $controls['styling_section_title']['condition'] );
	}
}
