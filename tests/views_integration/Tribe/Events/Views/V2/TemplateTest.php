<?php

namespace Tribe\Events\Views\V2;

class TemplateTest extends \Codeception\TestCase\WPTestCase {

	protected $slug = 'test';

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Template::class, $sut );
	}

	/**
	 * @return Template
	 */
	private function make_instance() {
		return new Template( $this->slug );
	}

	/**
	 * It should allow setting a number of values at the same time
	 *
	 * @test
	 */
	public function should_allow_setting_a_number_of_values_at_the_same_time() {
		$template = $this->make_instance();

		$template->set_values( [
			'twenty-three' => '23',
			'eighty-nine'  => 89,
			'an_array'     => [ 'key' => 2389 ],
			'an_object'    => (object) [ 'key' => 89 ],
			'a_null_value' => null,
		] );

		$this->assertEquals( '23', $template->get( 'twenty-three' ) );
		$this->assertEquals( 89, $template->get( 'eighty-nine' ) );
		$this->assertEquals( [ 'key' => 2389 ], $template->get( 'an_array' ) );
		$this->assertEquals( (object) [ 'key' => 89 ], $template->get( 'an_object' ) );
		$this->assertEquals( null, $template->get( 'a_null_value' ) );
	}

	/**
	 * It should allow setting contextual values without overriding the primary values
	 *
	 * @test
	 */
	public function should_allow_setting_contextual_values_without_overriding_the_primary_values() {
		$template      = $this->make_instance();
		$global_set    = [
			'twenty-three' => '23',
			'eighty-nine'  => 89,
		];
		$global_values = array_merge( $global_set, [ 'slug' => $this->slug ] );

		$template->set_values( $global_set, false );

		$this->assertEquals( $global_values, $template->get_global_values() );
		$this->assertEquals( [], $template->get_local_values() );
		$this->assertEquals( $global_values, $template->get_values() );

		$local_set = [
			'eighty-nine' => 2389,
			'another_var' => 'another_value',
		];
		$template->set_values( $local_set );

		$this->assertEquals( $global_values, $template->get_global_values() );
		$this->assertEquals( $local_set, $template->get_local_values() );
		$this->assertEquals( array_merge( $global_values, $local_set ), $template->get_values() );
	}
}
