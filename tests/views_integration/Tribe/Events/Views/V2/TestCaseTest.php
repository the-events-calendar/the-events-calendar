<?php

namespace Tribe\Events\Views\V2;

require_once codecept_data_dir( 'Views/V2/classes/Test_View.php' );
require_once codecept_data_dir( 'Views/V2/classes/Test_Context_View.php' );

class TestCaseTest extends TestCase {

	/**
	 * It should throw if building context for non registered view class
	 *
	 * @test
	 */
	public function should_throw_if_building_context_for_non_registered_view_class() {
		add_filter( 'tribe_events_views', function () {
			return [];
		} );

		$this->expectException( \RuntimeException::class );

		$context = $this->given_a_main_query_request()
		                ->for_view( Test_View::class );
	}

	/**
	 * It should be able to setup a main query context
	 *
	 * @test
	 */
	public function should_be_able_to_setup_a_main_query_context() {
		add_filter( 'tribe_events_views', function () {
			return [ 'test' => Test_View::class ];
		} );
		$global_context = tribe_context()->to_array();

		$this->given_a_main_query_request()
		     ->for_view( Test_View::class )
		     ->with_args( [
			     'view_data' => [
				     'day' => '2019-03-12',
			     ],
		     ] )
		     ->alter_global_context();

		$actual = tribe_context()->to_array();
		$this->assertEquals( 'test', $actual['view'] );
		$this->assertEquals( true, $actual['is_main_query'] );
		$this->assertEquals( [
			'day' => '2019-03-12',
		], $actual['view_data'] );
	}

	/**
	 * It should allow making a simple snapshot assertion
	 *
	 * @test
	 */
	public function should_allow_making_a_simple_snapshot_assertion() {
		add_filter( 'tribe_events_views', function () {
			return [ 'test' => Test_Context_View::class ];
		} );

		$this->given_a_main_query_request()
		     ->for_view( Test_Context_View::class )
		     ->with_args( [
			     'view_data' => [
				     'venue'     => 23,
				     'organizer' => [ 89, 2389 ],
				     'featured'  => false,
				     'color'     => [ 'yellow', 'blue' ],
			     ],
		     ] )
		     ->alter_global_context();

		$this->assert_view_snapshot( View::make( 'test' ) );
	}
}