<?php
namespace Tribe\Events\Views\V2;

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

	public function data_themes_supported() {
		return [
			'avada_is_supported' => [ 'avada' ],
			'divi_is_supported' => [ 'divi' ],
			'enfold_is_supported' => [ 'enfold' ],
			'genesis_is_supported' => [ 'genesis' ],
			'twentyseventeen_is_supported' => [ 'twentyseventeen' ],
			'twentynineteen_is_supported' => [ 'twentynineteen' ],
		];
	}

	/**
	 * @test
	 */
	public function should_not_need_compatibility_for_non_supported_themes() {
		update_option( 'template', 'invalid-value-for-theme' );
		update_option( 'stylesheet', 'invalid-value-for-theme' );

		$is_compatibility_required = $this->make_instance()->is_compatibility_required();

		$this->assertEquals( $is_compatibility_required, false );
	}

	/**
	 * @test
	 * @dataProvider data_themes_supported
	 */
	public function should_need_compatibility_for_supported_themes( $input ) {
		update_option( 'stylesheet', $input );
		update_option( 'template', $input );

		$is_compatibility_required = $this->make_instance()->is_compatibility_required();

		$this->assertEquals( $is_compatibility_required, true );
	}

	/**
	 * @test
	 * @dataProvider data_themes_supported
	 */
	public function should_not_need_compatibility_for_supported_themes_in_template_but_missing_stylesheet( $input ) {
		update_option( 'template', $input );
		delete_option( 'stylesheet' );

		$is_compatibility_required = $this->make_instance()->is_compatibility_required();

		$this->assertEquals( $is_compatibility_required, false );
	}

	/**
	 * @test
	 * @dataProvider data_themes_supported
	 */
	public function should_not_need_compatibility_for_supported_themes_in_stylesheet_but_missing_template( $input ) {
		delete_option( 'template' );
		update_option( 'stylesheet', $input );

		$is_compatibility_required = $this->make_instance()->is_compatibility_required();

		$this->assertEquals( $is_compatibility_required, false );
	}

	/**
	 * @test
	 */
	public function should_need_compatibility_for_supported_themes_in_template_but_not_in_stylesheet() {
		update_option( 'template', 'avada' );
		update_option( 'stylesheet', 'invalid-value-for-theme' );

		$is_compatibility_required = $this->make_instance()->is_compatibility_required();

		$this->assertEquals( $is_compatibility_required, true );
	}

	/**
	 * @test
	 */
	public function should_need_compatibility_for_supported_themes_in_stylesheet_but_not_in_template() {
		update_option( 'template', 'invalid-value-for-theme' );
		update_option( 'stylesheet', 'avada' );

		$is_compatibility_required = $this->make_instance()->is_compatibility_required();

		$this->assertEquals( $is_compatibility_required, true );
	}

	/**
	 * @test
	 */
	public function should_allow_filtering_of_compatible_themes() {
		$theme = 'filtered-theme';
		update_option( 'template', $theme );
		update_option( 'stylesheet', $theme );

		add_filter(
			'tribe_events_views_v2_theme_compatibility_registred',
			function( $themes ) use ( $theme ) {
				$themes[] = $theme;
				return $theme;
			}
		);

		$is_compatibility_required = $this->make_instance()->is_compatibility_required();

		$this->assertEquals( $is_compatibility_required, true );
	}

	/**
	 * @test
	 */
	public function should_return_empty_classes_template_without_stylesheet() {
		update_option( 'template', 'foo-theme' );
		delete_option( 'stylesheet' );

		$classes = $this->make_instance()->get_body_classes();

		$this->assertEmpty( $classes );
	}

	/**
	 * @test
	 */
	public function should_return_empty_classes_stylesheet_without_template() {
		delete_option( 'template' );
		update_option( 'stylesheet', 'foo-theme' );

		$classes = $this->make_instance()->get_body_classes();

		$this->assertEmpty( $classes );
	}

	public function data_themes_and_classes() {
		return [
			'theme_on_template_and_stylesheet' => [
				'foo-theme',
				'foo-theme',
				[ 'tribe-theme-foo-theme' ],
			],
			'theme_on_template_and_child_on_stylesheet' => [
				'foo-theme',
				'double-foo-theme',
				[ 'tribe-theme-foo-theme', 'tribe-theme-child-double-foo-theme' ],
			],
		];
	}

	/**
	 * @test
	 * @dataProvider data_themes_and_classes
	 */
	public function should_have_expected_classes_depending_on_template_and_stylesheet( $template, $stylesheet, $classes_expected ) {
		update_option( 'template', $template );
		update_option( 'stylesheet', $stylesheet );

		$classes = $this->make_instance()->get_body_classes();

		$this->assertEquals( $classes, $classes_expected );
	}
}