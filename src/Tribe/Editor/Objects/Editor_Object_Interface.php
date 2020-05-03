<?php
/**
 * The API provided by an editor object.
 *
 * @since   5.1.0
 *
 * @package Tribe\Events\Editory\Objects
 */

namespace Tribe\Events\Editor\Objects;

/**
 * Class Editor_Object_Interface
 *
 * @since   5.1.0
 *
 * @package Tribe\Events\Editory\Objects
 */
interface Editor_Object_Interface {

	/**
	 * Returns the editor object data in the format required by the block editor.
	 *
	 * @since 5.1.0
	 *
	 * @param string|null $key     The specific data key to get, or `null` to get all data.
	 * @param mixed       $default The default value to return if the specified data key is not found, ignored if the
	 *                             data key is `null`.
	 *
	 * @return array<string,mixed> An array representation of the block editor object.
	 */
	public function data( $key = null, $default = null );
}
