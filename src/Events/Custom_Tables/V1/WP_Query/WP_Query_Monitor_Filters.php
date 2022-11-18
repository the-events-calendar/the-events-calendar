<?php
/**
 * Filters for the WP_Query_Monitor.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query;


use TEC\Events\Custom_Tables\V1\WP_Query\Modifiers\Events_Admin_List_Modifier;
use TEC\Events\Custom_Tables\V1\WP_Query\Modifiers\Events_Only_Modifier;
use TEC\Events\Custom_Tables\V1\WP_Query\Modifiers\WP_Query_Modifier;
use TEC\Events\Custom_Tables\V1\WP_Query\Monitors\Query_Monitor;
use TEC\Events\Custom_Tables\V1\WP_Query\Monitors\WP_Query_Monitor;

/**
 * Class WP_Query_Monitor_Filters
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */
class WP_Query_Monitor_Filters {
	/**
	 * @param array<WP_Query_Modifier> $implementations The query modifier implementations to be filtered.
	 * @param Query_Monitor            $query_monitor   An instance of a Query Monitor class.
	 *
	 * @return array<WP_Query_Modifier> The filtered query modifier implementations.
	 */
	public function filter_query_modifier_implementations( array $implementations, $query_monitor ): array {
		if ( $query_monitor instanceof WP_Query_Monitor ) {
			if ( ! in_array( Events_Only_Modifier::class, $implementations ) ) {
				$implementations[] = Events_Only_Modifier::class;
			}
			if ( ! in_array( Events_Admin_List_Modifier::class, $implementations ) ) {
				$implementations[] = Events_Admin_List_Modifier::class;
			}
		}

		return $implementations;
	}
}
