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
	 * @param array<int> $ids A list of the Event post IDs to prepare the day results
	 *                        for.
	 *
	 * @return array<int,stdClass> The prepared day results.
	 */
	public function prepare_day_results( array $ids = [] ) {
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
			$occurrences = Occurrence::where_in( 'post_id', $ids_chunk )->all();

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
