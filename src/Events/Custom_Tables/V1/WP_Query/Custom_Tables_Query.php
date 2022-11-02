<?php
/**
 * An extension of the base WordPress WP_Query to redirect queries to the plugin custom tables.
 *
 * @since   6.0.0
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
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */
class Custom_Tables_Query extends WP_Query {
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
	 * @param  WP_Query                  $wp_query       A reference to the `WP_Query` instance that
	 *                                                   should be used as a model to build an instance
	 *                                                   of this class.
	 * @param  array<string,mixed>|null  $override_args  An array of query arguments to override
	 *                                                   the ones set from the original query.
	 *
	 * @return Custom_Tables_Query An instance of the class, built using the input `WP_Query`
	 *                             instance as a model.
	 */
	public static function from_wp_query( WP_Query $wp_query, array $override_args = null ) {
		// Initialize a new instance of the query.
		$ct_query = new self();
		$ct_query->init();
		$filtered_query = $ct_query->filter_query_vars( wp_parse_args( (array) $override_args, $wp_query->query ) );
		$ct_query->query = $filtered_query;
		$filtered_query_vars = $ct_query->filter_query_vars( wp_parse_args( (array) $override_args, $wp_query->query_vars ) );
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
	 * @param $query_vars array<string,mixed> The query variables, as created by WordPress or previous filtering
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
		add_filter( 'posts_orderby', [ $this, 'order_by_occurrence_id' ], 100, 2 );
		add_filter( 'posts_where', [ $this, 'filter_by_date' ], 10, 2 );
		add_filter( 'posts_where', [ $this, 'filter_where' ], 10, 2 );
		add_filter( 'posts_join', [ $this, 'join_occurrences_table' ], 10, 2 );
		// This is the last filter in the `WP_Query` class: use this as an action to clean up.
		add_filter( 'the_posts', [ $this, 'remove_late_filters' ], 10, 2 );

		// This "parallel" query should not be manipulated by the WP_Query_Monitor.
		$monitor_ignore_flag          = WP_Query_Monitor::ignore_flag();
		$this->{$monitor_ignore_flag} = true;
		$this->set( $monitor_ignore_flag, true );

		// This parallel query should be modified by custom tables query modifiers, if any.
		/** @var Custom_Tables_Query_Monitor $monitor */
		$monitor = tribe(Custom_Tables_Query_Monitor::class);
		$monitor->attach( $this );

        // This "parallel" query should not be manipulated from other query managers.
		$this->set( 'tribe_suppress_query_filters', true );
		$this->tribe_suppress_query_filters = true;
		$this->set( 'tribe_include_date_meta', false );
		$this->tribe_include_date_meta = false;

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
		}

		$results = parent::get_posts();

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
				$this->wp_query->found_posts = $this->found_posts;
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
	 * @param  string    $search    The WHERE clause as produced by the `WP_Query` instance.
	 * @param  WP_Query  $wp_query  A reference to the `WP_Query` instance whose search WHERE clause is currently being
	 *                              filtered.
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

		$meta_queries = isset( $source_query->meta_query->queries ) ? $source_query->meta_query->queries : [];

		$this->meta_query = new Custom_Tables_Meta_Query( $meta_queries );

