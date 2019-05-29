<?php
namespace Tribe\Events\Views\V2\Partials\Month;

use Tribe\Events\Views\V2\Partials\TestCase;

class Day_MarkerTest extends TestCase
{

	protected $partial_path = 'month/day-marker';

	/**
	 * Test static render
	 * @todo remove this static HTML test once the partial is dynamic.
	 */
	public function test_static_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
				'foo' => 23,
				'bar' => 89,
			]
		) );
	}
}
