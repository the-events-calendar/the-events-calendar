<?php
/**
 * An extension of the base WordPress WP_Query to redirect queries to the plugin custom tables.
 *
 * @since 6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query;

use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\WP_Query\Monitors\Custom_Tables_Query_Monitor;
use TEC\Events\Custom_Tables\V1\WP_Query\Monitors\WP_Query_Monitor;
use TEC\Events\Custom_Tables\V1\WP_Query\Repository\Custom_Tables_Query_Filters;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_Query;

/**
 * Class Custom_Tables_Query
 *
 * @since 6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */
class Custom_Tables_Query extends WP_Query {
	/**
	 * The last error string logged by any instance of the class.
	 *
	 * @since 6.0.4
	 *
	 * @var string
	 */
	protected static $last_error = '';

	/**
	 * A reference to the original `WP_Query` object this Custom Tables Query should use.
	 *
	 * @since 6.0.0
	 *
	 * @var WP_Query|null
	 */
	private $wp_query;

	/**
	 * Returns an instance of this class, built using the input `WP_Query` as a model.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query             $wp_query       A reference to the `WP_Query` instance that
	 *                                                   should be used as a model to build an instance
	 *                                                   of this class.
	 * @param ?array<string,mixed> $override_args  An array of query arguments to override
	 *                                                   the ones set from the original query.
	 *
	 * @return Custom_Tables_Query An instance of the class, built using the input `WP_Query`
	 *                             instance as a model.
	 */
	public static function from_wp_query( WP_Query $wp_query, ?array $override_args = null ) {
		// Initialize a new instance of the query.
		$ct_query = new self();
		$ct_query->init();
		$filtered_query       = $ct_query->filter_query_vars( wp_parse_args( (array) $override_args, $wp_query->query ) );
		$ct_query->query      = $filtered_query;
		$filtered_query_vars  = $ct_query->filter_query_vars( wp_parse_args( (array) $override_args, $wp_query->query_vars ) );
		$ct_query->query_vars = $filtered_query_vars;

		// Keep a reference to the original `WP_Query` instance.
		$ct_query->wp_query = $wp_query;

		if (
			isset( $wp_query->builder->filter_query )
			&& $wp_query->builder->filter_query instanceof Custom_Tables_Query_Filters
		) {
			/*
			 * If the original Query was built from a Repository, then there will be additional Query Filters that should
			 * be * applied to it. The Query Filters targeting the original Query either already fired or will not fire
			 * on the Custom Tables query. Here we get hold of the Query Filters set up by the Repository, redirect them
			 * to the Custom Tables query and set them up to avoid duplicated JOIN issues.
			 *
			 * @var Custom_Tables_Query_Filters $query_filters
			 */
			$query_filters = $wp_query->builder->filter_query;
			$query_filters->set_query( $ct_query );
		}

		$ct_query->wp_query = $wp_query;

		return $ct_query;
	}

