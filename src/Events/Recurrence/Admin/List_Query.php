<?php
/**
 * Queries the occurrence admin view while preserving WordPress search and visibility rules.
 *
 * @since TBD
 * @package TEC\Events\Recurrence\Admin
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Admin;

use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use WP_Query;

/** The admin-only adapter to the existing custom-table query. @since TBD */
class List_Query {
	/** @var string Internal marker, never inferred on unrelated queries. @since TBD */
	public const FLAG = 'tec_recurrence_admin_query';
	/** @var WP_Query|null The original list query, used to compute matching counts. @since TBD */
	private ?WP_Query $source = null;
	/** @var array<string,int>|null Counts for this request's filters. @since TBD */
	private ?array $counts = null;

	/** Registers scoped query callbacks. @since TBD @return void */
	public function register(): void {
		add_action( 'pre_get_posts', [ $this, 'prepare' ], 210 );
		add_filter( 'posts_pre_query', [ $this, 'results' ], 90, 2 );
		add_filter( 'posts_clauses', [ $this, 'clauses' ], 1000, 2 );
	}

	/** Removes callbacks and request state. @since TBD @return void */
	public function unregister(): void {
		remove_action( 'pre_get_posts', [ $this, 'prepare' ], 210 );
		remove_filter( 'posts_pre_query', [ $this, 'results' ], 90 );
		remove_filter( 'posts_clauses', [ $this, 'clauses' ], 1000 );
		$this->source = null;
		$this->counts = null;
	}

	/**
	 * Marks only the event list's main query.
	 *
	 * @since TBD
	 * @param WP_Query $query Query being prepared.
	 * @return void
	 */
	public function prepare( WP_Query $query ): void {
		if ( $query instanceof Custom_Tables_Query || ! $query->is_main_query() || 'tribe_events' !== $query->get( 'post_type' ) || ! Provider::is_list() ) {
			return;
		}
		$query->set( self::FLAG, 'occurrences' === Provider::view() );
		$query->set( 'post_parent', 0 );
		$query->set( 'tec_dates', Provider::range() );
		$query->set( 'tec_event', absint( tribe_get_request_var( 'tec_event', 0 ) ) );
		$query->set( 'perm', 'readable' );
		if ( ! $query->get( 'post_status' ) ) {
			$query->set( 'post_status', get_post_stati( [ 'show_in_admin_all_list' => true ] ) );
		}
		$this->source = $query;
		$this->counts = null;
	}

	/**
	 * Retrieves distinct occurrences with the existing hydration and identity machinery.
	 *
	 * @since TBD
	 * @param array|null $posts Earlier short-circuit results.
	 * @param WP_Query   $query Original query.
	 * @return array|null The hydrated results or an untouched earlier result.
	 */
	public function results( $posts, WP_Query $query ) {
		if ( null !== $posts || $query instanceof Custom_Tables_Query || $query !== $this->source || ! $query->get( self::FLAG ) ) {
			return $posts;
		}
		$custom = Custom_Tables_Query::from_wp_query( $query );
		$posts  = $custom->get_posts();
		tribe( Presentation::class )->prime( $posts );
		return $posts;
	}

	/**
	 * Applies range and ordering to both rows and counts, after legacy date sorting.
	 *
	 * @since TBD
	 * @param array    $clauses SQL clauses built by WordPress and the custom-table layer.
	 * @param WP_Query $query   Query being filtered.
	 * @return array Updated clauses for this screen only.
	 */
	public function clauses( array $clauses, WP_Query $query ): array {
		if ( ! $query instanceof Custom_Tables_Query || ! $query->get( self::FLAG ) ) {
			return $clauses;
		}
		global $wpdb;
		$table  = Occurrences::table_name( true );
		$range  = $query->get( 'tec_dates', 'upcoming' );
		$parent = absint( $query->get( 'tec_event' ) );
		if ( 'all' !== $range ) {
			$comparison        = 'past' === $range ? '<' : '>=';
			$clauses['where'] .= $wpdb->prepare( " AND $table.end_date_utc $comparison %s", gmdate( 'Y-m-d H:i:s' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Internal table name and allowlisted operator.
		}
		if ( $parent ) {
			$clauses['where'] .= $wpdb->prepare( " AND $table.post_id = %d", $parent ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Internal table name.
		}
		$clauses['where']  .= " AND {$wpdb->posts}.post_parent = 0";
		$clauses['groupby'] = "$table.occurrence_id";
		$direction          = 'past' === $range ? 'DESC' : 'ASC';
		if ( isset( $query->query['order'] ) && in_array( strtoupper( $query->query['order'] ), [ 'ASC', 'DESC' ], true ) ) {
			$direction = strtoupper( $query->query['order'] );
		}
		$sort   = $query->query['orderby'] ?? 'start-date';
		$fields = [
			'start-date' => "$table.start_date_utc",
			'end-date'   => "$table.end_date_utc",
			'title'      => "{$wpdb->posts}.post_title",
			'author'     => "{$wpdb->posts}.post_author",
			'date'       => "$table.start_date_utc",
		];
		if ( is_string( $sort ) && in_array( $sort, [ 'events-cats', 'tags' ], true ) ) {
			$taxonomy           = 'tags' === $sort ? 'post_tag' : 'tribe_events_cat';
			$clauses['orderby'] = $wpdb->prepare( "(SELECT GROUP_CONCAT(t.name ORDER BY t.name) FROM {$wpdb->term_relationships} tr JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id JOIN {$wpdb->terms} t ON t.term_id = tt.term_id WHERE tr.object_id = {$wpdb->posts}.ID AND tt.taxonomy = %s)", $taxonomy ) . " $direction";
		} else {
			$field              = is_string( $sort ) ? ( $fields[ $sort ] ?? "$table.start_date_utc" ) : "$table.start_date_utc";
			$clauses['orderby'] = "$field $direction";
		}
		$clauses['orderby'] .= ", $table.occurrence_id $direction";
		return $clauses;
	}

	/**
	 * Counts visible occurrences with the same search, author, taxonomy and range constraints.
	 *
	 * @since TBD
	 * @return array<string,int> All and publication-status counts.
	 */
	public function counts(): array {
		if ( null !== $this->counts ) {
			return $this->counts;
		}
		$this->counts = [];
		if ( ! $this->source ) {
			return $this->counts;
		}
		$args                   = $this->source->query_vars;
		$args['posts_per_page'] = 1;
		$args['paged']          = 1;
		$args['offset']         = 0;
		$args['no_found_rows']  = false;
		$args['fields']         = 'ids';
		$args['orderby']        = 'start-date';
		$statuses               = get_post_stati( [ 'show_in_admin_status_list' => true ] );
		$statuses['all']        = get_post_stati( [ 'show_in_admin_all_list' => true ] );
		foreach ( $statuses as $key => $status ) {
			$args['post_status']  = $status;
			$query                = $this->source->get( self::FLAG ) ? new Custom_Tables_Query( $args ) : new WP_Query( $args );
			$this->counts[ $key ] = (int) $query->found_posts;
		}
		return $this->counts;
	}
}
