<?php
/**
 * An information repository about table redirection.
 *
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query;

use TEC\Events\Custom_Tables\V1\Tables\Occurrences;

/**
 * Class Redirection_Schema
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */
class Redirection_Schema {
	/**
	 * Returns the unfiltered version of the meta key redirection map.
	 *
	 * Note: extending classes should extend this method to modify the map.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string,array<string>> The unfiltered version of the meta key redirection map.
	 */
	protected static function get_meta_key_redirection_map() {
		$occurrences_table = Occurrences::table_name( true );

		return [
			'_EventStartDate'    => [
				'table'  => $occurrences_table,
				'column' => 'start_date',
				'join_posts_on' => 'post_id',
			],
			'_EventEndDate'      => [
				'table'  => $occurrences_table,
				'column' => 'end_date',
				'join_posts_on' => 'post_id',
			],
			'_EventStartDateUTC' => [
				'table'  => $occurrences_table,
				'column' => 'start_date_utc',
				'join_posts_on' => 'post_id',
			],
			'_EventEndDateUTC'   => [
				'table'  => $occurrences_table,
				'column' => 'end_date_utc',
				'join_posts_on' => 'post_id',
			],
			'_EventDuration'     => [
				'table'  => $occurrences_table,
				'column' => 'duration',
				'join_posts_on' => 'post_id',
			],
		];
	}

	/**
	 * Returns the filtered version of the meta key redirection map.
	 *
	 * Note: this method is to decouple the set up and filtering of the map for the benefit or external code; extending
	 * classes should override the `get_meta_key_redirection_map` method.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string,array<string>> The filtered version of the meta key redirection map.
	 */
	public static function get_filtered_meta_key_redirection_map() {
		$map = static::get_meta_key_redirection_map();

		/**
		 * Filters the meta key redirection map that will be used, from a meta key (e.g. `_EventStartDate`) to
		 * redirect the request to a custom table key and column.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string,array<string>> A map from meta keys to the corresponding custom table name and column.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_meta_key_redirection_map', $map );
	}
}
