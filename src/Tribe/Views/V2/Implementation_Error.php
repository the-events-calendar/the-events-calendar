<?php
/**
 * A View specific exception to signal implementation errors.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

/**
 * Class Implementation_Error
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */
class Implementation_Error extends \Exception {

	/**
	 * Signals a class extending the View class is not implementing a required API method.
	 *
	 * @since 4.9.2
	 *
	 * @param string $method The not implemented method.
	 * @param object An instance of the object not implementing the method.
	 *
	 * @return \Tribe\Events\Views\V2\Implementation_Error A built instance of the exception.
	 */
	public static function because_extending_view_should_define_this_method( $method, $object ) {
		$class = get_class( $object );
		$message = "Any class extending the base View class should implement the {$method} method; {$class} does not.";

		return new static( $message );
	}
}
