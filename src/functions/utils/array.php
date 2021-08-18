<?php
/**
 * Utility functions dealing with arrays
 */

/**
 * Drop-in replacement for array_unique(), designed to operate on an array of arrays
 * where each inner array is populated with strings (or types that can be stringified
 * while essentially keeping their unique value).
 *
 * @param array $original array_of_arrays
 *
 * @return array
 */
function tribe_array_unique( array $original ) {
	$unique = [];

	foreach ( $original as $inner ) {
		$unique[ join( '|', $inner ) ] = $inner;
	}

	return array_values( $unique );
}
