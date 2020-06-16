<?php

namespace Tribe\Events\Views\V2;

class Template_BootstrapTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Template_Bootstrap::class, $sut );
	}

	private function make_instance() {
		return new Template_Bootstrap( new Manager() );
	}

	public function base_template_options() {
		return [
			'invalid'       => [
				'foo',
				'foo',
			],
			'numeric'       => [
				2,
				2,
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
				'page',
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

	/**
	 * It should not load if not main event query
	 *
	 * @test
	 */
	public function should_not_load_if_not_event_query() {
		// Replace the main query with one we control.
		global $wp_the_query, $wp_query;
		$wp_the_query = new \WP_Query();
		$wp_query     = $wp_the_query;
		// Let's make sure this is NOT an event query.
		unset( $wp_the_query->tribe_is_event_query );

		$bootstrap = $this->make_instance();

		$this->assertFalse( $bootstrap->should_load() );
		$this->assertFalse( $bootstrap->should_load( $wp_the_query ) );
	}

	/**
	 * It should load on event main query
	 *
	 * @test
	 */
	public function should_load_on_event_main_query() {
		// Replace the main query with one we control.
		global $wp_the_query, $wp_query;
		$wp_the_query = new \WP_Query();
		$wp_query     = $wp_the_query;
		// Let's make sure this is an event query and flag it as such.
		$wp_the_query->tribe_is_event_query = true;

		$bootstrap = $this->make_instance();

		$this->assertTrue( $bootstrap->should_load() );
		$this->assertTrue( $bootstrap->should_load( $wp_the_query ) );
	}

	/**
	 * It should not load if the main query is not a query
	 *
	 * @test
	 */
	public function should_not_load_if_the_main_query_is_not_a_query() {
		// Replace the main query with one we control.
		global $wp_the_query, $wp_query;
		$wp_the_query = new \stdClass();
		$wp_query     = $wp_the_query;
		// Let's make sure this is an event query and flag it as such.
		$wp_the_query->tribe_is_event_query = true;

		$bootstrap = $this->make_instance();

		$this->assertFalse( $bootstrap->should_load() );
		$this->assertFalse( $bootstrap->should_load( $wp_the_query ) );
	}

	/**
	 * It should allow preventing the template bootstrap from loading w/ filter
	 *
	 * @test
	 */
	public function should_allow_preventing_the_template_bootstrap_from_loading_w_filter() {
		// Replace the main query with one we control.
		global $wp_the_query, $wp_query;
		$wp_the_query = new \WP_Query();
		$wp_query     = $wp_the_query;
		// Let's make sure this is an event query and flag it as such.
		$wp_the_query->tribe_is_event_query = true;

		$bootstrap = $this->make_instance();

		add_filter( 'tribe_events_views_v2_bootstrap_pre_should_load', '__return_false' );

		$this->assertFalse( $bootstrap->should_load() );
		$this->assertFalse( $bootstrap->should_load( $wp_the_query ) );

		// A filter switch mid-request should NOT run into a cached value.
		add_filter( 'tribe_events_views_v2_bootstrap_pre_should_load', '__return_true' );

		$this->assertTrue( $bootstrap->should_load() );
		$this->assertTrue( $bootstrap->should_load( $wp_the_query ) );
	}

	/**
	 * It should allow forcing the template to load w/ a filter
	 *
	 * @test
	 */
	public function should_allow_forcing_the_template_to_load_w_a_filter() {
		// Replace the main query with one we control.
		global $wp_the_query, $wp_query;
		$wp_the_query = new \WP_Query();
		$wp_query     = $wp_the_query;
		// Let's not flag this query as an event one, but let's assume we know what we are doing.
		$wp_the_query->tribe_is_event_query = true;

		$bootstrap = $this->make_instance();

		// And then we force the template bootstrap to load.
		add_filter( 'tribe_events_views_v2_bootstrap_pre_should_load', '__return_true' );

		$this->assertTrue( $bootstrap->should_load() );
		$this->assertTrue( $bootstrap->should_load( $wp_the_query ) );
	}

	/**
	 * It should not filter the template include when main query is not event query
	 *
	 * @test
	 */
	public function should_not_filter_the_template_include_when_main_query_is_not_event_query() {
		// Replace the main query with one we control.
		global $wp_the_query, $wp_query;
		$wp_the_query = new \WP_Query();
		$wp_query     = $wp_the_query;
		// Let's not flag this query as an event one, but let's assume we know what we are doing.
		unset( $wp_the_query->tribe_is_event_query );

		$bootstrap = $this->make_instance();

		$this->assertEquals( 'foo/bar.php', $bootstrap->filter_template_include( 'foo/bar.php' ) );
	}

	/**
	 * It should not filter a 404 template
	 *
	 * @test
	 */
	public function should_not_filter_a_404_template() {
		// Replace the main query with one we control.
		global $wp_the_query, $wp_query;
		$wp_the_query = new \WP_Query();
		$wp_query     = $wp_the_query;
		// This is an event query, but a 404 one.
		$wp_the_query->tribe_is_event_query = true;
		$wp_the_query->is_404               = true;

		$bootstrap = $this->make_instance();

		$this->assertEquals( 'foo/bar.php', $bootstrap->filter_template_include( 'foo/bar.php' ) );
	}

	public function filter_template_include_data_set() {
		{
			$event_template = ( new Template( $this->makeEmpty( View_Interface::class ) ) )
				->get_template_file( 'default-template' );

			// This data provider will run before WordPress loads, so we'll resolve the value later.
			$template_callbacks = [
				'page'     => static function () {
					return get_page_template();
				},
				'singular' => static function () {
					return get_singular_template();
				},
				'index'    => static function () {
					return get_index_template();
				},
				'custom'   => static function () {
					return locate_template( 'custom' );
				}
			];

			$all_templates = [ 'page', 'singular', 'index', 'custom' ];

			foreach ( [ 'day', 'month', 'list', 'default' ] as $view ) {
				foreach ( $all_templates as $theme_template ) {
					$available_templates = array_slice(
						$all_templates,
						array_search( $theme_template, $all_templates )
					);
					$templates           = implode( ', ', $available_templates );

					yield 'event template empty; looking at ' . $view . ' view; theme has ' . $templates . ' templates' => [
						null,
						$available_templates,
						$view,
						$event_template
					];

					yield 'event template empty string; looking at ' . $view . ' view; theme has ' . $templates . ' templates' => [
						'',
						$available_templates,
						$view,
						$event_template
					];

					yield 'event template is event; looking at ' . $view . ' view; theme has ' . $templates . ' templates' => [
						'event',
						$available_templates,
						$view,
						$event_template
					];

					yield 'event template is page; looking at ' . $view . ' view; theme has ' . $templates . ' templates' => [
						'page',
						$available_templates,
						$view,
						$template_callbacks[ $theme_template ]
					];
				}
			}
		};
	}

	/**
	 * @dataProvider filter_template_include_data_set
	 */
	public function test_filter_template_include(
		$event_template,
		$available_theme_templates,
		$view,
		$expected_template
	) {
		// Replace the main query with one we control.
		global $wp_the_query, $wp_query;
		$wp_the_query = new \WP_Query();
		$wp_query     = $wp_the_query;
		// This is an event query, but a 404 one.
		$wp_the_query->tribe_is_event_query = true;
		tribe_context()->alter( [
			'view' => $view
		] )->dangerously_set_global_context();
		tribe_update_option( 'tribeEventsTemplate', $event_template );
		// Since we cannot depend on a theme to have the templates we need, we replace the hierarchy completely.
		$this->setup_fake_theme_templates( $available_theme_templates );
		// Late resolution of values we could not build before WordPress loaded.
		if ( is_callable( $expected_template ) ) {
			$expected_template = $expected_template();
		}

		$bootstrap = $this->make_instance();

		$this->assertEquals( $expected_template, $bootstrap->filter_template_include( 'foo/bar.php' ) );
	}

	/**
	 * Sets up filters to simulate a theme with a specific set of templates available.
	 *
	 * @param array<string> $whitelist The list of templates available in the theme.
	 */
	protected function setup_fake_theme_templates( array $whitelist = [] ) {
		if ( in_array( 'page', $whitelist ) ) {
			// Filter the `page` template, to cover `get_page_template`.
			add_filter( 'page_template', static function () {
				return codecept_data_dir( 'templates/page.php' );
			} );
		} else {
			add_filter( 'page_template', static function () {
				return '';
			} );
		}

		if ( in_array( 'singular', $whitelist ) ) {
			// Filter the `singular` template, to cover `get_singular_template`.
			add_filter( 'singular_template', static function () {
				return codecept_data_dir( 'templates/singular.php' );
			} );
		} else {
			add_filter( 'singular_template', static function () {
				return '';
			} );
		}

		if ( in_array( 'index', $whitelist ) ) {
			// Filter the `index` template, to cover `get_index_template`.
			add_filter( 'index_template', static function () {
				return codecept_data_dir( 'templates/index.php' );
			} );
		} else {
			add_filter( 'index_template', static function () {
				return '';
			} );
		}

		if ( in_array( 'custom', $whitelist ) ) {
			// Filter the `custom` template, to cover `get_custom_template`.
			add_filter( 'custom_template', static function () {
				return codecept_data_dir( 'templates/custom.php' );
			} );
		} else {
			add_filter( 'custom_template', static function () {
				return '';
			} );
		}
	}
}
