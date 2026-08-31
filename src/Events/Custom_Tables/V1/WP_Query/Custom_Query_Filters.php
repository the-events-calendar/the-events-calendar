<?php
/**
 * Handles the interaction of the plugin with the Custom Tables Queries
 * set up in The Events Calendar plugin.
 *
 * @since   6.0.0
 * @since TBD Migrated to The Events Calendar from Events Calendar Pro.
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query;

use DateTime;
use TEC\Events\Custom_Tables\V1\Models\Builder;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use TEC\Events\Custom_Tables\V1\Events\Event_Sequence;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use Tribe__Cache;
use Tribe__Cache_Listener;
use Tribe__Date_Utils;
use Tribe__Events__Main;
use WP_Query;

/**
 * Class Custom_Query_Filters
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */
class Custom_Query_Filters {

	const POST_IN = 'tec_ct1_post__in';
	const POST_NOT_IN = 'tec_ct1_post__not_in';

	/**
	 * The Provisional Post ID base.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	private $provisional_id_base;

	/**
	 * A reference to the current provisional post handler.
	 *
	 * @since 6.0.0
	 *
	 * @var Provisional_Post
	 */
	private $provisional_post;

	/**
	 * Custom_Query_Filters constructor.
	 *
	 * @param int              $provisional_id_base The Provisional Post ID base.
	 * @param Provisional_Post $provisional_post    A reference to the current Provisional
	 *                                              Post provider.
	 */
	public function __construct( $provisional_id_base, Provisional_Post $provisional_post ) {
		$this->provisional_id_base = $provisional_id_base;
		$this->provisional_post    = $provisional_post;
	}

	/**
	 * Returns the SQL code required to select the distinct
	 * Occurrences in the context of a Custom Tables Query.
	 *
	 * @since 6.0.0
	 *
	 * @return string The SQL code required to select the distinct
	 *                Occurrences in the context of a Custom Tables Query.
	 */
	public function get_occurrence_field() {
		$occurrences_table            = Occurrences::table_name( true );
		$occurrences_table_uid_column = Occurrences::uid_column();

		$occurrence_id_field = sprintf(
			'(%1$s.%2$s + %3$d) as %2$s',
			$occurrences_table,
			$occurrences_table_uid_column,
			$this->provisional_id_base
		);

		return $occurrence_id_field;
	}

	/**
	 * Will mutate the $query_vars for custom table queries,
	 * used by the custom query object.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $query_vars The Custom Tables Query variables.
	 *
	 * @return array<string,mixed> The filtered query variables.
	 */
	public function filter_query_vars( array $query_vars ) {
		$keys = [
			'post__not_in' => self::POST_NOT_IN,
			'post__in'     => self::POST_IN,
		];

		foreach ( $keys as $query_var => $occurrence_query_var ) {
			if ( ! isset( $query_vars[ $query_var ] ) ) {
				continue;
			}

			// Move the post IDs, provisional or not, over to our query var.
			$query_vars[ $occurrence_query_var ] = (array) $query_vars[ $query_var ];
			unset( $query_vars[ $query_var ] );
		}

		return $query_vars;
	}

	/**
	 * Depending on the custom table specific `post__in` and `post__not_in` query
	 * vars, update the query `WHERE` statement.
	 *
	 * @since 6.0.0
	 *
	 * @param string   $where The `WHERE` statement as produced
	 * @param WP_Query $query A reference to the Query being filtered.
	 *
	 * @return string The filtered `WHERE` statement.
	 */
	public function filter_where( $where, WP_Query $query ) {
		if ( ! $query instanceof Custom_Tables_Query ) {
			return $where;
		}

		$not__in = $query->get( self::POST_NOT_IN );
		if ( ! empty( $not__in ) ) {
			[ $post_ids, $occurrence_provisional_ids ] = $this->divide_ids( $not__in );
			$sql   = $this->build_in_sql_for( 'post__not_in', $post_ids, $occurrence_provisional_ids );

			if ( strpos( $where, $sql ) === false ) {
				$where .= " AND ($sql)";
			}
		}

		$in = $query->get( self::POST_IN );
		if ( ! empty( $in ) ) {
			[ $post_ids, $occurrence_provisional_ids ] = $this->divide_ids( $in );
			$sql   = $this->build_in_sql_for( 'post__in', $post_ids, $occurrence_provisional_ids );

			if ( strpos( $where, $sql ) === false ) {
				$where .= " AND ($sql)";
			}
		}

		return $where;
	}

