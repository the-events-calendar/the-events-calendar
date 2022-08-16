<?php
/**
 * An extension of the Query Filters class used by the Repository to
 * redirect some of its custom fields based queries to the plugin custom tables.
 *
 * @since   TBD
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
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Repository
 */
class Custom_Tables_Query_Filters extends Query_Filters {

	/**
	 * The default query vars filtering application mask.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
	 *
	 * @var array<string,bool>
	 */
	private $query_vars_mask;

	/**
	 * A flag property to indicate whether the Custom Table redirections have been already applied or not.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	private $redirected = false;
	/**
	 * Reference to the class used to replace values from the request.
	 *
	 * @since TBD
	 *
	 * @var Query_Replace query_redirect
	 */
	private $query_redirect;

	/**
	 * Custom_Tables_Query_Filters constructor.
	 *
	 * Overrides the base constructor to set up a meta key redirection map.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
		}

		return parent::filter_posts_join( $join, $query );
	}

	/**
	 * Overrides the default implementation to redirect some 'ORDER BY' clauses
	 * to the custom tables.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
		 * @since TBD
		 * @see   Custom_Tables_Query::redirect_posts_fields() for this filter documentation.
		 */
		$select_fields = apply_filters( 'tec_events_custom_tables_v1_occurrence_select_fields', $wp_post_fields, 'ids' );


		return str_replace( $wp_post_fields, $select_fields, $input );
	}

	/**
	 * Filters the `GROUP BY` part of the SQL built by the Query to replace references
	 * of `wp_posts.ID` with references to the `Occurrences.occurrence_id` table.
	 *
	 * @since TBD
	 *
	 * @param  string    $groupby  The original `GROUP BY` string.
	 * @param  WP_Query  $query    A reference to the `WP_Query` object that is currently
	 *                             running.
	 *
	 * @return string The modified `GROUP BY` SQL.
	 */
	public function group_by_occurrence_id( $groupby, WP_Query $query ) {
		if ( $this->current_query !== $query ) {
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
	 * @since TBD
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
	 * Overrides the base method to handle requests based on the post parent, usually coming from PRO.
	 *
	 * @since TBD
	 *
	 * @param string      $where_clause The original WHERE clause the repository is adding to the query.
	 * @param string|null $id           An optional unique identifier for the query.
	 * @param bool        $override     Whether to override a pre-existing WHERE clause with this one, if present, or
	 *                                  not. This will only apply if the `$id` is provided.
	 */
	public function where( $where_clause, $id = null, $override = false ) {
		global $wpdb;

		// @see Tribe__Events__Pro__Repositories__Event::filter_by_in_series for the origin of this statement.
		$is_in_series_where = preg_match(
			'/^' . preg_quote( "{$wpdb->posts}.post_parent", '/' ) . '\\s?=\\s?(?<id>\\d+)/',
			$where_clause,
			$m );

		// TODO: Move into PRO?
		if ( $is_in_series_where && isset( $m['id'] ) ) {
			$occurrences   = Occurrences::table_name( true );
			$occurrence_id = Occurrence::normalize_id( absint( $m['id'] ) );
			$occurrence    = Occurrence::find( $occurrence_id, 'occurrence_id' );

			if ( ! $occurrence instanceof Occurrence ) {
				return;
			}

			$where_clause = (string) $wpdb->prepare( "{$occurrences}.post_id = %d", $occurrence->post_id );
		}

		parent::where( $where_clause, $id, $override );
	}

	/**
	 * Sets the mask value for a query var, or a list of query vars, that should be applied at filtering time.
	 *
	 * The mask will NOT change the value and content of each query var, it will just prevent the `filter_` methods
	 * from applying if set to `false`. By default, all query vars will be applied.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return WP_Query A reference to the current target query.
	 */
	public function get_query() {
		return $this->current_query;
	}

	/**
	 * Resets the query vars filtering mask to the default value.
	 *
	 * @since TBD
	 */
	public function reset_query_vars_mask() {
		$this->query_vars_mask = self::$default_query_vars_mask;
	}
}
