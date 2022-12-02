<?php
/**
 * An extension of the Query Filters class used by the Repository to
 * redirect some of its custom fields based queries to the plugin custom tables.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Repository
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Repository;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use Tribe__Events__Main as TEC;
use Tribe__Repository__Query_Filters as Query_Filters;
use WP_Query;

/**
 * Class Custom_Tables_Query_Filters
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Repository
 */
class Custom_Tables_Query_Filters extends Query_Filters {

	/**
	 * The default query vars filtering application mask.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,bool>
	 */
	private static $default_query_vars_mask = [
		'like'          => true,
		'status'        => true,
		'join'          => true,
		'where'         => true,
		'orderby'       => true,
		'fields'        => true,
	];

	/**
	 * A list of redirected meta keys, compiled during calls to the `redirect` method.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,array<string,mixed>>
	 */
	protected $redirects = [];

	/**
	 * A mask governing what query vars should be applied at filtering time, `true` will apply the query var,
	 * `false` will not apply it.
	 *
	 * The constructor will initialize this to the default value.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,bool>
	 */
	private $query_vars_mask;

	/**
	 * A flag property to indicate whether the Custom Table redirections have been already applied or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $redirected = false;
	/**
	 * Reference to the class used to replace values from the request.
	 *
	 * @since 6.0.0
	 *
	 * @var Query_Replace query_redirect
	 */
	private $query_redirect;

	/**
	 * Custom_Tables_Query_Filters constructor.
	 *
	 * Overrides the base constructor to set up a meta key redirection map.
	 *
	 * @since 6.0.0
	 *
	 * @param  Query_Replace  $query_redirect
	 */
	public function __construct( Query_Replace $query_redirect ) {
		parent::__construct();
		$this->query_redirect  = $query_redirect;
		$this->query_vars_mask = self::$default_query_vars_mask;
	}

	/**
	 * Overrides the default implementation to redirect some WHERE LIKE clauses
	 * to the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * {@inheritdoc}
	 */
	public function filter_by_like( $where, WP_Query $query ) {
		if ( $this->current_query !== $query || false === $this->query_vars_mask['like'] ) {
			return $where;
		}

		$this->redirect();

		return parent::filter_by_like( $where, $query );
	}

	/**
	 * Redirects the current query vars as set from the ORM default implementation
	 * to the Custom Tables where required.
	 *
	 * @since 6.0.0
	 */
	private function redirect() {
		if ( $this->redirected ) {
			return;
		}

		$redirected = $this->query_redirect
			->set_join( $this->query_vars['join'] )
			->set_where( $this->query_vars['where'] );

		if ( isset( $this->query_vars['fields'] ) ) {
			$redirected->set_fields( $this->query_vars['fields'] );
		}

		if ( isset( $this->query_vars['orderby'] ) ) {
			$redirected->set_order_by( $this->query_vars['orderby'] );
		}

		$after_order_by_index = static::AFTER . 'orderby';
		if ( isset( $this->query_vars[ $after_order_by_index ] ) ) {
			$redirected->set_after_order_by( $this->query_vars[ $after_order_by_index ] );
		}

		if ( isset( $this->query_vars['like'] ) ) {
			$redirected->set_like( (array) $this->query_vars['like'] );
		}

		$redirected_query_vars = $redirected->build();

		$this->redirects                           = $redirected_query_vars['redirects'];
		$this->query_vars['like']                  = $redirected_query_vars['like'];
		$this->query_vars['status']                = $redirected_query_vars['status'];
		$this->query_vars['join']                  = $redirected_query_vars['join'];
		$this->query_vars['where']                 = $redirected_query_vars['where'];
		$this->query_vars['orderby']               = $redirected_query_vars['orderby'];
		$this->query_vars[ $after_order_by_index ] = $redirected_query_vars[ $after_order_by_index ];
		$this->query_vars['fields']                = $redirected_query_vars['fields'];

		$this->redirected = true;
	}

	/**
	 * Overrides the default implementation to redirect some `WHERE` clauses
	 * to the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * {@inheritdoc}
	 */
	public function filter_posts_where( $where, WP_Query $query ) {
		if ( $this->current_query !== $query || false === $this->query_vars_mask['where'] ) {
			return $where;
		}

		$this->redirect();

		return parent::filter_posts_where( $where, $query );
	}

