<?php
/**
 * Attaches the correct Custom Tables Query modifier to a Custom Tables Query depending on its nature.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Monitors
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Monitors;

use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use WP_Query;

/**
 * Class Custom_Tables_Query_Monitor
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Monitors
 */
class Custom_Tables_Query_Monitor {
	use Query_Monitor;

	/**
	 * A list of possible modifiers implementations.
	 * Will be filtered into an array of modifiers.
	 *
	 * @since 6.0.0
	 *
	 * @var null|array<string>
	 */
	private $implementations = null;

	/**
	 * Returns the flag property that will be set on a `WP_Query` instance to indicate it should
	 * be ignored by the Monitor.
	 *
	 * @since 6.0.0
	 *
	 * @return string The name of the flag property that will be set on a `WP_Query` object to indicate it
	 *                should be ignored by the Monitor.
	 */
	public static function ignore_flag() {
		return 'tec_events_ct_ignore';
	}

	/**
	 * Whether the monitor applies to the Query or not.
	 *
	 * This monitor will only apply to custom tables Queries.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query|null $query A reference to the WP Query object
	 *                             to check.
	 *
	 * @return bool Whether this Monitor should apply to the query or not.
	 */
	private function applies_to_query( $query = null ) {
		return $query instanceof Custom_Tables_Query;
	}
}