		return $search;
	}

	/**
	 * Redirects the `SELECT` part of the query to fetch from the Occurrences table.
	 *
	 * @since 6.0.0
	 *
	 * @param  string        $request_fields The original `SELECT` SQL.
	 * @param  WP_Query|null $query          A reference to the `WP_Query` instance currently being
	 *                                 filtered.
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
	 * @param  string         $groupby  The original `GROUP BY` SQL clause.
	 * @param  WP_Query|null  $query    A reference to the `WP_Query` instance currently being filtered.
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
	 * Replace the SQL clause that would order posts by ID to order them by Occurrence ID.
	 *
	 * The correct ORDER BY clause will be built from the redirection map.
	 *
	 * @since 6.0.0
	 *
	 * @param string        $order_by          The input `ORDER BY` SQL clause, as produced by the
	 *                                         `WP_Query` class code.
	 * @param WP_Query|null $query             A reference to the `WP_Query` instance currently being filtered.
	 *
	 * @return string The filtered `ORDER BY` SQL clause, redirecting `wp_posts.ID` to Occurrence ID,
	 *                if required.
	 */
	public function order_by_occurrence_id( $order_by, $query = null ) {
		if ( $this !== $query ) {
			return $order_by;
		}

		remove_filter( 'posts_orderby', [ $this, 'order_by_occurrence_id' ], 100 );

		$original_order_by = $this->wp_query->query_vars['orderby'] ?? [];
		$normalized_order_by = tribe_normalize_orderby( $original_order_by );
		$occurrences = Occurrences::table_name( true );

		if ( ! empty( $normalized_order_by ) ) {
			global $wpdb;

			// Rebuild the ORDER string based on the custom tables redirection.
			$buffer = [];
			$meta_query_clauses = $this->meta_query->get_clauses();
			foreach ( $normalized_order_by as $original_key => $direction ) {
				if ( $original_key === 'meta_value' ) {
					// Handle queries with on meta value.
					$original_key = array_key_first( $meta_query_clauses );
				}

				if ( in_array( $original_key, [ 'ID', $wpdb->posts . '.ID' ], true ) ) {
					// If the order is by post ID, order by post ID and occurrence ID.
					$buffer[] = "ID $direction, $occurrences.occurrence_id $direction";
					continue;
				}

				if ( ! ( is_string( $original_key ) && isset( $meta_query_clauses[ $original_key ] ) ) ) {
					// Not a key we redirect or handle.
					$buffer[] = $original_key . ' ' . $direction;
					continue;
				}

				$alias = $meta_query_clauses[ $original_key ]['alias'];
				$key = $meta_query_clauses[ $original_key ]['key'];
				$cast = ! empty( $meta_query_clauses[ $original_key ]['cast'] ) ?
					$meta_query_clauses[ $original_key ]['cast'] : 'CHAR';
				$buffer[] = sprintf( "CAST(%s.%s AS %s) %s", $alias, $key, $cast, $direction );
			}
			$order_by = implode( ', ', $buffer );
		}

		// Handle some curated keys.
		$order_by = str_replace(
			[ 'event_date', 'event_date_utc', 'event_duration' ],
			[ $occurrences . '.start_date', $occurrences . '.start_date_utc', $occurrences . '.duration' ],
			$order_by
		);

		return $order_by;
	}

	/**
	 * Intercept appropriate order by fields and map to our new occurrence fields.
	 *
	 * @since 6.0.0
	 *
	 * @inheritDoc
	 *
	 * @return string The redirected ORDER clause, if required.
	 */
	protected function parse_orderby( $orderby ) {
		if ( 'meta_value' !== $orderby || ! isset( $this->query['meta_key'] ) ) {
			return parent::parse_orderby( $orderby );
		}

		$map = Redirection_Schema::get_filtered_meta_key_redirection_map();

		if ( ! isset( $map[ $this->query['meta_key'] ] ) ) {
			return parent::parse_orderby( $orderby );
		}

		$redirection = $map[ $this->query['meta_key'] ];

		return sprintf( '%1$s.%2$s', $redirection['table'], $redirection['column'] );
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
		 * @param Custom_Tables_Query $ct_query A reference to the Custom Tables Query instance that is appplying
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
		$str = "JOIN {$occurrences} ON {$wpdb->posts}.ID = {$occurrences}.post_id";

		if ( strpos( $join, $str ) === false ) {
			// Let's add the JOIN clause only if we did not already.
			$join .= ' ' . $str;
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
	public function __isset( $name ) {
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
	 * @since TBD
	 *
	 * @param string $found_posts_query The SQL query that would run to fill in the `found_posts` property of the
	 *                                  `WP_Query` instance.
	 * @param        $query             WP_Query The `WP_Query` instance that is currently filtering its `found_posts`
	 *                                  property.
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
	 * @since TBD
	 *
	 * @return void Lingering filters will be removed.
	 */
	protected function remove_filters(): void {
		remove_filter( 'posts_search', [ $this, 'replace_meta_query' ] );
		remove_filter( 'posts_fields', [ $this, 'redirect_posts_fields' ] );
		remove_filter( 'posts_groupby', [ $this, 'group_posts_by_occurrence_id' ] );
		remove_filter( 'posts_orderby', [ $this, 'order_by_occurrence_id' ], 100 );
		remove_filter( 'posts_where', [ $this, 'filter_by_date' ] );
		remove_filter( 'posts_where', [ $this, 'filter_where' ] );
		remove_filter( 'posts_join', [ $this, 'join_occurrences_table' ] );
		remove_filter( 'the_posts', [ $this, 'remove_late_filters' ] );
		remove_filter( 'found_posts', [ $this, 'hydrate_posts_on_found_rows' ], 0 );
	}

	/**
	 * Removes late filters that are required after the `posts_pre_query` filter.
	 *
	 * @since TBD
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
	 * @since TBD
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
		 * @since TBD
		 *
		 * @param array               $posts The posts that will be hydrated by the Custom Tables Query early.
		 * @param Custom_Tables_Query $this  The Custom Tables Query instance.
		 */
		$query->posts = apply_filters( 'tec_events_custom_tables_v1_custom_tables_query_hydrate_posts', $query->posts, $query );

		return $found_posts;
	}
}