	/**
	 * Adds an opportunity to filter the query_vars that will be used
	 * in the newly constructed instance of this object.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $query_vars The query variables, as created by WordPress or previous filtering
	 *                                        methods.
	 *
	 * @return array<string,mixed> The filtered query variables.
	 */
	private function filter_query_vars( $query_vars ) {
		/**
		 * Filters the query variables that will be used to build the Custom Tables Query.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string,mixed> $query_vars The query variables as set up by the Custom
		 *                                        Tables Query.
		 * @param Custom_Tables_Query $query A reference to the Custom Tables Query object that
		 *                                   is applying the filter.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_custom_tables_query_vars', $query_vars, $this );
	}

	/**
	 * Returns the last error string logged by any instance of the class.
	 *
	 * @since 6.0.4
	 *
	 * @return string The last error string logged by any instance of the class.
	 */
	public static function get_last_error(): string {
		return self::$last_error;
	}

	/**
	 * Overrides the base method to replace the Meta Query with one that will redirect
	 * to the plugin custom tables.
	 *
	 * The method will use the `posts_search` filter as an action to access the `WP_Query` instance
	 * `meta_query` property after it's been built and before it's used to produce Custom Fields related
	 * SQL.
	 *
	 * @since 6.0.0
	 *
	 * @return array<int|WP_Post> The query results, in the same format used by the `WP_Query::get_posts` method.
	 */
	public function get_posts() {
		$this->set( 'post_type', TEC::POSTTYPE );
		// Use the `posts_search` filter as an action to replace the hard-coded `$meta_query` reference.
		add_filter( 'posts_search', [ $this, 'replace_meta_query' ], 10, 2 );
		// Let's make sure filters are NOT suppressed as we'll need them.
		$this->set( 'suppress_filters', false );
		// While not ideal, this is the only way to intervene on `SELECT` in the `get_posts()` method.
		add_filter( 'posts_fields', [ $this, 'redirect_posts_fields' ], 10, 2 );
		// While not ideal, this is the only way to intervene on `GROUP BY` in the `get_posts()` method.
		add_filter( 'posts_groupby', [ $this, 'group_posts_by_occurrence_id' ], 10, 2 );
		add_filter( 'posts_where', [ $this, 'filter_by_date' ], 10, 2 );
		add_filter( 'posts_where', [ $this, 'filter_where' ], 20, 2 );
		add_filter( 'posts_where', [ $this, 'limit_to_single_occurrence_when_pro_inactive' ], 30, 2 );
		add_filter( 'posts_join', [ $this, 'join_occurrences_table' ], 10, 2 );
		// This is the last filter in the `WP_Query` class: use this as an action to clean up.
		add_filter( 'the_posts', [ $this, 'remove_late_filters' ], 10, 2 );
		add_filter( 'posts_orderby', [ $this, 'redirect_posts_orderby' ], 200, 2 );

		// This "parallel" query should not be manipulated by the WP_Query_Monitor.
		$monitor_ignore_flag          = WP_Query_Monitor::ignore_flag();
		$this->{$monitor_ignore_flag} = true;
		$this->set( $monitor_ignore_flag, true );

		// This parallel query should be modified by custom tables query modifiers, if any.
		/** @var Custom_Tables_Query_Monitor $monitor */
		$monitor = tribe( Custom_Tables_Query_Monitor::class );
		$monitor->attach( $this );

		// This "parallel" query should not be manipulated from other query managers.
		$this->set( 'tribe_suppress_query_filters', true );
		$this->tribe_suppress_query_filters = true;
		$this->set( 'tribe_include_date_meta', false );
		$this->tribe_include_date_meta = false;
		$this->set( 'cache_results', false );

		/**
		 * Fires before the Custom Tables query runs.
		 *
		 * @since 6.0.0
		 *
		 * @param Custom_Tables_Query $this A reference to this Custom Tables query.
		 */
		do_action( 'tec_events_custom_tables_v1_custom_tables_query_pre_get_posts', $this );

		$set_found_rows = empty( $this->get( 'no_found_rows', false ) );

		/*
		 * Since WordPress 6.1 query results are cached. To cache the results the `get_post` function
		 * will run too early for the post hydration logic to kick in in the `post_results` filter.
		 * We try to do the same hydration while fetching found rows; if the request is not to set
		 * found rows, then we'll do the hydration in the `post_results` filter and prevent query caching.
		 * This is not ideal, but will do for now as a temporary fix.
		 */
		if ( $set_found_rows ) {
			add_filter( 'found_posts', [ $this, 'hydrate_posts_on_found_rows' ], 0, 2 );
		} else {
			$this->set( 'cache_results', false );
			// When not calculating found_posts, hydrate via posts_results instead.
			add_filter( 'posts_results', [ $this, 'hydrate_posts_via_posts_results' ], 10, 2 );
		}

		$results = parent::get_posts();

		global $wpdb;
		self::$last_error = (string) $wpdb->last_error;

		$this->remove_filters();

		/**
		 * Fires after the Custom Tables Query ran.
		 *
		 * @since 6.0.0
		 *
		 * @param array|object|null   $results The query results.
		 * @param Custom_Tables_Query $this    A reference to this Custom Tables query.
		 */
		do_action( 'tec_events_custom_tables_v1_custom_tables_query_results', $results, $this );

		if ( $this->wp_query instanceof WP_Query ) {
			if ( $set_found_rows ) {
				// Avoid `SELECT FOUND_ROWS()` running twice. See #ECP-1360.
				add_filter( 'found_posts_query', [ $this, 'filter_found_posts_query' ], 10, 2 );
				$this->wp_query->found_posts   = $this->found_posts;
				$this->wp_query->max_num_pages = $this->max_num_pages;
			}

			// Set the request SQL that actually ran to allow easier debugging of the query.
			$this->wp_query->request = $this->request;
		}

		return $results;
	}

	/**
	 * Replaces the `WP_Meta_Query` instance built in the `WP_Query::get_posts` method with an instance of
	 * the `WP_Meta_Query` extension that will redirect some custom fields queries to the plugin custom tables.
	 *
	 * This method is expected to be hooked to the `posts_search` hook in the `WP_Query::get_posts` method.
	 * The method will not change
	 *
	 * @since 6.0.0
	 *
	 * @param string   $search    The WHERE clause as produced by the `WP_Query` instance.
	 * @param WP_Query $wp_query  A reference to the `WP_Query` instance whose search WHERE clause is currently being
	 *                            filtered.
	 *
	 * @return string The WHERE clause as produced by the `WP_Query` instance, untouched by the method.
	 */
	public function replace_meta_query( $search, $wp_query ) {
		if ( $wp_query !== $this ) {
			// Only target the class own instance.
			return $search;
		}

		/**
		 * This instance might have been built from a `WP_Query` instance or on its own.
		 * Depending on that, change the "source" query.
		 */
		$source_query = $this->wp_query instanceof WP_Query ? $this->wp_query : $this;

		// Let's not run again for this instance and allow garbage collection of this object.
		remove_filter( 'posts_search', [ $this, 'replace_meta_query' ] );

		if ( ! (
			isset( $source_query->meta_query )
			&& $source_query instanceof WP_Query
			&& $source_query->meta_query instanceof \WP_Meta_Query )
		) {
			// Let's not try and replace something that was not there to begin with.
			return $search;
		}

		$meta_queries = $source_query->meta_query->queries ?? [];

		$this->meta_query = new Custom_Tables_Meta_Query( $meta_queries );

		return $search;
	}

	/**
	 * Redirects the `SELECT` part of the query to fetch from the Occurrences table.
	 *
	 * @since 6.0.0
	 *
	 * @param string    $request_fields The original `SELECT` SQL.
	 * @param ?WP_Query $query          A reference to the `WP_Query` instance currently being
	 *                                      filtered.
	 *
	 * @return string The filtered `SELECT` clause.
	 */
	public function redirect_posts_fields( $request_fields, $query = null ) {
		if ( $this !== $query ) {
			return $request_fields;
		}

		remove_filter( 'posts_fields', [ $this, 'redirect_posts_fields' ] );

		/**
		 * Filters the table field, including the table name, that should be
		 * used to identify distinct Occurrences results in the context
		 * of a Custom Tables Query.
		 *
		 * @since 6.0.0
		 *
		 * @param string $request_fields The Query fields request, e.g. `ids`.
		 */
		$request_fields = apply_filters( 'tec_events_custom_tables_v1_occurrence_select_fields', $request_fields );

		return $request_fields;
	}

	/**
	 * Changes the `GROUP BY` clause for posts to avoid the collapse of results on the post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param string    $groupby  The original `GROUP BY` SQL clause.
	 * @param ?WP_Query $query    A reference to the `WP_Query` instance currently being filtered.
	 *
	 * @return string The updated `GROUP BY` SQL clause.
	 */
	public function group_posts_by_occurrence_id( $groupby, $query = null ) {
		if ( $this !== $query ) {
			return $groupby;
		}

		remove_filter( 'posts_groupby', [ $this, 'group_posts_by_occurrence_id' ] );

		$occurrences = Occurrences::table_name( true );
		global $wpdb;

		// Group by the occurrence ID, not the post ID.
		return str_replace( "$wpdb->posts.ID", "$occurrences.occurrence_id", $groupby );
	}

	/**
	 * Redirects the the table and fields used in the `ORDER BY` clause of the query to the Custom Tables,
	 * if required.
	 *
	 * @since 6.0.0
	 *
	 * @param string $orderby The original `ORDER BY` SQL clause.
	 *
	 * @return string|false The redirected `ORDER BY` field, `false` on failure.
	 */
	protected function parse_orderby( $orderby ) {
		global $wpdb;
		$occurrences = Occurrences::table_name( true );

		$orderby = str_replace( [ $occurrences . '.', $wpdb->posts . '.' ], '', $orderby );

		// Let's try to handle some specific cases first.
		switch ( $orderby ) {
			case 'start_date':
			case 'event_date':
				$parsed = $occurrences . '.start_date';
				break;
			case 'end_date':
			case 'event_end_date':
				$parsed = $occurrences . '.end_date';
				break;
			case 'start_date_utc':
			case 'event_date_utc':
				$parsed = $occurrences . '.start_date_utc';
				break;
			case 'end_date_utc':
			case 'event_end_date_utc':
				$parsed = $occurrences . '.end_date_utc';
				break;
			case 'duration':
			case 'event_duration':
				$parsed = $occurrences . '.duration';
				break;
			case 'ID':
			case $wpdb->posts . '.ID':
				// If the order is by post ID, order by post ID and occurrence ID.
				$original_order_by   = $this->query_vars['orderby'] ?? [];
				$normalized_order_by = tribe_normalize_orderby( $original_order_by );
				$occurrences         = Occurrences::table_name( true );
				$order               = $normalized_order_by['ID'] ?? $normalized_order_by[ $wpdb->posts . '.ID' ] ?? 'DESC';
				$order               = $this->sanitize_order( $order );

				// The second `order` is omitted: it will be added by the following `parse_order` call.
				$parsed = "ID $order, $occurrences.occurrence_id";
				break;
			case 'none':
			case 'rand':
				// Fast-track the `none` and `rand` order bys.
				return parent::parse_orderby( $orderby );
			default:
				$parsed = null;
		}

		if ( ! empty( $parsed ) ) {
			return $parsed;
		}

		// Handle order imposed by a meta value.
		$meta_query_clauses = $this->meta_query->get_clauses();

		$meta_query_orderby = $orderby;
		if ( $orderby === 'meta_value' ) {
			// Handle the case where the order is by a meta key and value couple.
			$meta_query_orderby = $this->get( 'meta_key' );
			// If we have a list of meta query args.
			if ( ! $meta_query_orderby && isset( reset( $meta_query_clauses )['original_meta_key'] ) ) {
				$meta_query_orderby = reset( $meta_query_clauses )['original_meta_key'];
			}
		} elseif ( isset( $meta_query_clauses[ $orderby ]['original_meta_key'] ) ) {
			// Handle the case where the order is by the meta query key.
			$meta_query_orderby = $meta_query_clauses[ $orderby ]['original_meta_key'];
		}

		if ( count( $meta_query_clauses ) ) {
			$map = Redirection_Schema::get_filtered_meta_key_redirection_map();

			foreach ( $meta_query_clauses as $meta_query_clause ) {
				$original_meta_key = $meta_query_clause['original_meta_key'] ?? null;

				if ( $meta_query_orderby !== $original_meta_key ) {
					continue;
				}

				$matching_mapping = $map[ $original_meta_key ] ?? null;

				if ( $matching_mapping === null || ! isset( $map[ $original_meta_key ]['column'] ) ) {
					continue;
				}

				return $occurrences . '.' . $map[ $original_meta_key ]['column'];
			}
		}

		// Let the parent handle the rest or return the input unchanged.
		return parent::parse_orderby( $orderby );
	}

	/**
	 * Adds a filter for TEC custom queries in order to further parse the `WHERE` statements.
	 *
	 * @since 6.0.0
	 *
	 * @param string        $where          The input `WHERE` clause, as built by the `WP_Query`
	 *                                      class code.
	 * @param WP_Query|null $query          A reference to the `WP_Query` instance currently being filtered.
	 *
	 * @return string The `WHERE` SQL clause, modified to be date-bound, if required.
	 */
	public function filter_where( $where, $query ) {
		if ( ! $query instanceof WP_Query ) {
			return $where;
		}

		/**
		 * Filters the `WHERE` statement produced by the Custom Tables Query.
		 *
		 * @since 6.0.0
		 *
		 * @param string              $where    The `WHERE` statement produced by the Custom Tables Query.
		 * @param WP_Query            $query    The query object being filtered.
		 * @param Custom_Tables_Query $ct_query A reference to the Custom Tables Query instance that is applying
		 *                                      the filter.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_custom_tables_query_where', $where, $query, $this );
	}

	/**
	 * Updates the `WHERE` statements to ensure any Event Query is date-bound.
	 *
	 * @since 6.0.0
	 *
	 * @param string        $where          The input `WHERE` clause, as built by the `WP_Query`
	 *                                      class code.
	 * @param WP_Query|null $query          A reference to the `WP_Query` instance currently being filtered.
	 *
	 * @return string The `WHERE` SQL clause, modified to be date-bound, if required.
	 */
	public function filter_by_date( $where, $query = null ) {
		if ( $this !== $query ) {
			return $where;
		}

		remove_filter( 'posts_where', [ $this, 'filter_by_date' ] );

		if (
			$query instanceof WP_Query
			&& $query->get( 'eventDate', null )
			&& $query->is_single()
		) {
			/**
			 * When dealing with DATE() casting on MySQL we need to be careful since `2022-08` would not validate as a
			 * date in any version of MySQL, which wouldn't be a problem on its own but on version 8 of MySQL it will
			 * throw an error, which is a blocker for functionality.
			 *
			 * So this particular query can only be executed when dealing with the Single view of a Series, which only
			 * allows fully formatted dates.
			 *
			 * On Month view it will pass `YYYY-MM` kind of formatting, which will throw the error.
			 */
			$where .= sprintf(
				' AND CAST(%1$s.%2$s AS DATE) = \'%3$s\'',
				Occurrences::table_name( true ),
				'start_date',
				sanitize_text_field( $query->get( 'eventDate' ) )
			);
		}

		return $where;
	}

	/**
	 * Limits results to a single occurrence per event when Events Calendar Pro is not active.
	 *
	 * When ECP is deactivated but recurring events were previously created, the occurrences table
	 * may contain multiple rows per post_id. This filter ensures only one occurrence per event is
	 * returned when Pro is inactive, preventing duplicate event displays.
	 *
	 * The subquery respects date filtering by only selecting from occurrences that match the
	 * main query's date criteria, ensuring that for queries like "events after now", we get
	 * the first *future* occurrence, not the globally first occurrence by ID.
	 *
	 * @since TBD
	 *
	 * @param string        $where The input `WHERE` clause, as built by the `WP_Query` class code.
	 * @param WP_Query|null $query A reference to the `WP_Query` instance currently being filtered.
	 *
	 * @return string The `WHERE` SQL clause, modified to limit to one occurrence per post when Pro is inactive.
	 */
	public function limit_to_single_occurrence_when_pro_inactive( $where, $query = null ) {
		if ( $this !== $query ) {
			return $where;
		}

		remove_filter( 'posts_where', [ $this, 'limit_to_single_occurrence_when_pro_inactive' ], 30 );

		// If Events Calendar Pro is active, it will handle multiple occurrences properly.
		if ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
			return $where;
		}

		global $wpdb;
		$occurrences = Occurrences::table_name( true );

		/**
		 * Extract date filtering conditions from the query's meta_query.
		 * At this point in the filter chain, meta_query conditions haven't been added to WHERE yet,
		 * so we need to build them from the meta_query array directly.
		 */
		$date_conditions = $this->build_date_conditions_from_meta_query( $query );

		// Check for List View via eventDisplay parameter.
		$event_display = $this->get( 'eventDisplay', false );

		// For List View, when no date conditions are in meta_query, apply a future events filter
		// This ensures we select the first future occurrence, not the globally earliest one.
		if ( $event_display === 'list' && empty( $date_conditions ) ) {
			$current_time    = current_time( 'mysql' );
			$date_conditions = " AND occ.end_date > '{$current_time}'";
		}

		// Store the date conditions in the query object so hydration can use them.
		$this->set( '_tec_occurrence_date_conditions', $date_conditions );

		/**
		 * If no date conditions were found in meta_query or ends_after, use simple limiting.
		 * The main query's WHERE clause will handle date filtering via date_overlaps.
		 */
		$use_simple_limit = empty( $date_conditions );

		// Store whether we're using simple limit so hydration knows whether to apply date filtering.
		$this->set( '_tec_used_simple_limit', $use_simple_limit );

		/**
		 * When Pro is not active, limit to the earliest occurrence for each post_id
		 * that matches the date criteria. This prevents showing the same event multiple
		 * times when recurring event data exists in the occurrences table but Pro is deactivated.
		 *
		 * We select by earliest start_date, not by MIN(occurrence_id), since occurrence IDs
		 * may not be in chronological order. By including the date conditions in the subquery,
		 * we ensure that for queries like "events after now", we get the first future occurrence.
		 *
		 * This uses a self-join approach compatible with MySQL 5.7+.
		 */

		if ( $use_simple_limit ) {
			// Simple limit: Get the earliest occurrence per post_id that matches the main WHERE clause
			// We need to incorporate the date filtering from the main WHERE clause into the subquery
			// Extract date conditions from the current WHERE clause.
			$where_date_conditions = '';
			if ( preg_match( '/wp_tec_occurrences\.(start_date|end_date)\s*[<>=]+\s*\'[^\']+\'/i', $where, $matches ) ) {
				// Extract all date conditions that reference wp_tec_occurrences table.
				preg_match_all( '/wp_tec_occurrences\.(start_date|end_date)\s*[<>=]+\s*\'[^\']+\'/i', $where, $all_matches );
				if ( ! empty( $all_matches[0] ) ) {
					foreach ( $all_matches[0] as $condition ) {
						// Convert wp_tec_occurrences to o1/o2 aliases.
						$where_date_conditions .= ' AND ' . str_replace( 'wp_tec_occurrences', 'o1', $condition );
					}
				}
			}

			$where .= " AND {$occurrences}.occurrence_id IN (
				SELECT o1.occurrence_id
				FROM {$occurrences} o1
				LEFT JOIN {$occurrences} o2 ON o1.post_id = o2.post_id
					AND (o2.start_date < o1.start_date OR (o2.start_date = o1.start_date AND o2.occurrence_id < o1.occurrence_id))
					{$where_date_conditions}
				WHERE o2.occurrence_id IS NULL
				{$where_date_conditions}
			)";
		} else {
			// Date-aware limit: include date conditions in the subquery to select the right occurrence.
			// This ensures that for queries like "events after now", we get the first future occurrence.
			$o1_date_conditions = str_replace( 'occ.', 'o1.', $date_conditions );
			$o2_date_conditions = str_replace( 'occ.', 'o2.', $date_conditions );

			$where .= " AND {$occurrences}.occurrence_id IN (
				SELECT o1.occurrence_id
				FROM {$occurrences} o1
				LEFT JOIN {$occurrences} o2 ON o1.post_id = o2.post_id
					AND (o2.start_date < o1.start_date OR (o2.start_date = o1.start_date AND o2.occurrence_id < o1.occurrence_id))
					AND 1=1 {$o2_date_conditions}
				WHERE o2.occurrence_id IS NULL
				AND 1=1 {$o1_date_conditions}
			)";
		}

