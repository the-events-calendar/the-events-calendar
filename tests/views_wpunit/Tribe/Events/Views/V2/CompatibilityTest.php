<?php

namespace Tribe\Events\V2\Views\Widgets;

use Tribe\Events\Views\V2\Widgets\Compatibility;

class CompatibilityTest extends \Codeception\TestCase\WPTestCase {

	protected $sidebars = [
		'wp_inactive_widgets' => [],
		'header-right'        => [
			'tribe-events-list-widget-10',
		],
		'sidebar'             => [
			'tribe-events-adv-list-widget-8',
			'tribe-events-list-widget-5',
		],
		'array_version'       => 3,
	];

	protected $adv_list_widget = [
		8 => [
			'title' => 'Pro 1',
			'limit' => '5',
		]
	];

	protected $list_widget = [
		10 => [
			'title' => 'Free 1',
			'limit' => '3',
		],
		5  => [
			'title' => 'Free 2',
			'limit' => '7',
		],
	];

	public function setUp() {
		// before
		parent::setUp();

		// Add base options.
		update_option( 'sidebars_widgets', $this->sidebars );
		update_option( 'widget_tribe-events-adv-list-widget', $this->adv_list_widget );
		update_option( 'widget_tribe-events-list-widget', $this->list_widget );

		// This method would be called only on `tribe_plugins_loaded`; that will not be called in the tests flow, we call it now.
		tribe( Compatibility::class )->switch_compatibility();
	}

	/**
	 * @return Compatibility.
	 */
	private function make_instance() {
		return new Compatibility();
	}

	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Compatibility::class, $sut );
	}

	/**
	 * @test
	 */
	public function it_should_merge_pro_into_free_when_v2_is_active() {
		$widgets = get_option( 'widget_tribe-events-list-widget' );

		$this->assertEquals( 3, count( $widgets ) );
	}

	/**
	 * @test
	 */
	public function it_should_change_pro_to_free_when_v2_is_active() {
		$sidebars = get_option( 'sidebars_widgets' );

		$this->assertEquals( 'tribe-events-list-widget-8', $sidebars['sidebar'][0] );
	}

	/**
	 * @test
	 */
	public function it_should_merge_free_into_pro_when_v1_is_active() {
		// Remove the initial option filter.
		remove_filter( 'option_widget_tribe-events-list-widget', [ tribe( Compatibility::class ), 'merge_list_widget_options' ] );
		// Setup new settings and recreate the option filters.
		add_filter( 'tribe_events_views_v2_advanced_list_widget_primary', '__return_true' );
		defined( 'TRIBE_EVENTS_V2_VIEWS' ) ? null : define( 'TRIBE_EVENTS_V2_VIEWS', false );
		$this->make_instance()->switch_compatibility();
		$widgets = get_option( 'widget_tribe-events-adv-list-widget' );

		$this->assertEquals( 3, count( $widgets ) );
	}

	/**
	 * @test
	 */
	public function it_should_change_free_with_pro_when_v1_is_active() {
		// Remove the initial option filter.
		remove_filter( 'option_sidebars_widgets', [ tribe( Compatibility::class ), 'remap_list_widget_id_bases' ] );
		// Setup new settings and recreate the option filters.
		add_filter( 'tribe_events_views_v2_advanced_list_widget_primary', '__return_true' );
		defined( 'TRIBE_EVENTS_V2_VIEWS' ) ? null : define( 'TRIBE_EVENTS_V2_VIEWS', false );
		$compatibility = $this->make_instance();
		$compatibility->switch_compatibility();
		add_filter( 'option_sidebars_widgets', [ $compatibility, 'remap_list_widget_id_bases' ] );
		$sidebars = get_option( 'sidebars_widgets' );

		$this->assertEquals( 'tribe-events-adv-list-widget-10', $sidebars['header-right'][0] );
		$this->assertEquals( 'tribe-events-adv-list-widget-5', $sidebars['sidebar'][1] );
	}

	/**
	 * @test
	 */
	public function it_should_merge_free_into_pro_when_v1_is_active_by_constant() {
		// Remove the initial option filter.
		remove_filter( 'option_widget_tribe-events-list-widget', [ tribe( Compatibility::class ), 'merge_list_widget_options' ] );
		// Setup new settings and recreate the option filters.
		add_filter( 'tribe_events_views_v2_advanced_list_widget_primary', '__return_true' );
		defined( 'TRIBE_EVENTS_WIDGETS_V2_DISABLED' ) ? null : define( 'TRIBE_EVENTS_WIDGETS_V2_DISABLED', true );
		$this->make_instance()->switch_compatibility();
		$widgets = get_option( 'widget_tribe-events-adv-list-widget' );

		$this->assertEquals( 3, count( $widgets ) );
	}

	/**
	 * @test
	 */
	public function it_should_change_free_with_pro_when_v1_is_active_by_constant() {
		// Remove the initial option filter.
		remove_filter( 'option_sidebars_widgets', [ tribe( Compatibility::class ), 'remap_list_widget_id_bases' ] );
		// Setup new settings and recreate the option filters.
		add_filter( 'tribe_events_views_v2_advanced_list_widget_primary', '__return_true' );
		defined( 'TRIBE_EVENTS_WIDGETS_V2_DISABLED' ) ? null : define( 'TRIBE_EVENTS_WIDGETS_V2_DISABLED', true );
		$compatibility = $this->make_instance();
		$compatibility->switch_compatibility();
		add_filter( 'option_sidebars_widgets', [ $compatibility, 'remap_list_widget_id_bases' ] );
		$sidebars = get_option( 'sidebars_widgets' );

		$this->assertEquals( 'tribe-events-adv-list-widget-10', $sidebars['header-right'][0] );
		$this->assertEquals( 'tribe-events-adv-list-widget-5', $sidebars['sidebar'][1] );
	}
}
