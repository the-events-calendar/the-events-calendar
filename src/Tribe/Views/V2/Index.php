<?php
/**
 * The main Views index template file.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

/**
 * Class Index
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */
class Index extends Template {

	/**
	 * Index constructor.
	 *
	 * Overrides the base implementation to allow plugins and themes to override the index file.
	 */
	public function __construct() {
		parent::__construct( 'default-template' );
	}
}