		return $where;
	}

	/**
	 * Builds date filtering conditions from the query's meta_query array.
	 *
	 * This is necessary because at the time `posts_where` filters run, the meta_query
	 * conditions haven't been converted to SQL yet. We need to extract them early
	 * to use in our occurrence-limiting subquery.
	 *
	 * @since TBD
	 *
	 * @param WP_Query|null $query A reference to the `WP_Query` instance.
	 *
	 * @return string SQL conditions for the subquery, prefixed with 'occ.' table alias.
	 */
	private function build_date_conditions_from_meta_query( $query ) {
		if ( ! $query || ! isset( $query->meta_query ) || ! $query->meta_query instanceof \WP_Meta_Query ) {
			return '';
		}

		$date_conditions = '';

		// Map meta keys to occurrence table columns.
		$meta_key_map = [
			'_EventStartDate'    => 'start_date',
			'_EventEndDate'      => 'end_date',
			'_EventStartDateUTC' => 'start_date_utc',
			'_EventEndDateUTC'   => 'end_date_utc',
		];

		// Extract conditions from meta_query.
		$queries = $query->meta_query->queries;

		foreach ( $queries as $query_key => $query_item ) {
			if ( ! is_array( $query_item ) ) {
				continue;
			}

			// Check if this is the actual query or metadata (like 'relation').
			if ( $query_key === 'relation' ) {
				continue;
			}

			// Get the meta key - check both 'original_meta_key' (set by Custom Tables) and 'key'.
			$meta_key = $query_item['original_meta_key'] ?? $query_item['key'] ?? null;

			if ( ! $meta_key ) {
				continue;
			}

			// Only process date-related meta keys.
			if ( ! isset( $meta_key_map[ $meta_key ] ) ) {
				continue;
			}

			$column  = $meta_key_map[ $meta_key ];
			$compare = $query_item['compare'] ?? '=';
			$value   = $query_item['value'] ?? '';

			// Sanitize and escape the value.
			$value = esc_sql( $value );

			// Build the condition.
			$date_conditions .= " AND occ.{$column} {$compare} '{$value}'";
		}

		return $date_conditions;
	}

	/**
	 * Filters the Query JOIN clause to JOIN on the Occurrences table if the Custom
	 * Tables Meta Query did not do that already.
	 *
	 * @since 6.0.0
	 *
	 * @param string   $join   The input JOIN query, as parsed and built by the WordPress
	 *                         Query.
	 * @param WP_Query $query  A reference to the WP Query object that is currently filtering
	 *                         its JOIN query.
	 *
	 * @return string The filtered JOIN query, if required.
	 */
	public function join_occurrences_table( $join, $query ) {
		if ( $this !== $query ) {
			return $join;
		}

		remove_filter( 'posts_join', [ $this, 'join_occurrences_table' ] );

		$occurrences = Occurrences::table_name( true );

		if (
			$query->meta_query instanceof Custom_Tables_Meta_Query
			&& $query->meta_query->did_join_table( $occurrences )
		) {
			// The Custom Tables Meta Query already joined on the Occurrences table, we're ok.
			return $join;
		}

		global $wpdb;
		$join_clause = "JOIN {$occurrences} ON {$wpdb->posts}.ID = {$occurrences}.post_id";

		if ( strpos( $join, $join_clause ) === false ) {
			// Let's add the JOIN clause only if we did not already.
			$join .= ' ' . $join_clause;
		}

		return $join;
	}

	/**
	 * Implementation of the magic method to check if a property is set on this object or not.
	 *
	 * @since 6.0.0
	 *
	 * @param string $name The property to check for.
	 *
	 * @return bool Whether a property is set on this object or not.
	 */
	public function __isset( $name ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		return parent::__isset( $name );
	}

	/**
	 * Returns a reference to the `WP_Query` instance this instance is wrapping.
	 *
	 * @since 6.0.0
	 *
	 * @return WP_Query|null A reference to the `WP_Query` instance this object is wrapping.
	 */
	public function get_wp_query() {
		return $this->wp_query;
	}

	/**
	 * Short-circuits the controlled `WP_Query` instance query to get the number of found results
	 * to avoid it from running twice.
	 *
	 * The Custom Tables query will pre-fill the results on the `posts_pre_query` filter and will run,
	 * in that context, a query to get the posts and the found rows. The `WP_Query` instance whose posts
	 * are pre-filled, will attempt to run the query to get the found rows again. This method will intercept
	 * that second `SELECT FOUND_ROWS()` query to pre-fill it with a result the Custom Tables query already
	 * has.
	 *
	 * @since 6.0.4
	 *
	 * @param string   $found_posts_query The SQL query that would run to fill in the `found_posts` property of the
	 *                                    `WP_Query` instance.
	 * @param WP_Query $query             The `WP_Query` instance that is currently filtering its `found_posts`
	 *                                    property.
	 *
	 * @return string The filtered SQL query that will run to fill in the `found_posts` property of the `WP_Query`
	 *                instance.
	 */
	public function filter_found_posts_query( $found_posts_query, $query ) {
		if ( $this->wp_query !== $query ) {
			return $found_posts_query;
		}

		remove_filter( 'found_posts_query', [ $this, 'filter_found_posts_query' ] );

		return 'SELECT ' . $this->found_posts;
	}

	/**
	 * Removes all the filters the Custom Tables Query has added to filter its own inner workings while
	 * pre-filling the results in the `posts_pre_query` filter.
	 *
	 * @since 6.0.4
	 *
	 * @return void Lingering filters will be removed.
	 */
	protected function remove_filters(): void {
		remove_filter( 'posts_search', [ $this, 'replace_meta_query' ] );
		remove_filter( 'posts_fields', [ $this, 'redirect_posts_fields' ] );
		remove_filter( 'posts_groupby', [ $this, 'group_posts_by_occurrence_id' ] );
		remove_filter( 'posts_where', [ $this, 'filter_by_date' ] );
		remove_filter( 'posts_where', [ $this, 'filter_where' ] );
		remove_filter( 'posts_where', [ $this, 'limit_to_single_occurrence_when_pro_inactive' ] );
		remove_filter( 'posts_join', [ $this, 'join_occurrences_table' ] );
		remove_filter( 'the_posts', [ $this, 'remove_late_filters' ] );
		remove_filter( 'found_posts', [ $this, 'hydrate_posts_on_found_rows' ], 0 );
		remove_filter( 'posts_orderby', [ $this, 'redirect_posts_orderby' ], 200 );
	}

	/**
	 * Removes late filters that are required after the `posts_pre_query` filter.
	 *
	 * @since 6.0.4
	 *
	 * @param array    $the_posts The array of posts that will be returned by the `WP_Query` instance.
	 * @param WP_Query $query     WP_Query The `WP_Query` instance that is currently filtering its `the_posts` property.
	 *
	 * @return array The filtered array of posts that will be returned by the `WP_Query` instance, not modified by
	 *               this filter.
	 */
	public function remove_late_filters( $the_posts, $query ) {
		if ( $query !== $this->wp_query ) {
			return $the_posts;
		}

		remove_filter( 'found_posts_query', [ $this, 'filter_found_posts_query' ] );

		return $the_posts;
	}

	/**
	 * Attempt an early hydration of the post caches when fetching the found rows, this method
	 * is using the `found_posts` filter as an action.
	 *
	 * @since 6.0.4
	 *
	 * @param int      $found_posts The number of found posts, not used by this method.
	 * @param WP_Query $query       The `WP_Query` instance that is currently filtering its `found_posts` property.
	 *
	 * @return int The number of found posts, not modified by this method.
	 */
	public function hydrate_posts_on_found_rows( $found_posts, $query ) {
		if ( $query !== $this ) {
			return $found_posts;
		}

		remove_filter( 'found_posts', [ $this, 'hydrate_posts_on_found_rows' ], 0 );

		if ( empty( $found_posts ) || ! is_array( $query->posts ) ) {
			return $found_posts;
		}

		/**
		 * Filters the posts that will be hydrated by the Custom Tables Query early, before
		 * query caching introduced in WordPress 6.1 kicks in.
		 *
		 * @since 6.0.4
		 *
		 * @param array               $posts The posts that will be hydrated by the Custom Tables Query early.
		 * @param Custom_Tables_Query $this  The Custom Tables Query instance.
		 */
		$query->posts = apply_filters( 'tec_events_custom_tables_v1_custom_tables_query_hydrate_posts', $query->posts, $query );

		return $found_posts;
	}

	/**
	 * Hydrates the posts returned by the query via the posts_results filter.
	 *
	 * This is used when the query has no_found_rows set to true, meaning the found_posts
	 * filter won't run. In this case, we hydrate via posts_results instead.
	 *
	 * @since TBD
	 *
	 * @param array    $posts The posts returned by the query.
	 * @param WP_Query $query The query object.
	 *
	 * @return array The hydrated posts.
	 */
	public function hydrate_posts_via_posts_results( $posts, $query ) {
		// The posts_results filter doesn't pass $this, but rather returns from parent::get_posts()
		// So we can't do strict object comparison. Instead, just proceed with hydration.

		remove_filter( 'posts_results', [ $this, 'hydrate_posts_via_posts_results' ], 10 );

		if ( empty( $posts ) || ! is_array( $posts ) ) {
			return $posts;
		}

		/**
		 * Filters the posts that will be hydrated by the Custom Tables Query.
		 *
		 * @since TBD
		 *
		 * @param array               $posts The posts that will be hydrated by the Custom Tables Query.
		 * @param Custom_Tables_Query $this  The Custom Tables Query instance.
		 */
		$posts = apply_filters( 'tec_events_custom_tables_v1_custom_tables_query_hydrate_posts', $posts, $this );

		// Pass $this instead of $query, because $this has the date conditions stored.
		return $posts;
	}

	/**
	 * Filters the `ORDER` section of the query to redirect the fields that require it to the custom tables.
	 *
	 * The `parse_orderby` method might have not completely taken care of this redirection if the query did
	 * not originally specify any `orderby` or later filters have modified the `ORDER BY` section of the query
	 * further.
	 *
	 * @since 6.0.4
	 *
	 * @param string   $posts_orderby The `ORDER` section of the query.
	 * @param WP_Query $query         The `WP_Query` instance that is currently filtering its `posts_orderby` property.
	 *
	 * @return string The filtered `ORDER` section of the query.
	 */
	public function redirect_posts_orderby( $posts_orderby, $query ) {
		if ( $query !== $this ) {
			return $posts_orderby;
		}

		remove_filter( 'posts_orderby', [ $this, 'redirect_posts_orderby' ], 200 );

		if ( ! is_string( $posts_orderby ) || trim( $posts_orderby ) === '' ) {
			return $posts_orderby;
		}

		$redirected_orderbys = '';
		$orderbys            = explode( ',', $posts_orderby );

		foreach ( $orderbys as $orderby_frag ) {
			// Fast-track the `rand` order, no need to redirect anything.
			// Only allow the exact RAND() function to prevent SQL injection.
			$trimmed_frag = trim( $orderby_frag );
			if ( preg_match( '/^rand\s*\(\s*\)$/i', $trimmed_frag ) ) {
				// Use hardcoded RAND() to prevent SQL injection
				$redirected_orderbys .= ( $redirected_orderbys === '' ? '' : ', ' ) . 'RAND()';
				continue;
			}

			// Each `ORDER BY` entry could specify an order (DESC|ASC) or not.
			if ( preg_match( '~(?<orderby>.+)\s+(?<order>ASC|DESC)$~i', $orderby_frag, $m ) ) {
				$orderby = trim( $m['orderby'] );
				$order   = $this->sanitize_order( $m['order'] );
			} else {
				// Follow the WordPress default and use DESC if no order is specified.
				$orderby = $orderby_frag;
				$order   = 'DESC';
			}

			if ( strpos( $redirected_orderbys, $orderby ) !== false ) {
				// The field has been already added to the redirected `ORDER BY` clause.
				continue;
			}

			$parsed_orderby = $this->parse_orderby( (string) $orderby );

			if ( ! $parsed_orderby ) {
				continue;
			}

			if ( strpos( $redirected_orderbys, $parsed_orderby ) !== false ) {
				// The field has been already added to the redirected `ORDER BY` clause.
				continue;
			}

			$redirected_orderbys .= $redirected_orderbys === '' ?
				$parsed_orderby . ' ' . $order
				: ', ' . $parsed_orderby . ' ' . $order;
		}

		return $redirected_orderbys;
	}

	/**
	 * Sanitizes the order direction.
	 *
	 * @since 6.7.0
	 *
	 * @param string $order The order direction to sanitize.
	 *
	 * @return string The sanitized order direction.
	 */
	protected function sanitize_order( $order ) {
		return strtoupper( trim( $order ) ) === 'DESC' ? 'DESC' : 'ASC';
	}
}
