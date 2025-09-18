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
	 * Filters if the superglobal is allowed to be filtered for this var.
	 *
	 * @since TBD
	 *
	 * @param string $key The query var name.
	 * @param string $superglobal The superglobal key (GET, POST, REQUEST).
	 *
	 * @return string|bool Whether the value is allowed. Returning a string "key" will limit the superglobal modification to that key.
	 */
	public function filter_superglobal_allowed( string $key, string $superglobal ) {
		return true;
	}

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
		if ( is_array( $value ) ) {
			$value = reset( $value );
		}

		// Support presence-only query var (?ical) as truthy.
		if ( array_key_exists( $this->get_name(), $query_vars ) && ( '' === $value || null === $value ) ) {
			return 1;
		}

		return tribe_is_truthy( $value ) ? 1 : null;
	}
}
