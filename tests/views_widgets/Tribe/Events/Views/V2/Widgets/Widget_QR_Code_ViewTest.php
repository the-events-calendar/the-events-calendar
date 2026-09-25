<?php
namespace Tribe\Events\Views\V2\Views\Widgets;

use Generator;
use TEC\Events\QR\Controller as QR_Controller;
use Tribe\Events\Test\Testcases\TecViewTestCase;
use Tribe\Events\Views\V2\View;

class Widget_QR_Code_ViewTest extends TecViewTestCase {

	/**
	 * A shortcode tag registered only for these tests, whose output marks that the
	 * shortcode parser ran against a value it should never have parsed.
	 *
	 * @var string
	 */
	private const MARKER_TAG = 'tec_test_qr_marker';

	/**
	 * The string the marker shortcode emits when it executes.
	 *
	 * @var string
	 */
	private const MARKER_OUTPUT = 'tec-test-qr-marker-ran';

	public function setUp() {
		parent::setUp();
		\Tribe__Rewrite::instance()->setup();

		add_filter(
			'tribe_events_views',
			static function ( array $views ) {
				$views[ Widget_QR_Code_View::get_view_slug() ] = Widget_QR_Code_View::class;

				return $views;
			}
		);

		tribe( QR_Controller::class )->register();

		add_shortcode( self::MARKER_TAG, static fn() => self::MARKER_OUTPUT );

		remove_filter( 'post_class', 'twenty_twenty_one_post_classes', 10 );
		remove_filter( 'post_class', 'twentynineteen_post_classes', 10 );
		add_filter( 'tribe_events_views_v2_theme_compatibility_registered', '__return_empty_array' );
	}

	public function tearDown() {
		remove_shortcode( self::MARKER_TAG );

		parent::tearDown();
	}

	/**
	 * Each context key the QR widget view reads is attacker-controllable through the Views V2
	 * request routes. A value carrying shortcode delimiters must never be handed to the shortcode
	 * parser, or a closing bracket terminates the intended shortcode and a second one runs.
	 *
	 * @return Generator<string,array{0:array<string,string>}> Context overrides keyed by the reached key.
	 */
	public function context_key_data_provider(): Generator {
		$payload = 'x] [' . self::MARKER_TAG . '] [y';

		// Reaches the shortcode `mode` attribute.
		yield 'redirection' => [ [ 'redirection' => $payload ] ];
		// Reaches the `id` attribute when redirection is anything but `next`.
		yield 'event_id' => [ [ 'redirection' => 'current', 'event_id' => $payload ] ];
		// Reaches the `id` attribute when redirection is `next`.
		yield 'series_id' => [ [ 'redirection' => 'next', 'series_id' => $payload ] ];
		// Reaches the `size` attribute.
		yield 'qr_code_size' => [ [ 'qr_code_size' => $payload ] ];
	}

	/**
	 * @test
	 * @dataProvider context_key_data_provider
	 *
	 * @param array<string,string> $overrides Context values that stand in for the request input.
	 */
	public function it_does_not_run_shortcodes_from_context_values( array $overrides ) {
		$context = tribe_context()->alter(
			array_merge(
				[
					'today'      => $this->mock_date_value,
					'now'        => $this->mock_date_value,
					'event_date' => $this->mock_date_value,
				],
				$overrides
			)
		);

		$html = View::make( Widget_QR_Code_View::class, $context )->get_html();

		// The view rendered its container, so an empty string is not masking the assertion below.
		$this->assertStringContainsString( 'tribe-events-widget-events-qr-code', $html );
		$this->assertStringNotContainsString( self::MARKER_OUTPUT, $html );
	}

	/**
	 * @test
	 */
	public function it_renders_the_qr_image_for_a_valid_request() {
		$context = tribe_context()->alter(
			[
				'today'        => $this->mock_date_value,
				'now'          => $this->mock_date_value,
				'event_date'   => $this->mock_date_value,
				'redirection'  => 'current',
				'qr_code_size' => '4',
			]
		);

		$html = View::make( Widget_QR_Code_View::class, $context )->get_html();

		// A valid request renders the QR code as an inline base64 PNG image.
		$this->assertStringContainsString( 'tec-events-qr-code__image', $html );
		$this->assertStringContainsString( 'src="data:image/png;base64,', $html );
	}
}
