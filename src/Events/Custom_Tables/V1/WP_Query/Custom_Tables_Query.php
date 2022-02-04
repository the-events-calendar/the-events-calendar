<?php
/**
 * An extension of the base WordPress WP_Query to redirect queries to the plugin custom tables.
 *
 * @since   TBD
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
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */
class Custom_Tables_Query extends WP_Query {
	/**
	 * A reference to the original `WP_Query` object this Custom Tables Query should use.
	 *
	 * @since TBD
	 *
	 * @var WP_Query|null
	 */
	private $wp_query;

	/**
	 * Returns an instance of this class, built using the input `WP_Query` as a model.
	 *
	 * @since TBD
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
		$ct_query->query      = $ct_query->filter_query_vars( wp_parse_args( (array) $override_args, $wp_query->query ) );
		$ct_query->query_vars = $ct_query->query;

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
			$query_filters->set_query_var_mask( 'join', false );
			$query_filters->set_query( $ct_query );
		}

		$ct_query->wp_query = $wp_query;

		return $ct_query;
	}

	/**
	 * Adds an opportunity to filter the query_vars that will be used
	 * in the newly constructed instance of this object.
	 *
	 * @since TBD
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
		 * @since TBD
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
	 * @since TBD
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
		add_filter( 'posts_orderby', [ $this, 'order_by_occurrence_id' ], 10, 2 );
		add_filter( 'posts_where', [ $this, 'filter_by_date' ], 10, 2 );
		add_filter( 'posts_where', [ $this, 'filter_where' ], 10, 2 );
		add_filter( 'posts_join', [ $this, 'join_occurrences_table' ], 10, 2 );

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
		 * @since TBD
		 *
		 * @param Custom_Tables_Query $this A reference to this Custom Tables query.
		 */
		do_action( 'tec_events_custom_tables_v1_custom_tables_query_pre_get_posts', $this );

		$results = parent::get_posts();

		/**
		 * Fires after the Custom Tables Query ran.
		 *
		 * @since TBD
		 *
		 * @param array|object|null   $results The query results.
		 * @param Custom_Tables_Query $this    A reference to this Custom Tables query.
		 */
		do_action( 'tec_events_custom_tables_v1_custom_tables_query_results', $results, $this );

		if (
			$this->wp_query instanceof WP_Query
			&& empty( $this->get( 'no_found_rows', false ) )
		) {
			$this->wp_query->found_posts = $this->found_posts;
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
	 * @since TBD
	 *
	 * @param  string    $search    The WHERE clause as produced by the `WP_Query` instance.
	 * @param  WP_Query  $wp_query  A reference to the `WP_Query` instance whose search WHERE clause is currently being
	 *                              filtered.
	 *
	 * @return string The WHERE clause as produced by the `WP_Query` instance, untouched by the method.
	 */
	public function replace_meta_query( $search, WP_Query $wp_query ) {
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
	 * @since TBD
	 *
	 * @param  string        $request_fields The original `SELECT` SQL.
	 * @param  WP_Query|null $query          A reference to the `WP_Query` instance currently being
	 *                                 filtered.
	 *
	 * @return string The filtered `SELECT` clause.
	 */
	public function redirect_posts_fields( $request_fields, WP_Query $query = null ) {
		if ( $this !== $query ) {
			return $request_fields;
		}

		remove_filter( 'posts_fields', [ $this, 'redirect_posts_fields' ] );

		/**
		 * Filters the table field, including the table name, that should be
		 * used to identify distinct Occurrences results in the context
		 * of a Custom Tables Query.
		 *
		 * @since TBD
		 *
		 * @param string $request_fields The Query fields request, e.g. `ids`.
		 */
		$request_fields = apply_filters( 'tec_events_custom_tables_v1_occurrence_select_fields', $request_fields );

		return $request_fields;
	}

	/**
	 * Changes the `GROUP BY` clause for posts to avoid the collapse of results on the post ID.
	 *
	 * @since TBD
	 *
	 * @param  string         $groupby  The original `GROUP BY` SQL clause.
	 * @param  WP_Query|null  $query    A reference to the `WP_Query` instance currently being filtered.
	 *
	 * @return string The updated `GROUP BY` SQL clause.
	 */
	public function group_posts_by_occurrence_id( $groupby, WP_Query $query = null ) {
		if ( $this !== $query ) {
			return $groupby;
		}

		remove_filter( 'posts_groupby', [ $this, 'group_posts_by_occurrence_id' ] );

		return '';
	}

	/**
	 * Replace the SQL clause that would order posts by ID to order them by Occurrence ID.
	 *
	 * @since TBD
	 *
	 * @param string        $order_by          The input `ORDER BY` SQL clause, as produced by the
	 *                                         `WP_Query` class code.
	 * @param WP_Query|null $query             A reference to the `WP_Query` instance currently being filtered.
	 *
	 * @return string The filtered `ORDER BY` SQL clause, redirecting `wp_posts.ID` to Occurrence ID,
	 *                if required.
	 */
	public function order_by_occurrence_id( $order_by, WP_Query $query = null ) {
		if ( $this !== $query ) {
			return $order_by;
		}

		remove_filter( 'posts_orderby', [ $this, 'order_by_occurrence_id' ] );

		$occurrences = Occurrences::table_name( true );
		global $wpdb;

		/*
		 * Replace, implicitly redirecting them, a curated list of order criteria to the Occurrence-table
		 * based criteria.
		 *
		 * @todo is this code eligible for a more general purpose use? Should it be more flexible?
		 */
		$order_by = str_replace(
			[ $wpdb->posts . '.ID', 'event_date', 'event_duration' ],
			[ $occurrences . '.occurrence_id', $occurrences . '.start_date', $occurrences . '.duration' ],
			$order_by
		);

		return $order_by;
	}

	/**
	 * Intercept appropriate order by fields and map to our new occurrence fields.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @param string        $where          The input `WHERE` clause, as built by the `WP_Query`
	 *                                      class code.
	 * @param WP_Query|null $query          A reference to the `WP_Query` instance currently being filtered.
	 *
	 * @return string The `WHERE` SQL clause, modified to be date-bound, if required.
	 */
	public function filter_where( $where, WP_Query $query ) {
		/**
		 * Filters the `WHERE` statement produced by the Custom Tables Query.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @param string        $where          The input `WHERE` clause, as built by the `WP_Query`
	 *                                      class code.
	 * @param WP_Query|null $query          A reference to the `WP_Query` instance currently being filtered.
	 *
	 * @return string The `WHERE` SQL clause, modified to be date-bound, if required.
	 */
	public function filter_by_date( $where, WP_Query $query = null ) {
		if ( $this !== $query ) {
			return $where;
		}

		remove_filter( 'posts_where', [ $this, 'filter_by_date' ] );

		if ( $query instanceof WP_Query && $query->get( 'eventDate', null ) ) {
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
	 * @since TBD
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
		$join .= " LEFT JOIN {$occurrences} ON {$wpdb->posts}.ID = {$occurrences}.post_id";

		return $join;
	}

	/**
	 * Implementation of the magic method to check if a property is set on this object or not.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return WP_Query|null A reference to the `WP_Query` instance this object is wrapping.
	 */
	public function get_wp_query() {
		return $this->wp_query;
	}
}
