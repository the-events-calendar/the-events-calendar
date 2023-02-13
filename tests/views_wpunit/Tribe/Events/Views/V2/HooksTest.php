<?php

namespace Tribe\Events\Views\V2;


use Tribe\Tests\Traits\With_Uopz;
use Tribe__Context as Context;

class HooksTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	public function test_filter_redirect_canonical() {
		$this->set_fn_return( 'doing_filter', 'redirect_canonical' );
		$mock_context = new Context();
		$mock_context->set_locations( [
			'tec_post_type' => true,
			'view_request'  => 'month',
		] );
		$this->set_fn_return( 'tribe_context', $mock_context );

		$hooks    = new Hooks( tribe() );
		$filtered = $hooks->filter_redirect_canonical( 'http://example.com/events/month/', 'http://example.com/events/list/' );

		$this->assertEquals( 'http://example.com/events/month/', $filtered );
	}

	public function filter_redirect_canonical_data(): array {
		return [
			'not TEC post type'                           => [
				[
					'tec_post_type' => false,
					'view_request'  => 'month',
				],
				'http://example.com/some-page/',
				'http://example.com/some-page/',
			],
			'embed of TEC post type'                      => [
				[
					'tec_post_type' => true,
					'view_request'  => 'embed',
				],
				'http://example.com/some-page/',
				false
			],
			'single view of Event'                        => [
				[
					'tec_post_type' => true,
					'view_request'  => 'single-event',
				],
				'http://example.com/events/some-event/',
				'http://example.com/events/some-event/',
			],
			'TEC post type by empty view'                 => [
				[
					'tec_post_type' => true,
					'view_request'  => '',
				],
				'http://example.com/some/event/path/',
				'http://example.com/some/event/path/',
			],
			'redirected with tribe_redirected'            => [
				[
					'tec_post_type' => true,
					'view_request'  => 'month',
				],
				'http://example.com/events/list/?tribe_redirected=1',
				'http://example.com/events/list/?tribe_redirected=1',
			],
			'not redirected, eventDisplay match'          => [
				[
					'tec_post_type' => true,
					'view_request'  => 'month',
				],
				'http://example.com/events/month/',
				'http://example.com/events/month/',
			],
			'not redirected, eventDisplay does not match' => [
				[
					'tec_post_type' => true,
					'view_request'  => 'list',
				],
				'http://example.com/events/month/',
				false
			],
		];
	}

	/**
	 * @dataProvider filter_redirect_canonical_data
	 */
	public function test_filter_redirect_canonical_will_not_redirect_embed( array $context, string $redirect_url, $expected ): void {
		// Mock just the month rewreite rules to have `Rewrite::parse_request` work correctly.
		update_option( 'rewrite_rules', [
			'(?:events)/(?:month)/?$'                                                    => 'index.php?post_type=tribe_events&eventDisplay=month',
			'(?:events)/(?:month)/(?:featured)/?$'                                       => 'index.php?post_type=tribe_events&eventDisplay=month&featured=1',
			'(?:events)/(?:month)/(\\d{4}-\\d{2})/?$'                                    => 'index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]',
			'(?:events)/(\\d{4}-\\d{2})/?$'                                              => 'index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]',
			'(?:events)/(\\d{4}-\\d{2})/(?:featured)/?$'                                 => 'index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]&featured=1',
			'(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:month)/?$'                    => 'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month',
			'(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:month)/(?:featured)/?$'       => 'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&featured=1',
			'(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2})/?$'              => 'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&eventDate=$matches[2]',
			'(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2})/(?:featured)/?$' => 'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&eventDate=$matches[2]&featured=1',
			'(?:events)/(?:tag)/([^/]+)/(?:month)/?$'                                    => 'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month',
			'(?:events)/(?:tag)/([^/]+)/(?:month)/(?:featured)/?$'                       => 'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&featured=1',
			'(?:events)/(?:tag)/([^/]+)/(\\d{4}-\\d{2})/?$'                              => 'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&eventDate=$matches[2]',
			'(?:events)/(?:tag)/([^/]+)/(\\d{4}-\\d{2})/(?:featured)/?$'                 => 'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&eventDate=$matches[2]&featured=1',
		] );
		$mock_context = tribe_context()->alter( $context );
		$this->set_fn_return( 'tribe_context', $mock_context );

		$hooks    = new Hooks( tribe() );
		$filtered = $hooks->filter_redirect_canonical( $redirect_url );

		$this->assertEquals( $expected, $filtered );
	}
}
