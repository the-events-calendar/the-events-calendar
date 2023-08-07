<?php


class Tribe__Events__Constants implements ArrayAccess {

	/**
	 * @var bool Whether the class will define and read real constants or not.
	 */
	protected $volatile;

	/**
	 * @var array An array that will store volatile values if the class is used in volatile mode.
	 */
	protected $volatile_values;

	/**
	 * Tribe__Events__Constants constructor.
	 *
	 * @param bool $volatile If `true` the class will not define and read real constants.
	 */
	#[\ReturnTypeWillChange]
	public function __construct( $volatile = false ) {
		$this->volatile        = $volatile;
		$this->volatile_values = [];
	}

	/**
	 * Whether a constant is defined or not.
	 *
	 * @param string $offset
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ): bool {
		return $this->volatile ? isset( $this->volatile_values[ $offset ] ) : defined( $offset );
	}

	/**
	 * Gets a constant value.
	 *
	 * @param string $offset
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->volatile ? $this->volatile_values[ $offset ] : constant( $offset );
	}

	/**
	 * Sets the value of a constant if not already defined.
	 *
	 * @param string $offset
	 * @param mixed $value
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		if ( $this->volatile && ! isset( $this->volatile_values[ $offset ] ) ) {
			$this->volatile_values[ $offset ] = $value;
		} else {
			if ( ! defined( $offset ) ) {
				define( $offset, $value );
			}
		}
	}

	/**
	 * Unsets a constant if in volatile mode.
	 *
	 * @param string $offset
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		if ( $this->volatile ) {
			$this->volatile_values = array_diff( $this->volatile_values, [ $offset ] );
		} else {
			// no op
		}
	}
}