	/**
	 * Overrides the default implementation to redirect some `WHERE` clauses
	 * to the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * {@inheritdoc}
	 */
	public function filter_by_to_ping( $where, WP_Query $query ) {
		if ( $this->current_query !== $query || false === $this->query_vars_mask['where'] ) {
			return $where;
		}

		$this->redirect();

		return parent::filter_by_to_ping( $where, $query );
	}

	/**
	 * Overrides the default implementation to redirect some 'JOIN' clauses
	 * to the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * {@inheritdoc}
	 */
	public function filter_posts_join( $join, WP_Query $query ) {
		if (
			(array) $query->get( 'post_type', [] ) !== [ TEC::POSTTYPE ]
			|| $this->current_query !== $query
			|| false === $this->query_vars_mask['join']
		) {
			return $join;
		}

		$this->redirect();

		if ( count( $this->redirects ) === 0 ) {
			/*
			 * Whether meta is being actually redirected or not, there should always be a JOIN on the Occurrences
			 * table as that is the only way to represent Occurrences.
			 */
			global $wpdb;
			$occurrences                = Occurrences::table_name( true );
			$this->query_vars['join'][] = "JOIN {$occurrences} ON {$wpdb->posts}.ID = {$occurrences}.post_id";
		} else if ( ! empty( $this->query_vars['join'] ) ) {
			$join = $this->deduplicate_joins( $join );
		}

		return parent::filter_posts_join( $join, $query );
	}

	/**
	 * Overrides the default implementation to redirect some 'ORDER BY' clauses
	 * to the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * {@inheritdoc}
	 */
	public function filter_posts_orderby( $orderby, WP_Query $query ) {
		if ( $this->current_query !== $query || false === $this->query_vars_mask['orderby'] ) {
			return $orderby;
		}

		$this->redirect();

		return parent::filter_posts_orderby( $orderby, $query );
	}

	/**
	 * Overrides the default implementation to redirect some 'SELECT' clauses
	 * to the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * {@inheritdoc}
	 */
	public function filter_posts_fields( $fields, WP_Query $query ) {
		if ( $this->current_query !== $query || false === $this->query_vars_mask['fields'] ) {
			return $fields;
		}

		$this->redirect();

		$fields = $this->post_id_to_occurrence_id( $fields );

		return parent::filter_posts_fields( $fields, $query );
	}

	/**
	 * Replaces references to `wp_posts.ID` with references to `Occurrences.occurrence_id`.
	 *
	 * @since 6.0.0
	 *
	 * @param  string|array<string>  $input  Either a single string to make the replacement
	 *                                       in or a set of strings.
	 *
	 * @return string|array<string> Either a string if the input value was a string, or an
	 *                              array of strings if the input value was an array of strings.
	 */
	private function post_id_to_occurrence_id( $input ) {
		// Do not get the `ID` column from the `posts` table, but the `occurrence_id` column from the Occurrences table.
		global $wpdb;

		$wp_post_fields = $wpdb->posts . '.ID';

		/**
		 * @since 6.0.0
		 * @see   Custom_Tables_Query::redirect_posts_fields() for this filter documentation.
		 */
		$select_fields = apply_filters( 'tec_events_custom_tables_v1_occurrence_select_fields', $wp_post_fields, 'ids' );


		return str_replace( $wp_post_fields, $select_fields, $input );
	}

	/**
	 * Filters the `GROUP BY` part of the SQL built by the Query to replace references
	 * of `wp_posts.ID` with references to the `Occurrences.occurrence_id` table.
	 *
	 * @since 6.0.0
	 *
	 * @param  string    $groupby  The original `GROUP BY` string.
	 * @param  WP_Query  $query    A reference to the `WP_Query` object that is currently
	 *                             running.
	 *
	 * @return string The modified `GROUP BY` SQL.
	 */
	public function group_by_occurrence_id( $groupby, WP_Query $query ) {
		// Don't add group by if we don't have occurrence table joined.
		if ( $this->current_query !== $query || empty( $this->query_vars['join'] ) ) {
			return $groupby;
		}

		$this->redirect();

		global $wpdb;

		return str_replace(
			$wpdb->posts . '.ID',
			Occurrences::table_name( true ) . '.' . Occurrences::uid_column(),
			$groupby
		);
	}

