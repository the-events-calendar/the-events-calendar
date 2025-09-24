<?php
/**
 * Ical query var.
 *
 * @since TBD
 *
 * @package TEC\Events\Request
 */

namespace TEC\Events\Request;

use TEC\Common\Request\Abstract_Query_Var;

/**
 * Class Ical
 *
 * @since TBD
 *
 * @package TEC\Events\Request
 */
class Ical extends Abstract_Query_Var {
	/**
	 * The query var name.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected string $name = 'ical';

	/**
	 * Whether the query var should be filtered.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected bool $should_filter = true;

	/**
	 * Whether the query var should overwrite valueless params.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected bool $should_accept_valueless_params = true;

	/**
	 * Whether the query var should filter superglobals.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected bool $should_filter_superglobal = true;
}
