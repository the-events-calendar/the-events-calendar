<?php
namespace Tribe\Events\Views\V2;

class TemplateBootstrapTest extends \Codeception\TestCase\WPTestCase {
	private function make_instance() {
		return new Template_Bootstrap();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Template_Bootstrap::class, $sut );
	}

	public function base_template_options() {
		return [
			'invalid' => [
				'foo',
				'event',
			],
			'numeric' => [
				2,
				'event',
			],
			'default' => [
				'default',
				'page',
			],
			'empty_string' => [
				'',
				'event',
			],
			'numeric_zero' => [
				0,
				'event',
			],
			'null' => [
				null,
				'event',
			],
			'boolean_false' => [
				false,
				'event',
			],
			'boolean_true' => [
				false,
				'event',
			],
			'slug_event' => [
				'event',
				'event',
			],
			'slug_page' => [
				'page',
				'event',
			],
		];
	}

	/**
	 * It should only allow permitted values on base template option
	 *
	 * @test
	 * @dataProvider base_template_options
	 */
	public function should_only_allow_permitted_values_on_base_template_option( $input, $expected ) {
		tribe_update_option( 'tribeEventsTemplate', $input );

		$option_value = $this->make_instance()->get_template_setting();

		$this->assertEquals( $option_value, $expected );
	}

	/**
	 * It should return template event instance
	 *
	 * @test
	 */
	public function it_should_return_template_event_instance() {
		tribe_update_option( 'tribeEventsTemplate', 'event' );

		$instance = $this->make_instance()->get_template_object();

		$this->assertInstanceOf( Template\Event::class, $instance );
	}

	/**
	 * It should return template page instance
	 *
	 * @test
	 */
	public function it_should_return_template_page_instance() {
		tribe_update_option( 'tribeEventsTemplate', 'default' );

		$instance = $this->make_instance()->get_template_object();

		$this->assertInstanceOf( Template\Page::class, $instance );
	}

	public function query_args_to_load() {
		return [
			'invalid' => [
				'foo',
				false,
			],
			'post_type_eq_tribe_events' => [
				[ 'post_type' => 'tribe_events' ],
				true,
			],
			'post_type_contains_tribe_events' => [
				[ 'post_type' => [ 'tribe_events', 'invalid_post_type' ] ],
				true,
			],
			'post_type_not_tribe_events' => [
				[ 'post_type' => 'post' ],
				false,
			],
		];
	}

	/**
	 * It should load only on correct wp query
	 *
	 * @test
	 * @dataProvider query_args_to_load
	 */
	public function it_should_load_only_on_correct_wp_query( $query_args, $expected ) {
		$query = new \WP_Query( $query_args );

		$should_load = $this->make_instance()->should_load( $query );

		$this->assertEquals( $should_load, $expected );
	}

	public function invalid_queries() {
		return [
			'string' => [
				'foo',
			],
			'numeric' => [
				2,
			],
			'boolean_false' => [
				false,
			],
			'boolean_true' => [
				true,
			],
			'stdObject' => [
				(object) [],
			],
			'array' => [
				[],
			],
		];
	}

	/**
	 * It should use global query on invalid query
	 *
	 * @test
	 * @dataProvider invalid_queries
	 */
	public function it_should_use_global_query_on_invalid_query( $invalid_query ) {
		global $wp_query;
		$wp_query = new \WP_Query( [ 'post_type' => 'tribe_events' ] );

		$should_load = $this->make_instance()->should_load( $invalid_query );

		$this->assertTrue( $should_load );
	}

}