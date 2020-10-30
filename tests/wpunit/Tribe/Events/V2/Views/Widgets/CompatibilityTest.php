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
	 * Scenarios:
	 * * TEC with Pro Activated in V1 ( Reverse )
	 * * Pro Disabled to TEC in V1 ( Default ) - not supported
	 * * XV1 to V2 ( Default )
	 * * Pro Disabled V2 Active ( Default )
	 * * Pro Active V2 to V1 with constant ( Reverse )
	 * * Pro Disabled V2 back to V1 with constant ( Default ) - not supported
	 */

	/**
	 * @test
	 */
	public function it_should_change_pro_to_free_when_v2_is_active() {

		$sidebars = get_option( 'sidebars_widgets' );
		var_dump('get_option');
		$widgets = get_option( 'widget_tribe-events-list-widget' );

		//var_dump($sidebars);
		var_dump(count( $widgets ));
	}
}
