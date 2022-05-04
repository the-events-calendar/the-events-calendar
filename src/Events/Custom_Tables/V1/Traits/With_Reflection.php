<?php
/**
 * Provides Reflection API wrappers.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Traits;
 */

namespace TEC\Events\Custom_Tables\V1\Traits;

/**
 * Trait With_Reflection.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Traits;
 */
trait With_Reflection {

	/**
	 * Returns the value of a not accessible object property.
	 *
	 * @since TBD
	 *
	 * @param object $object The object to read the property value from.
	 * @param string $prop   The name of the property to get the value of.
	 *
	 * @return mixed The property value.
	 *
	 * @throws \ReflectionException If the object does not declare such a
	 *                              property.
	 */
	private function get_private_property( $object, $prop ) {
		$property = new \ReflectionProperty( $object, $prop );

		if ( $property->isPublic() ) {
			return $object->{$prop};
		}

		$property->setAccessible( true );
		$value = $property->getValue( $object );
		$property->setAccessible( false );

		return $value;
	}
}