<?php
/**
 * Handles the plugin integration and compatibility with the `By_Day_View` class, the common ancestor of Month and
 * Week View.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 */

namespace TEC\Events\Custom_Tables\V1\Views\V2;

use stdClass;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Models\Post_Types\Event;
use Tribe__Timezones as Timezones;

/**
 * Class By_Day_View_Compatibility
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 */
class By_Day_View_Compatibility {

	/**
	 * Returns the day results, prepared as the `By_Day_View` expects them.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int>   $ids        A list of the Event post IDs to prepare the day results for.
	 * @param string|null  $start_date Optional. Start date for filtering occurrences (Y-m-d format).
	 * @param string|null  $end_date   Optional. End date for filtering occurrences (Y-m-d format).
	 *
	 * @return array<int,stdClass> The prepared day results.
	 */
	public function prepare_day_results( array $ids = [], $start_date = null, $end_date = null ) {
		if ( empty( $ids ) ) {
			return [];
		}

		$use_site_timezone = Timezones::is_mode( 'site' );
		$start_date_prop   = $use_site_timezone ? 'start_date_utc' : 'start_date';
		$end_date_prop     = $use_site_timezone ? 'end_date_utc' : 'end_date';
		$ids_chunk_size    = tec_query_batch_size( __METHOD__ );
		$ids_count         = count( $ids );

		$prepared = [];

		while ( $ids_count ) {
			$ids_chunk   = array_splice( $ids, 0, $ids_chunk_size );
			$ids_count   = count( $ids );

			// When Events Calendar Pro is not active, limit to the earliest occurrence per event.
			if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
				// For each post_id, get only the earliest occurrence BY DATE, not by occurrence_id.
				// Occurrence IDs may not be in chronological order.
				$occurrences = [];
				foreach ( $ids_chunk as $post_id ) {
					$occurrence = Occurrence::where( 'post_id', '=', $post_id )
						->order_by( 'start_date', 'ASC' )
						->order_by( 'occurrence_id', 'ASC' )
						->first();
					if ( $occurrence ) {
						$occurrences[] = $occurrence;
					}
				}
			} else {
				// When Pro is active, fetch all occurrences but filter by date range if provided
				$query = Occurrence::where_in( 'post_id', $ids_chunk );

				// Filter by date range if provided (for Day View, Month View, etc.)
				if ( $start_date && $end_date ) {
					// Convert Y-m-d to Y-m-d H:i:s format for comparison
					$start_datetime = $start_date . ' 00:00:00';
					$end_datetime   = $end_date . ' 23:59:59';

					// Find occurrences that overlap with the date range
					// An occurrence overlaps if: occurrence_start < range_end AND occurrence_end > range_start
					$query->where( $start_date_prop, '<', $end_datetime )
					      ->where( $end_date_prop, '>', $start_datetime );
				}

				$occurrences = $query->all();
			}

			foreach ( $occurrences as $occurrence ) {
				/** @var Occurrence $occurrence */
				$prepared[ $occurrence->post_id ] = (object) [
					'ID'         => $occurrence->post_id,
					'start_date' => $occurrence->{$start_date_prop},
					'end_date'   => $occurrence->{$end_date_prop},
					'timezone'   => get_post_meta( $occurrence->post_id, '_EventTimezone', true ),
				];
			}
		}

		$prepared = wp_list_sort( $prepared, 'start_date', 'ASC' );

		return $prepared;
	}
}
