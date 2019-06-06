<?php
/**
 * Add theme compatibility things here.
 * @TODO: This is an implementation to set a body class we can use in the common implementation.
 * @TODO: Check this once we move forward with the views structure.
 * @since   4.9.3
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Template_Bootstrap;

class Theme_Compatibility {

	/*
	 * List of themes which have compatibility fixes
	 */
	public $themes_with_compatibility_fixes = [
		'avada',
		'divi',
		'enfold',
		'genesis',
		'twentyseventeen',
		'twentynineteen',
	];


	/**
	 * Register
	 *
	 * @since  4.9.3
	 *
	 *
	 * @return void
	 */
	public function register() {}

	/**
	 * Checks if theme needs a compatibility fix.
	 *
	 * @param string $theme Name of template from WP_Theme->Template, defaults to current active template.
	 *
	 * @since 4.9.3
	 *
	 * @return mixed
	 */
	public function needs_compatibility_fix( $theme = null ) {
		// Defaults to current active theme
		if ( $theme === null ) {
			$theme = get_stylesheet();
		}

		/**
		 * Allows to filter the theme list with compatibility fixes.
		 *
		 * @since  4.9.3
		 *
		 * @param  array  $themes_with_compatibility_fixes A list of themes we provide compatibility for.
		 */
		$theme_compatibility_list = apply_filters( 'tribe_events_views_v2_themes_compatibility_fixes', $this->themes_with_compatibility_fixes );

		return in_array( $theme, $theme_compatibility_list );
	}

	/**
	 * Add the theme to the body class.
	 *
	 * @since 4.9.3
	 *
	 * @return array $classes
	 */
	public function body_class( $classes ) {

		if (
			! tribe( Template_Bootstrap::class )->should_load()
			|| ! $this->needs_compatibility_fix()
		) {
			return $classes;
		}

		$child_theme  = get_option( 'stylesheet' );
		$parent_theme = get_option( 'template' );

		// if the 2 options are the same, then there is no child theme.
		if ( $child_theme == $parent_theme ) {
			$child_theme = false;
		}

		$classes[] = "tribe-theme-$parent_theme";

		if ( $child_theme ) {
			$classes[] = "tribe-theme-child-$child_theme";
		}

		return $classes;
	}
}