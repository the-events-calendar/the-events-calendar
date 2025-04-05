<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Tests\Traits\With_Uopz;


class Event_ImageTest extends WPTestCase {
	use With_Post_Remapping;
	use SnapshotAssertions;
	use With_Uopz;

	/**
	 * Placeholder for the generated event.
	 *
	 * @var \WP_Post
	 */
	public $event;

	/**
	 * The filter to use.
	 */
	public $filter = 'tec_events_elementor_widget_image_description_template_data';

	public function setUp(): void {
		parent::setUp();

		// Was trying to avoid post remapping, but it's so much simpler this way...
		$this->event = $this->mock_event( 'events/single/1.json' )->with_thumbnail()->get();

		$this->set_fn_return( 'get_the_ID', $this->event->ID );
	}

	public function tearDown() {
		parent::_tearDown();
	}

	public function normalize_render( $render ) {
		// Upload dates will be an issue, so let's set them.
		$today   = date( 'Y\/m' );
		$cleaned = str_replace( $today, '1971/07', $render );
		// We don't care about the image filename, but it will change on each test run. Let's set them.
		$cleaned = preg_replace( '/image-(\d+)/m', 'image-1', $cleaned );

		return $cleaned;
	}

	/**
	 * Test render with html filtered.
	 *
	 * @skip The regex is not working as expected.
	 */
	public function test_render_image() {
		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[ 'image_size' => 'large' ]
		);

		$widget = new Event_Image();
		$render = $widget->get_output();

		$cleaned = $this->normalize_render( $render );

		// Ensure the HTML is as expected.
		$this->assertMatchesHtmlSnapshot( $cleaned );
	}

	/**
	 * Test render with html filtered.
	 *
	 * @skip The regex is not working as expected.
	 */
	public function test_render_image_w_hover() {
		$this->set_class_fn_return(
			'Elementor\Controls_Stack',
			'get_settings_for_display',
			[
				'image_size'      => 'large',
				'hover_animation' => 'zoom-in',
			]
		);

		$widget = new Event_Image();
		$render = $widget->get_output();

		$cleaned = $this->normalize_render( $render );

		// Ensure the HTML is as expected.
		$this->assertMatchesHtmlSnapshot( $cleaned );

		// Ensure the class has been added.
		$this->assertContains( 'elementor-animation-zoom-in', $cleaned );
	}
}
