<?php
/**
 * Builds the Scheduled Dates list of an Event for the admin edit screen.
 *
 * The list is display-only: it shows every Occurrence generated for the Event, with
 * a View link per date. Editing a single Occurrence requires the scoped-updates
 * machinery (an Events Calendar Pro port still to be designed), so no Edit links
 * are offered here.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use DateTimeImmutable;
use Exception;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;

/**
 * Class Occurrences_List.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Occurrences_List {
	/**
	 * The query variable selecting the list view.
	 *
	 * @since TBD
	 */
	public const VIEW_VAR = 'tec_dates_view';

	/**
	 * The query variable selecting the list page.
	 *
	 * @since TBD
	 */
	public const PAGE_VAR = 'tec_dates_page';

	/**
	 * Returns the number of Occurrences scheduled for the Event.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return int The number of scheduled Occurrences.
	 */
	public function get_count( int $post_id ): int {
		if ( ! tribe()->getVar( 'ct1_fully_activated', false ) ) {
			// The Occurrences table might not exist at all.
			return 0;
		}

		$post_id = Occurrence::normalize_id( $post_id );

		return (int) Occurrence::where( 'post_id', '=', $post_id )->count();
	}

	/**
	 * Returns one page of the Event's scheduled dates, with its pagination data.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return array{
	 *     view: string,
	 *     page: int,
	 *     per_page: int,
	 *     total: int,
	 *     total_pages: int,
	 *     rows: array<int,array{provisional_id: int, start: DateTimeImmutable, end: DateTimeImmutable}>
	 * } The current page of the list.
	 */
	public function get_page_data( int $post_id ): array {
		$view = 'all' === tribe_get_request_var( self::VIEW_VAR, 'upcoming' ) ? 'all' : 'upcoming';
		$page = max( 1, absint( tribe_get_request_var( self::PAGE_VAR, 1 ) ) );

		/**
		 * Filters the number of scheduled dates shown per page in the Event edit screen list.
		 *
		 * @since TBD
		 *
		 * @param int $per_page The number of dates per page.
		 * @param int $post_id  The Event post ID.
		 */
		$per_page = max( 1, (int) apply_filters( 'tec_events_recurrence_occurrences_list_per_page', 20, $post_id ) );

		$data = [
			'view'        => $view,
			'page'        => $page,
			'per_page'    => $per_page,
			'total'       => 0,
			'total_pages' => 0,
			'rows'        => [],
		];

		if ( ! tribe()->getVar( 'ct1_fully_activated', false ) ) {
			return $data;
		}

		$post_id = Occurrence::normalize_id( $post_id );

		$builder       = Occurrence::where( 'post_id', '=', $post_id );
		$count_builder = Occurrence::where( 'post_id', '=', $post_id );

		if ( 'upcoming' === $view ) {
			$now = current_time( 'mysql' );
			$builder->where( 'end_date', '>=', $now );
			$count_builder->where( 'end_date', '>=', $now );
		}

		$data['total']       = (int) $count_builder->count();
		$data['total_pages'] = (int) ceil( $data['total'] / $per_page );
		$data['page']        = min( $page, max( 1, $data['total_pages'] ) );

		if ( 0 === $data['total'] ) {
			return $data;
		}

		$occurrences = iterator_to_array(
			$builder->order_by( 'start_date_utc', 'ASC' )
					->limit( $per_page )
					->offset( ( $data['page'] - 1 ) * $per_page )
					->all(),
			false
		);

		$base_provisional_id = tribe( ID_Generator::class )->current();

		foreach ( $occurrences as $occurrence ) {
			try {
				$data['rows'][] = [
					'provisional_id' => $base_provisional_id + (int) $occurrence->occurrence_id,
					'start'          => new DateTimeImmutable( (string) $occurrence->start_date ),
					'end'            => new DateTimeImmutable( (string) $occurrence->end_date ),
				];
			} catch ( Exception $e ) {
				continue;
			}
		}

		if ( count( $data['rows'] ) ) {
			// The provisional posts must be in the posts cache for `get_permalink()` to resolve them.
			tribe( Provisional_Post::class )->hydrate_caches( array_column( $data['rows'], 'provisional_id' ) );
		}

		return $data;
	}
}
