<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Tests\Traits\With_Uopz;
use Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets\Filter_Trait;


class Event_TitleTest extends WPTestCase {
	use SnapshotAssertions;
	use With_Uopz;
	use Filter_Trait;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_event_title_template_data';

	public function setUp(): void {
		parent::setUp();

		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[ 'html_tag' => 'p', 'title' => 'Event Title' ]
		);
	}

	/**
	 * Data provider for tests.
	 * label is the key to be used in the filter.
	 * value is the value to be used in the filter.
	 * string is the string to be checked for in the rendered HTML.
	 */
	public function data_provider(): Generator {
		yield 'header_tag' => [
			static function () {
				return [
					'label'  => 'header_tag',
					'value'  => 'h2',
					'string' => '<h2',
					'additional' => [
						'title' => 'Backup Title',
					]
				];
			},
		];
		yield 'title' => [
			static function () {
				return [
					'label'  => 'title',
					'value'  => 'Filtered Title',
					'string' => 'Filtered Title',
				];
			},
		];
		yield 'no_title' => [
			static function () {
				return [
					'label'  => 'title',
					'value'  => '',
					'render' => false,
				];
			},
		];
	}

	/**
	 * Test render with html filtered.
	 *
	 * @dataProvider data_provider
	 */
	public function test_render_filtered( Closure $passed ) {
		$object = $passed();

		$widget = Event_Title::class;

		$this->render_filtered( $object, $widget );
	}

	/**
	 * A stored tag that is not an allowed HTML tag must never be printed as an element name.
	 *
	 * @dataProvider data_unsafe_tags
	 */
	public function test_it_should_not_render_unsafe_header_tag( string $tag ) {
		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[ 'header_tag' => $tag ]
		);
		$this->filter_template_data( [ 'title' => 'document.body.dataset.x=1' ] );

		$output = $this->tidy_render( ( new Event_Title() )->get_output() );

		$this->assertStringNotContainsString( '<' . $tag, $output );
		$this->assertStringContainsString( '<div', $output );
	}

	public function data_unsafe_tags(): Generator {
		yield 'script' => [ 'script' ];
		yield 'style' => [ 'style' ];
		yield 'iframe' => [ 'iframe' ];
	}

	/**
	 * Tags a site owner may legitimately filter in stay untouched.
	 */
	public function test_it_should_keep_an_allowed_header_tag() {
		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[ 'header_tag' => 'h4' ]
		);
		$this->filter_template_data( [ 'title' => 'Event Title' ] );

		$this->assertStringContainsString( '<h4', $this->tidy_render( ( new Event_Title() )->get_output() ) );
	}

	/**
	 * @dataProvider data_extra_safe_tags
	 */
	public function test_it_should_keep_the_extra_safe_tags( string $tag ) {
		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[ 'header_tag' => $tag ]
		);
		$this->filter_template_data( [ 'title' => 'Event Title' ] );

		$this->assertStringContainsString( '<' . $tag, $this->tidy_render( ( new Event_Title() )->get_output() ) );
	}

	public function data_extra_safe_tags(): Generator {
		yield 'section' => [ 'section' ];
		yield 'header' => [ 'header' ];
		yield 'nav' => [ 'nav' ];
		yield 'b' => [ 'b' ];
		yield 'em' => [ 'em' ];
		yield 'i' => [ 'i' ];
		yield 'strong' => [ 'strong' ];
	}

	public function test_it_should_let_the_allowed_tags_filter_add_a_tag() {
		add_filter(
			'tec_events_elementor_widget_allowed_tags',
			static function ( array $tags ): array {
				$tags[] = 'address';

				return $tags;
			}
		);
		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[ 'header_tag' => 'address' ]
		);
		$this->filter_template_data( [ 'title' => 'Event Title' ] );

		$this->assertStringContainsString( '<address', $this->tidy_render( ( new Event_Title() )->get_output() ) );
	}

	public function test_it_should_let_the_allowed_tags_filter_add_any_tag_on_purpose() {
		add_filter(
			'tec_events_elementor_widget_allowed_tags',
			static function ( array $tags ): array {
				$tags[] = 'script';

				return $tags;
			}
		);
		$this->set_class_fn_return(
			'Elementor\\Controls_Stack',
			'get_settings_for_display',
			[ 'header_tag' => 'script' ]
		);
		$this->filter_template_data( [ 'title' => 'Event Title' ] );

		$this->assertStringContainsString( '<script', $this->tidy_render( ( new Event_Title() )->get_output() ) );
	}
}
