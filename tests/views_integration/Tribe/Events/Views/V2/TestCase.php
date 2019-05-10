<?php
/**
 * The base test case to test v2 Views.
 *
 * It provides utility methods and assertions useful and required in Views testing.
 *
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2;

use Codeception\TestCase\WPTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Tribe__Context as Context;

/**
 * Class TestCase
 *
 * @package Tribe\Events\Views\V2
 */
abstract class TestCase extends WPTestCase {

	use MatchesSnapshots;

	/**
	 * The current Context Mocker instance.
	 *
	 * @var \Tribe\Events\Views\V2\ContextMocker
	 */
	protected $context_mocker;

	/**
	 * The state of the global Context object before the test method ran.
	 *
	 * @var array
	 */
	protected $global_context_before_test;

	/**
	 * After the test method ran try and restore the global context to its previous state and make sure that
	 * is the case.
	 *
	 * @since TBD
	 */
	public function tearDown() {
		// Get the values we had before the test method.
		$reset_values = $this->global_context_before_test;
		// Get the values we have now.
		$locations = tribe_context()->get_locations();
		// Reset any value we had before to its previous value, set any other value to `NOT_FOUND`.
		$reset_values = array_merge(
			array_combine(
				array_keys( $locations ),
				array_fill( 0, count( $locations ), Context::NOT_FOUND )
			),
			$reset_values
		);
		// Make sure the reset was successful.
		tribe_context()->alter( $reset_values )->dangerously_set_global_context();
		$this->assertEqualSets( $this->global_context_before_test, tribe_context()->to_array() );

		parent::tearDown();
	}

	/**
	 * Before each test method take a snapshot of the global context state to make sure it's restored
	 * as it was after each test.
	 *
	 * We do this here, before each test method, as `setupBeforeClass` would be too early.
	 */
	public function setUp() {
		parent::setUp();
		// Always set the `is_main_query` value to `false` to have a clean starting fixture.
		tribe_context()->alter( [ 'is_main_query' => false ] )->dangerously_set_global_context( [ 'is_main_query' ] );
		$this->global_context_before_test = tribe_context()->to_array();
	}

	/**
	 * Starts the chain to replace the global context using the Context Mocker.
	 *
	 * @return \Tribe\Events\Views\V2\ContextMocker The context mocker instance.
	 */
	protected function given_a_main_query_request(): ContextMocker {
		$context_mocker = new ContextMocker();
		$context_mocker->set( 'is_main_query', true );
		$this->context_mocker = $context_mocker;

		return $context_mocker;
	}

	/**
	 * Asserts a view current HTML output matches a stored HTML snapshot.
	 *
	 * @param \Tribe\Events\Views\V2\View_Interface $view The view instance.
	 */
	protected function assert_view_snapshot( View_Interface $view ) {
		if ( null !== $this->context_mocker && ! $this->context_mocker->did_mock() ) {
			// Let's alter the global context now.
			$this->context_mocker->alter_global_context();
		}

		$this->assertMatchesSnapshot( $view->get_html() );
	}
}