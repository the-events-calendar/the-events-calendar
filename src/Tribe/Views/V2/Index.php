<?php
/**
 * The main Views index template file.
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */

namespace Tribe\Events\Views\V2;

/**
 * Class Index
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */
class Index extends Template {

	/**
	 * Index constructor.
	 *
	 * Overrides the base implementation to allow plugins and themes to override the index file.
	 */
	public function __construct() {
		parent::__construct( 'index' );
	}
}