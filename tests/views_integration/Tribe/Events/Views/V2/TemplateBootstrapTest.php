<?php
namespace Tribe\Events\Views\V2;

class TemplateBootstrapTest extends \Codeception\TestCase\WPTestCase {
	private function make_instance() {
		return new Template_Bootstrap();
	}

	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Template_Bootstrap::class, $sut );
	}

	public function base_template_options() {
		return [
			'invalid'       => [
				'foo',
				'event',
			],
			'numeric'       => [
				2,
				'event',
			],
			'default'       => [
				'default',
				'page',
			],
			'empty_string'  => [
				'',
				'event',
			],
			'numeric_zero'  => [
				0,
				'event',
			],
			'null'          => [
				null,
				'event',
			],
			'boolean_false' => [
				false,
				'event',
			],
			'boolean_true'  => [
				false,
				'event',
			],
			'slug_event'    => [
				'event',
				'event',
			],
			'slug_page'     => [
				'page',
				'event',
			],
		];
	}

	/**
	 * @test
	 * @dataProvider base_template_options
	 */
	public function should_only_allow_permitted_values_on_base_template_option( $input, $expected ) {
		tribe_update_option( 'tribeEventsTemplate', $input );

		$option_value = $this->make_instance()->get_template_setting();

		$this->assertEquals( $option_value, $expected );
	}

	/**
	 * @test
	 */
	public function it_should_return_template_event_instance() {
		tribe_update_option( 'tribeEventsTemplate', 'event' );

		$instance = $this->make_instance()->get_template_object();

		$this->assertInstanceOf( Template\Event::class, $instance );
	}

	/**
	 * @test
	 */
	public function it_should_return_template_page_instance() {
		tribe_update_option( 'tribeEventsTemplate', 'default' );

		$instance = $this->make_instance()->get_template_object();

		$this->assertInstanceOf( Template\Page::class, $instance );
	}

	public function query_args_to_load() {
		return [
			'not_main_event_query'       => [
				true,
				false,
				false,
			],
			'main_event_query'           => [
				true,
				true,
				true,
			],
			'main_query_not_event_query' => [
				false,
				true,
				false,
			],
			'not_main_not_event_query'   => [
				false,
				false,
				false,
			],
		];
	}

	/**
	 * @test
	 * @dataProvider query_args_to_load
	 */
	public function it_should_load_only_on_correct_wp_query( $is_event_query, $is_main_query, $expected ) {
		global $wp_query;
		$query                       = $this->make(
			\WP_Query::class,
			[
				'is_main_query' => $is_main_query,
			]
		);
		$query->tribe_is_event_query = $is_event_query;
		$wp_query                    = $query;

		$should_load = $this->make_instance()->should_load( $query );

		$this->assertEquals( $should_load, $expected );
	}

	/**
	 * It should not load if query is not main query
	 *
	 * @test
	 */
	public function should_not_load_if_query_is_not_main_query() {
		$query = $this->make(
			\WP_Query::class,
			[
				'is_main_query' => false,
			]
		);

		$this->assertFalse( $this->make_instance()->should_load( $query ) );
	}

	public function invalid_queries() {
		return [
			'string'        => [
				'foo',
			],
			'numeric'       => [
				2,
			],
			'boolean_false' => [
				false,
			],
			'boolean_true'  => [
				true,
			],
			'stdObject'     => [
				(object) [],
			],
			'array'         => [
				[],
			],
		];
	}

	/**
	 * @test
	 * @dataProvider invalid_queries
	 */
	public function it_should_use_global_query_on_invalid_query( $invalid_query ) {
		global $wp_query;
		$called                         = false;
		$wp_query                       = $this->make(
			\WP_Query::class,
			[
				'is_main_query' => static function () use ( &$called ) {
					return $called = true;
				},
			]
		);
		$wp_query->tribe_is_event_query = true;

		$should_load = $this->make_instance()->should_load( $invalid_query );

		$this->assertTrue( $should_load );
		$this->assertTrue( $called );
	}
}