	/**
	 * Divides the IDs found in a `post__not_in` or `post__in` query var between
	 * real post IDs and Occurrence Provisional post IDs.
	 *
	 * @param array<int> $not__in List of IDs
	 *
	 * @return array<array<int>> Two arrays, one of real post IDs, the other of
	 *                           Occurrence provisional post IDs.
	 */
	private function divide_ids( array $not__in ): array {
		$post_ids                   = [];
		$occurrence_provisional_ids = [];
		foreach ( $not__in as $id ) {
			if ( $this->provisional_post->is_provisional_post_id( $id ) ) {
				$occurrence_provisional_ids[] = $id;
			} else {
				$post_ids[] = $id;
			}
		}

		return array( $post_ids, $occurrence_provisional_ids );
	}

	/**
	 * Builds the SQL statement that should be added to the `WHERE` to exclude or include
	 * Occurrences depending on required inclusion statement.
	 *
	 * @since 6.0.0
	 * @param string     $operator                   The operator to build for, either `post__in` or `post__not_in`.
	 * @param array<int> $post_ids                   A list of real post IDs to build the SQL statement for.
	 * @param array<int> $occurrence_provisional_ids A list of Occurrence provisional post IDs  to build the
	 *                                               SQL statement for.
	 *
	 * @return string The built SQL statement.
	 */
	private function build_in_sql_for( $operator, array $post_ids, array $occurrence_provisional_ids ) {
		/*
		 * `post__in` should include post IDs `OR` Occurrence IDs.
		 * `post__not_in` should exclude post IDs `AND` Occurrence IDs.
		 */
		if ( $operator === 'post__in' ) {
			$in_operator      = 'IN';
			$implode_operator = 'OR';
		} else {
			$in_operator      = 'NOT IN';
			$implode_operator = 'AND';
		}
		global $wpdb;
		$occurrence_table = Occurrences::table_name();
		$id_statements    = [];
		if ( count( $occurrence_provisional_ids ) ) {
			$interval        = implode( ',', array_map( [
				$this->provisional_post,
				'normalize_provisional_post_id'
			], $occurrence_provisional_ids ) );
			$id_statements[] = "{$occurrence_table}.occurrence_id {$in_operator} ({$interval})";
		}
		if ( count( $post_ids ) ) {
			$interval        = implode( ',', array_map( 'absint', $post_ids ) );
			$id_statements[] = "{$wpdb->posts}.ID {$in_operator} ({$interval})";
		}

		return implode( " {$implode_operator} ", $id_statements );
	}

	/**
	 * Modifies the WP_Query for the sequence ID, i.e. occurrence lookup.
	 *
	 * @since 6.0.11
	 *
	 * @param WP_Query $wp_query The WP_Query instance to be modified.
	 * @param numeric  $event_id The ID to set for this lookup.
	 */
	protected function wp_query_for_sequence_id( WP_Query $wp_query, $event_id ) {
		unset( $wp_query->query_vars['name'], $wp_query->query_vars[ Tribe__Events__Main::POSTTYPE ] );
		$wp_query->set( 'p', $event_id );
	}

	/**
	 * Fetch an Occurrence Builder with the start_date boundaries already applied.
	 * This will add a filter for the beginning to end of day for the date supplied.
	 *
	 * @since 6.0.11
	 *
	 * @param DateTime $date The start_date to filter occurrences by.
	 *
	 * @return Builder The Occurrence's Builder with start_date where clauses applied.
	 */
	public static function occurrence_where_same_day( DateTime $date ): Builder {
		return Occurrence::where( 'start_date', '>=', $date->format( 'Y-m-d 00:00:00' ) )
			->where( 'start_date', '<=', $date->format( 'Y-m-d 23:59:59' ) );
	}

