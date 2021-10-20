<?php
/**
 * Utility functions to support the Custom Tables features.
 *
 * @since   TBD
 * @package iCalTec
 */

namespace iCalTec;

/**
 * Sets a protected or private property of an object.
 *
 * If the object does not have that property defined then nothing is done.
 *
 * @since TBD
 *
 * @param object $object   The object to set the property of.
 * @param string $property The name of the property to set.
 * @param mixed  $value    The value that will be assigned to the property.
 */
function set_private_property( $object, $property, $value ) {
	try {
		$property = ( new \ReflectionObject( $object ) )->getProperty( $property );
	} catch ( \ReflectionException $e ) {
		return;
	}

	$property->setAccessible( true );

	$property->setValue( $object, $value );
}

/**
 * Gets the value of a protected or private property an object.
 *
 * @since TBD
 *
 * @param object $object   The object to read the property from.
 * @param string $property The property name.
 *
 * @return mixed The current value of the property or `null` if the property is not set.
 */
function get_private_property( $object, $property ) {
	try {
		$property = ( new \ReflectionObject( $object ) )->getProperty( $property );
	} catch ( \ReflectionException $e ) {
		return null;
	}

	$property->setAccessible( true );

	return $property->getValue( $object ) ?: null;
}
