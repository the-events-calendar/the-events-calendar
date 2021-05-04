<?php
/**
 * Add theme compatibility classes.
 *
 * @since 4.9.3
 * @since TBD made an extension of Tribe\Utils\Theme_Compatibility
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Template_Bootstrap;
use Tribe\Utils\Body_Classes;
use Tribe\Utils\Theme_Compatibility as Compat;

class Theme_Compatibility extends Compat {
	/**
	 * Fetches the correct class strings for theme and child theme if available.
	 *
	 * @since 4.9.3
	 * @since TBD made an extension of Tribe\Utils\Theme_Compatibility
	 *
	 * @return array $classes
	 */
	public function get_body_classes() {
		_deprecated_function( __FUNCTION__, 'TBD', 'Tribe\Utils\Theme_Compatibility::get_compatibility_classes()' );
		return static::get_compatibility_classes();
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
		_deprecated_function( __FUNCTION__, '5.1.5', 'Theme_Compatibility::add_body_classes()' );

		if ( ! tribe( Template_Bootstrap::class )->should_load() ) {
			return $classes;
		}

		if ( ! static::is_compatibility_required() ) {
			return $classes;
		}

		return array_merge( $classes, $this->get_body_classes() );
	}

	/**
	 * Contains the logic for if this object's classes should be added to the queue.
	 *
	 * @since 5.1.5
	 *
	 * @param boolean $add   Whether to add the class to the queue or not.
	 * @param array   $class The array of body class names to add.
	 * @param string  $queue The queue we want to get 'admin', 'display', 'all'.

	 * @return boolean Whether body classes should be added or not.
	 */
	public function should_add_body_class_to_queue( $add, $class, $queue ) {
		if (
			'admin' === $queue
			|| ! tribe( Template_Bootstrap::class )->should_load()
			|| ! static::is_compatibility_required()
		) {
			return $add;
		}

		if ( in_array( $class, $this->get_body_classes() ) ) {
			return true;
		}

		return $add;
	}

	/**
	 * Add body classes.
	 *
	 * @since 5.1.5
	 *
	 * @return void
	 */
	public function add_body_classes() {
		tribe( Body_Classes::class )->add_classes( $this->get_body_classes() );
	}
}
