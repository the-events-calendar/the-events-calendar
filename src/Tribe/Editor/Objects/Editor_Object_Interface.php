<?php
/**
 * The API provided by an editor object.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Editory\Objects
 */

namespace Tribe\Events\Editor\Objects;

/**
 * Class Editor_Object_Interface
 *
 * @since   TBD
 *
 * @package Tribe\Events\Editory\Objects
 */
interface Editor_Object_Interface {

	/**
	 * Returns the editor object data in the format required by the block editor.
	 *
	 * @since TBD
	 */
	public function data( $key = null, $default = null );
}
