<?php
/**
 * Handles the compatibility with the By Day Views (e.g. Month, Week) when Occurrences
 * are addressed by provisional post IDs.
 *
 * Adapted from `TEC\Events_Pro\Custom_Tables\V1\Views\V2\By_Day_View_Compatibility`: the
 * base implementation looks Occurrences up by real post ID, this one by the Occurrence
 * ID a provisional post ID encodes.
 *
 * @since TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 */

namespace TEC\Events\Custom_Tables\V1\Views\V2;

use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator as Provisional_ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Timezones as Timezones;

/**
 * Class Provisional_By_Day_View_Compatibility
 *
 * @since TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 */
class Provisional_By_Day_View_Compatibility extends By_Day_View_Compatibility {
	/**
	 * A reference to the current implementation of the Provisional Post ID Generator.
	 *
	 * @since TBD
	 *
	 * @var Provisional_ID_Generator
	 */
	private $provisional_id_generator;

	/**
	 * Provisional_By_Day_View_Compatibility constructor.
	 *
	 * @since TBD
	 *
	 * @param Provisional_ID_Generator $provisional_id_generator A reference to the current implementation
	 *                                                           of the Provisional Post ID Generator.
	 */
	public function __construct( Provisional_ID_Generator $provisional_id_generator ) {
		$this->provisional_id_generator = $provisional_id_generator;
	}

	/**
	 * Returns the day results, prepared as the `By_Day_View` expects them.
	 *
	 * @since TBD
	 *
	 * @param array<int> $result_ids A list of the Event post IDs to prepare the day results
	 *                               for.
	 *
	 * @return array<int,\stdClass> The prepared day results.
	 */
	public function prepare_day_results( array $result_ids = [] ) {
		if ( empty( $result_ids ) ) {
			return [];
		}

		$use_site_timezone = Timezones::is_mode( 'site' );
		$start_date_prop   = $use_site_timezone ? 'start_date_utc' : 'start_date';
		$end_date_prop     = $use_site_timezone ? 'end_date_utc' : 'end_date';
		$ids_chunk_size    = tec_query_batch_size( __METHOD__ );

		$prepared       = [];
		$base           = $this->provisional_id_generator->current();
		$occurrence_ids = array_map(
			static function ( $provisional_id ) use ( $base ) {
				return $provisional_id > $base ? $provisional_id - $base : $provisional_id;
			},
			$result_ids
		);

		$occurrence_ids_count = count( $occurrence_ids );

		while ( $occurrence_ids_count ) {
			$ids_chunk            = array_splice( $occurrence_ids, 0, $ids_chunk_size );
			$occurrence_ids_count = count( $occurrence_ids );
			$occurrences          = Occurrence::where_in( 'occurrence_id', $ids_chunk )->all();

			foreach ( $occurrences as $occurrence ) {
				/** @var Occurrence $occurrence */
				$prepared[ $base + $occurrence->occurrence_id ] = (object) [
					'ID'         => $base + $occurrence->occurrence_id,
					'start_date' => $occurrence->{$start_date_prop},
					'end_date'   => $occurrence->{$end_date_prop},
					'timezone'   => get_post_meta( $occurrence->post_id, '_EventTimezone', true ),
				];
			}
		}

		return wp_list_sort( $prepared, 'start_date', 'ASC' );
	}
}