	/**
	 * Inspecting this WP_Query instance to see if intended for an `eventSequence` rewrite route. Will
	 * modify the WP_Query instance with appropriate params.
	 *
	 * @since 6.0.11
	 *
	 * @param WP_Query $wp_query The query to determine if candidate for `eventSequence` rewrite lookups.
	 */
	public function parse_for_sequence_id_lookup( $wp_query ): void {
		global $wpdb;
		// Correct query param?
		if ( ! $wp_query instanceof WP_Query ) {
			return;
		}

		// Is this an eventSequence lookup?
		$date             = $wp_query->get( 'eventDate' );
		$slug             = $wp_query->query['name'] ?? '';
		$sequence_number  = $wp_query->get( 'eventSequence' );
		$post_already_set = ! empty( $wp_query->get( 'post__in' ) ) || ( is_numeric( $wp_query->get( 'p' ) ) && $wp_query->get( 'p' ) > 0 );

		if ( ! is_numeric( $sequence_number )
		     || $post_already_set
		     || ! $wp_query->is_main_query()
		     || empty( $date )
		     || empty( $slug )
		     || (array) $wp_query->get( 'post_type' ) !== [ Tribe__Events__Main::POSTTYPE ] ) {
			return; // We shouldn't be here.
		}

		$cache_key = 'single_event_' . $slug . '_' . $date . '_' . $sequence_number;
		$cache     = tribe_cache();

		// Check cache?
		$event_id = $cache->get( $cache_key, Tribe__Cache_Listener::TRIGGER_SAVE_POST );

		// If cached, we are done validating this "sequence", so set the ID.
		if ( is_numeric( $event_id ) ) {
			$this->wp_query_for_sequence_id( $wp_query, $event_id );

			return;
		}

		// Get our post ID
		$post_query = "SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type=%s LIMIT 1";
		$post_query = $wpdb->prepare( $post_query, $slug, Tribe__Events__Main::POSTTYPE );
		$post_id    = $wpdb->get_var( $post_query );

		if ( empty( $post_id ) ) {
			// Something went wrong, bail.
			do_action( 'tribe_log', 'error', 'Could not locate this post by the slug provided.', [ 'method' => __METHOD__ ] );

			return;
		}

		// Convert to a date object we can use.
		$event_date = Tribe__Date_Utils::build_date_object( $date );
		if ( ! $event_date instanceof DateTime ) {
			do_action( 'tribe_log', 'error', "Could not parse this '$date' date provided.", [ 'method' => __METHOD__ ] );

			return;
		}

		// Do we have an occurrence for this sequence?
		$occurrence = Event_Sequence::find_occurrence_by_sequence( $post_id, $sequence_number, $date );
		if ( ! $occurrence instanceof Occurrence ) {
			// Check if there is a valid sequence that was not generated yet?
			$other_occurrence = Event_Sequence::get_occurrence_on_same_day( $post_id, $date );;
			if ( ! $other_occurrence instanceof Occurrence ) {
				// This is a 404
				$wp_query->query_vars = array();
				$wp_query->set_404();
				status_header(404);
				return;
			}
			Event_Sequence::sync_sequences_for( $other_occurrence );
			$occurrence = Event_Sequence::find_occurrence_by_sequence( $post_id, $sequence_number, $date );
			// Ensure this is an occurrence.
			if ( ! $occurrence instanceof Occurrence ) {
				// This is a 404
				$wp_query->query_vars = array();
				$wp_query->set_404();
				status_header(404);
				return;
			}
		}

		// Yep, this is an occurrence.
		// We should route this query to our provisional ID for the occurrence we found on this sequence number.
		$event_id = $occurrence->provisional_id;

		$this->wp_query_for_sequence_id( $wp_query, $event_id );
		$cache->set( $cache_key, $event_id, Tribe__Cache::NO_EXPIRATION, Tribe__Cache_Listener::TRIGGER_SAVE_POST );
	}
}
