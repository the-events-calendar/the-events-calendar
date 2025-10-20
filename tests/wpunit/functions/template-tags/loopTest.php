<?php

namespace TEC\Test\functions\template_tags;

use Codeception\TestCase\WPTestCase;
use Generator;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Context;
use Tribe\Events\Views\V2\Manager;

class loopTest extends WPTestCase {

	use With_Uopz;

	/**
	 * Holds the original context to restore later.
	 *
	 * @var Tribe__Context|null
	 */
	protected $original_context;

	/**
	 * @before
	 */
	public function before_each(): void {
		// Store the existing context for restoration.
		$this->original_context = tribe_context();
	}

	/**
	 * @after
	 */
	public function after_each(): void {
		// Restore the original global context after each test.
		if ( $this->original_context instanceof Tribe__Context ) {
			$this->original_context->dangerously_set_global_context();
		}
	}

	/**
	 * Provides various view slugs and expected results.
	 *
	 * @return Generator
	 */
	public function view_slug_provider(): Generator {
		$views_manager    = tribe( Manager::class );
		$registered_views = array_keys( (array) $views_manager->get_registered_views() );

		// Test all registered views — should be valid.
		foreach ( $registered_views as $slug ) {
			yield "{$slug} view is valid" => [ $slug, true ];
		}

		// Also test invalid / edge cases.
		yield 'default is not valid' => [ 'default', false ];
		yield 'empty is not valid' => [ '', false ];
		yield 'unknown is not valid' => [ 'foobar', false ];
	}

	/**
	 * @test
	 * @dataProvider view_slug_provider
	 */
	public function it_returns_expected_result_for_each_view( string $view_slug, bool $expected ): void {
		$context = tribe_context()->alter( [ 'view' => $view_slug ] );

		$views_manager = tribe( Manager::class );
		$this->assertInstanceOf( Manager::class, $views_manager );

		$result = tec_is_valid_view( $context );

		$this->assertSame(
			$expected,
			$result,
			sprintf( 'Expected %s for view "%s".', var_export( $expected, true ), $view_slug )
		);
	}
}
