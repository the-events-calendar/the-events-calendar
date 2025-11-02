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
	 * Whether the query var should accept valueless params.
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

	/**
	 * Filters the value to either `1` or `null` (to unset).
	 *
	 * @since TBD
	 *
	 * @param mixed $value      The raw value to normalize.
	 * @param array $query_vars The query vars.
	 *
	 * @return int|null `1` when truthy, `null` when not.
	 */
	public function filter_query_var( $value, array $query_vars ) {
		$value = parent::filter_query_var( $value, $query_vars );

		if ( is_array( $value ) ) {
			$value = reset( $value );
		}

		return tribe_is_truthy( $value ) ? 1 : null;
	}
}