	/**
	 * Overrides the base method to handle post parent based queries that might come from PRO code.
	 *
	 * @since 6.0.0
	 *
	 * @param string      $join_clause The original JOIN clause to add.
	 * @param string|null $id          An optional unique identifier for the JOIN clause in the context of the filters.
	 * @param bool        $override    Whether to override a pre-existing JOIN clause with this one, if present, or
	 *                                 not. This will only apply if the `$id` is provided.
	 */
	public function join( $join_clause, $id = null, $override = false ) {
		global $wpdb;
		// @see Tribe__Events__Pro__Repositories__Event::filter_by_in_series for the origin of this statement.
		$in_series_join_pattern = "JOIN {$wpdb->postmeta} in_series_meta ON {$wpdb->posts}.ID = in_series_meta.post_id";
		$is_in_series_join      = false !== strpos( $join_clause, $in_series_join_pattern );

		if ( $is_in_series_join ) {
			// No JOIN clause required since the custom table is already JOINed, just return.
			return;
		}

		parent::join( $join_clause, $id, $override );
	}

	/**
	 * Sets the mask value for a query var, or a list of query vars, that should be applied at filtering time.
	 *
	 * The mask will NOT change the value and content of each query var, it will just prevent the `filter_` methods
	 * from applying if set to `false`. By default, all query vars will be applied.
	 *
	 * @since 6.0.0
	 *
	 * @param string|array<string> $query_var Either a query var, or a list of query vars to set the mask value for.
	 * @param bool $mask Whether the query var should apply at filtering time (`true`) or not (`false`).
	 */
	public function set_query_var_mask( $query_var, $mask ) {
		$query_vars            = (array) $query_var;
		$this->query_vars_mask = array_merge(
			$this->query_vars_mask,
			array_combine(
				$query_vars,
				array_fill( 0, count( $query_vars ), $mask )
			)
		);
	}

	/**
	 * Returns a reference to the current target Query.
	 *
	 * @since 6.0.0
	 *
	 * @return WP_Query A reference to the current target query.
	 */
	public function get_query() {
		return $this->current_query;
	}

	/**
	 * Resets the query vars filtering mask to the default value.
	 *
	 * @since 6.0.0
	 */
	public function reset_query_vars_mask() {
		$this->query_vars_mask = self::$default_query_vars_mask;
	}

	/**
	 * Returns a de-duplicated version of the query input JOIN clause that will not contain JOINs
	 * that would duplicated the ones set in the this object `join` query variables.
	 *
	 * @param string $query_join The query input JOIN clause.
	 *
	 * @return string The de-duplicated JOIN clause.
	 */
	protected function deduplicate_joins( $query_join ): string {
		if ( ! is_string( $query_join ) || empty( $query_join ) || empty( $this->query_vars['join'] ) ) {
			// Nothing to deduplicate.
			return $query_join;
		}

		// Break each current JOIN clause into a set of couples in the shape `['JOIN' 'table on ...']`.
		$query_vars_join_couples = [];
		$preg_split_flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
		foreach ( ( $this->query_vars['join'] ?? [] ) as $query_var_join ) {
			$split = array_filter( preg_split( '/((?:LEFT|INNER|RIGHT)?\\s?JOIN)/', trim( $query_var_join ), 2, $preg_split_flags ) );
			if ( count( $split ) !== 2 ) {
				continue;
			}
			$query_vars_join_couples[] = [ reset( $split ), trim( end( $split ) ) ];
		}

		// Break the input query JOIN clause into a set of couples in the shape `['JOIN' 'table on ...']`.
		$string_joins = array_filter( preg_split( '/((?:LEFT|INNER|RIGHT)?\\s?JOIN)/', trim( $query_join ), - 1, $preg_split_flags ) );
		$query_join_couples = array_chunk( array_map( 'trim', $string_joins ), 2 );

		// Now remove from the input query JOIN any JOIN already handled by this filter.
		$b_join_whats = array_column( $query_join_couples, 1 );
		foreach ( $query_vars_join_couples as $k => [$join_type, $join_what] ) {
			if ( ! in_array( $join_what, $b_join_whats, true ) ) {
				continue;
			}

			// Remove the JOIN clause from the query JOIN: it should be overridden by the filter's JOIN.
			unset( $query_join_couples[ array_search( $join_what, $b_join_whats, true ) ] );
		}

		// Removed all queries
		if ( empty( $query_join_couples ) ) {
			return '';
		}

		// Re-assemble the JOIN clause, minus the JOIN clauses removed as already handled by this filter.
		return implode( ' ', array_merge( ...$query_join_couples ) );
	}
}
