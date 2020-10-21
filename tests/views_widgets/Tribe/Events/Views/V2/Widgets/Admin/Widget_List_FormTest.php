<?php
namespace Tribe\Events\Views\V2\Views\Widgets\Admin;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\Widgets\Widget_List;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;

class Widget_List_FormTest extends ViewTestCase {

	use MatchesSnapshots;

	public function setUp() {
		parent::setUp();
		\Tribe__Rewrite::instance()->setup();
	}

	/**
	 * @test
	 */
	public function test_with_default_arguments() {
		$widget = new Widget_List();
		$instance = [];
		$arguments = $widget->get_arguments( $instance );

		$html = $widget->get_admin_template()->template( 'widgets/list', $arguments, false );

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * @test
	 */
	public function test_with_new_arguments() {
		$widget = new Widget_List();
		$instance = [
			'title'                => 'Event List Widget Test',
			'limit'                => '7',
			'no_upcoming_events'   => true,
			'featured_events_only' => true,
			'jsonld_enable'        => false,
		];

		$arguments = $widget->get_arguments( $instance );
		$html = $widget->get_admin_template()->template( 'widgets/list', $arguments, false );

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * @test
	 */
	public function test_with_filtered_limit_arguments() {
		$widget = new Widget_List();
		$instance = [
			'title'                => 'Event List High Limit',
			'limit'                => '12',
			'no_upcoming_events'   => true,
		];

		add_filter(
			'tribe_events_widget_list_events_max_limit',
			static function () {
				return 15;
			}
		);

		$arguments = $widget->get_arguments( $instance );
		$html = $widget->get_admin_template()->template( 'widgets/list', $arguments, false );

		$this->assertMatchesSnapshot( $html );
	}
}
