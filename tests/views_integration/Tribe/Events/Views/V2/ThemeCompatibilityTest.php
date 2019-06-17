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

	public function themes_supported_data_set() {
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
	 * @dataProvider themes_supported_data_set
	 */
	public function should_need_compatibility_for_supported_themes( $input ) {
		update_option( 'stylesheet', $input );
		update_option( 'template', $input );

		$is_compatibility_required = $this->make_instance()->is_compatibility_required();

		$this->assertEquals( $is_compatibility_required, true );
	}

	/**
	 * @test
	 * @dataProvider themes_supported_data_set
	 */
	public function should_not_need_compatibility_for_supported_themes_in_template_but_missing_stylesheet( $input ) {
		update_option( 'template', $input );
		delete_option( 'stylesheet' );

		$is_compatibility_required = $this->make_instance()->is_compatibility_required();

		$this->assertEquals( $is_compatibility_required, false );
	}

	/**
	 * @test
	 * @dataProvider themes_supported_data_set
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
		$theme = 'valid-theme';
		update_option( 'template', $theme );
		update_option( 'stylesheet', 'invalid-value-for-theme' );

		add_filter(
			'tribe_events_views_v2_theme_compatibility_registered',
			static function( $themes ) use ( $theme ) {
				$themes[] = $theme;
				return $themes;
			}
		);

		$is_compatibility_required = $this->make_instance()->is_compatibility_required();

		$this->assertEquals( $is_compatibility_required, true );
	}

	/**
	 * @test
	 */
	public function should_need_compatibility_for_supported_themes_in_stylesheet_and_template() {
		$theme = 'valid-theme';
		update_option( 'template', $theme );
		update_option( 'stylesheet', $theme );

		add_filter(
			'tribe_events_views_v2_theme_compatibility_registered',
			static function( $themes ) use ( $theme ) {
				$themes[] = $theme;
				return $themes;
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

	public function themes_and_classes_data_set() {
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
	 * @dataProvider themes_and_classes_data_set
	 */
	public function should_have_expected_classes_depending_on_template_and_stylesheet( $template, $stylesheet, $classes_expected ) {
		update_option( 'template', $template );
		update_option( 'stylesheet', $stylesheet );

		$classes = $this->make_instance()->get_body_classes();

		$this->assertEquals( $classes, $classes_expected );
	}
}