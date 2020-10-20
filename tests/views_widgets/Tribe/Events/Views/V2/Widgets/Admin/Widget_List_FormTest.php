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

		add_filter(
			"tribe_widget_{$widget->get_registration_slug()}_arguments",
			static function ( array $arguments ) use ( $instance ) {
				return wp_parse_args(
					$instance,
					$arguments
				);
			}
		);

		$arguments = $widget->setup_arguments();
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

		add_filter(
			"tribe_widget_{$widget->get_registration_slug()}_arguments",
			static function ( array $arguments ) use ( $instance ) {
				return wp_parse_args(
					$instance,
					$arguments
				);
			}
		);

		$arguments = $widget->setup_arguments();
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

		add_filter(
			"tribe_widget_{$widget->get_registration_slug()}_arguments",
			static function ( array $arguments ) use ( $instance ) {
				return wp_parse_args(
					$instance,
					$arguments
				);
			}
		);

		$arguments = $widget->setup_arguments();
		$html = $widget->get_admin_template()->template( 'widgets/list', $arguments, false );

		$this->assertMatchesSnapshot( $html );
	}
}
