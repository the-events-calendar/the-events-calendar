<?php
namespace Tribe\Events\Views\V2;

use Tribe\Utils\Body_Classes;

class ThemeCompatibilityTest extends \Codeception\TestCase\WPTestCase {
	private function make_instance() {
		return new Theme_Compatibility();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Theme_Compatibility::class, $sut );
	}

	/**
	 * @test
	 */
	public function should_not_need_compatibility_for_non_supported_themes() {
		add_filter( 'stylesheet', function( $stylesheet ) {
			return 'invalid-value-for-theme';
		 } );

		$is_compatibility_required = $this->make_instance()::is_compatibility_required();

		$this->assertFalse( tribe_is_truthy( $is_compatibility_required ) );
	}

	/**
	 * @test
	 */
	public function should_need_compatibility_for_supported_themes() {
		// Get a list of installed themes. Prevents trying to test ones that are invalid.
		$themes = array_keys( wp_get_themes() );
		// Get list of themes we "support" with compatibility fixes.
		$supported = Theme_Compatibility::get_registered_themes();
		// We'll test the intersection of the two lists.
		$testing = array_intersect( $themes, $supported );

		foreach ( $testing as $theme ) {
			update_option( 'template', $theme );
			add_filter( 'stylesheet', function( $stylesheet ) use ( $theme ) {
				return $theme;
			 } );

			$is_compatibility_required = $this->make_instance()::is_compatibility_required();

			$this->assertTrue( tribe_is_truthy( $is_compatibility_required ) );
		}


	}

	/**
	 * @test
	 */
	public function should_add_classes_to_queue() {
		$body_classes = tribe( Body_Classes::class );
		add_filter( 'tribe_body_class_should_add_to_queue', '__return_true' );
		$this->make_instance()->add_body_classes();

		// Have to add the page template class.
		$intended_classes = array_merge(
			[ 'tribe-events-page-template' ],
			$this->make_instance()->get_body_classes()
		);

		$actual_classes = $body_classes->get_class_names();

		$this->assertEquals( $intended_classes, $actual_classes );
	}
}
