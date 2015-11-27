<?php


class Tribe__Events__Constants implements ArrayAccess {

	/**
	 * @var bool Whether the class will really define constants or not.
	 */
	protected $volatile;

	/**
	 * @var array An array that will store volatile values if the class is used in volatile mode.
	 */
	protected $volatile_values;

	public function __construct( $volatile = false ) {
		$this->volatile        = $volatile;
		$this->volatile_values = [ ];
	}

	public function offsetExists( $offset ) {
		return $this->volatile ? isset( $this->volatile_values[ $offset ] ) : defined( $offset );
	}

	public function offsetGet( $offset ) {
		return $this->volatile ? $this->volatile_values[ $offset ] : constant( $offset );
	}

	public function offsetSet( $offset, $value ) {
		if ( $this->volatile && ! isset( $this->volatile_values[ $offset ] ) ) {
			$this->volatile_values[ $offset ] = $value;
		} else {
			if ( ! defined( $offset ) ) {
				define( $offset, $value );
			}
		}
	}

	public function offsetUnset( $offset ) {
		if ( $this->volatile ) {
			$this->volatile_values = array_diff( $this->volatile_values, array( $offset ) );
		} else {
			// no op
		}
	}
}
