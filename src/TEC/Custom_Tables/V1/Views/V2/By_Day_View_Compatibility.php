<?php
/**
 * Handles the plugin integration and compatibility with the `By_Day_View` class, the common ancestor of Month and
 * Week View.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Views\V2
 */

namespace TEC\Custom_Tables\V1\Views\V2;

use TEC\Custom_Tables\V1\Events\Provisional\ID_Generator as Provisional_ID_Generator;
use TEC\Custom_Tables\V1\Models\Occurrence;
use Tribe__Timezones as Timezones;

/**
 * Class By_Day_View_Compatibility
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Views\V2
 */
class By_Day_View_Compatibility {
	/**
	 * A reference to the current implementation of the Provisional Post ID Generator.
	 *
	 * @since TBD
	 *
	 * @var Provisional_ID_Generator
	 */
	private $provisional_id_generator;

	/**
	 * By_Day_View_Compatibility constructor.
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
	 * @param array<int> $provisional_ids A list of the Event post IDs to prepare the day results
	 *                                    for.
	 *
	 * @return array<int,\stdClass> The prepared day results.
	 */
	public function prepare_day_results( array $provisional_ids = [] ) {
		if ( empty( $provisional_ids ) ) {
			return [];
		}

		$use_site_timezone = Timezones::is_mode( 'site' );
		$start_date_prop   = $use_site_timezone ? 'start_date_utc' : 'start_date';
		$end_date_prop     = $use_site_timezone ? 'end_date_utc' : 'end_date';

		$prepared       = [];
		$base           = $this->provisional_id_generator->current();
		$occurrence_ids = array_map( static function ( $provisional_id ) use ( $base ) {
			return $provisional_id > $base ? $provisional_id - $base : $provisional_id;
		}, $provisional_ids );

		/** @var Occurrence $occurrence */
		foreach ( Occurrence::order_by($start_date_prop, 'ASC')
		                    ->find_all( $occurrence_ids, 'occurrence_id' ) as $occurrence ) {
			$prepared[ $base + $occurrence->occurrence_id ] = (object) [
				'ID'         => $base + $occurrence->occurrence_id,
				'start_date' => $occurrence->{$start_date_prop},
				'end_date'   => $occurrence->{$end_date_prop},
				'timezone'   => get_post_meta( $occurrence->post_id, '_EventTimezone', true ),
			];
		}

		return $prepared;
	}
}
