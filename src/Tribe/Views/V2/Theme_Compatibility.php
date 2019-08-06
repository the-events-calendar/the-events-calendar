<?php
/**
 * Add theme compatibility things here.
 *
 * @todo  This is an implementation to set a body class we can use in the common implementation.
 *
 * @since   4.9.3
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Template_Bootstrap;
use Tribe__Container as Container;

class Theme_Compatibility {
	/**
	 * List of themes which have compatibility.
	 *
	 * @since 4.9.4
	 *
	 * @var   array
	 */
	protected $themes = [
		'avada',
		'divi',
		'enfold',
		'genesis',
		'twentyseventeen',
		'twentynineteen',
	];

	/**
	 * Checks if theme needs a compatibility fix.
	 *
	 * @since  4.9.3
   *
	 * @return boolean
	 */
	public function is_compatibility_required() {
		$template   = strtolower( get_template() );
		$stylesheet = strtolower( get_stylesheet() );

		// Prevents empty stylesheet or template
		if ( empty( $template ) || empty( $stylesheet ) ) {
			return false;
		}

		if ( in_array( $template, $this->get_registered_themes() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add the theme to the body class.
	 *
	 * @since 4.9.3
	 *
	 * @param  array $classes Classes that are been passed to the body.
	 *
	 * @return array $classes
	 */
	public function filter_add_body_classes( array $classes ) {
		if ( ! tribe( Template_Bootstrap::class )->should_load() ) {
			return $classes;
		}

		if ( ! $this->is_compatibility_required() ) {
			return $classes;
		}

		return array_merge( $classes, $this->get_body_classes() );
	}

	/**
	 * Fetches the correct class strings for theme and child theme if available.
	 *
	 * @since 4.9.3
	 *
	 * @return array $classes
	 */
	public function get_body_classes() {
		$classes      = [];
		$child_theme  = strtolower( get_stylesheet() );
		$parent_theme = strtolower( get_template() );

		// Prevents empty stylesheet or template
		if ( empty( $parent_theme ) || empty( $child_theme ) ) {
			return $classes;
		}

		$classes[] = sanitize_html_class( "tribe-theme-$parent_theme" );

		// if the 2 options are the same, then there is no child theme.
		if ( $child_theme !== $parent_theme ) {
			$classes[] = sanitize_html_class( "tribe-theme-child-$child_theme" );
		}

		return $classes;
	}

	/**
	 * Returns a list of themes registred for compatibility with our Views.
	 *
	 * @since  4.9.4
	 *
	 * @return array An array of the themes registred.
	 */
	public function get_registered_themes() {
		/**
		 * Filters the list of themes that are registred for compatibility.
		 *
		 * @since 4.9.4
		 *
		 * @param array $registered An associative array of views in the shape `[ <slug> => <class> ]`.
		 */
		$registered = apply_filters( 'tribe_events_views_v2_theme_compatibility_registered', $this->themes );

		return (array) $registered;
	}
}
