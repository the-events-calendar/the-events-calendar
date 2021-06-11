<?php

namespace Tribe\Events\Integrations\Hello_Elementor;

class TemplatesTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should be instantiatable
	 *
	 * @test
	 */
	public function should_be_instantiatable() {
		$this->assertInstanceOf( Templates::class, new Templates() );
	}

	public function unhandled_locations_provider() {
		return [
			'empty'   => [ '' ],
			'single'  => [ 'single' ],
			'foo'     => [ 'foo' ],
			'archive' => [ 'archive' ],
		];
	}

	/**
	 * It should not redirect unhandled locations
	 *
	 * @test
	 * @dataProvider unhandled_locations_provider
	 */
	public function should_not_redirect_unhandled_locations( $template ) {
		$templates = new Templates();

		$this->assertFalse( $templates->theme_do_location( $template ) );
	}

	public function handled_template_and_view_slugs_provider() {
		return [
			'archive for all'   => [ 'archive', 'all', ],
			'archive for list'  => [ 'archive', 'list' ],
			'archive for month' => [ 'archive', 'month' ],
			'archive for day'   => [ 'archive', 'day' ],
		];
	}

	/**
	 * It should correctly redirect handled templates and views
	 *
	 * @test
	 * @dataProvider handled_template_and_view_slugs_provider
	 */
	public function should_correctly_redirect_handled_templates_and_views( $template, $view_slug ) {
		$templates = new Templates();
		tribe_context()->alter( [ 'view' => $view_slug ] )->dangerously_set_global_context();
		add_filter( 'tribe_events_views_v2_elementor_theme_do_location', function ( $redirected ) {
			$this->assertEquals( 'template-parts/single', $redirected );

			// Let's avoid actual inclusions!
			return false;
		} );

		$this->assertFalse(
			$templates->theme_do_location( $template ),
			'Since the template part is nulled, the code should return false.'
		);
	}
}
