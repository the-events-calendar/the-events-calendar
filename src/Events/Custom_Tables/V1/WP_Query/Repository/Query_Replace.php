<?php
/**
 * Class to redirect clauses from the meta table into the custom table.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Repository
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Repository;

use TEC\Events\Custom_Tables\V1\WP_Query\Redirection_Schema;
use Tribe__Repository__Query_Filters as Query_Filters;

/**
 * Class Query_Replace
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Repository
 */
class Query_Replace {
	/**
	 * The filtered version of the meta key redirection map, the same used by
	 * the Custom Tables Meta Query.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,array<string>>
	 */
	protected $meta_key_redirection_map;

	/**
	 * A set of `LIKE` clauses that should be redirected to the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,array<string,string>>|null
	 */
	protected $like;

	/**
	 * A list of the meta keys that should be redirected to the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string>
	 */
	private $redirectable_meta_keys;
	/**
	 * Initial state of the different sets of joins.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string> join
	 */
	private $join = [];
	/**
	 * Initial set of where clauses
	 *
	 * @since 6.0.0
	 *
	 * @var array<string> where
	 */
	private $where = [];
	/**
	 * List of fields selected for this query.
	 *
	 * @since 6.0.0
	 *
	 * @var string fields
	 */
	private $fields = '';
	/**
	 * Initial value for the order by.
	 *
	 * @since 6.0.0
	 *
	 * @var string order_by
	 */
	private $order_by = '';
	/**
	 * Clause used to order the results after.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string> after_order_by
	 */
	private $after_order_by = [];

	/**
	 * Custom_Tables_Query_Filters constructor.
	 *
	 * Overrides the base constructor to set up a meta key redirection map.
	 *
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->meta_key_redirection_map = (array) Redirection_Schema::get_filtered_meta_key_redirection_map();
		$this->redirectable_meta_keys   = array_keys( $this->meta_key_redirection_map );
	}

	/**
	 * Sets the fields as those could be set on the `WP_Query` `fields` parameter.
	 *
	 * @since 6.0.0
	 *
	 * @param string $fields The fields value.
	 *
	 * @return $this A reference to this for chaining.
	 */
	public function set_fields( $fields = '' ) {
		$this->fields = $fields;

		return $this;
	}

	/**
	 * Set the different joins.
	 *
	 * @since 6.0.0
	 *
	 * @param  array<string, string>  $join  An array with the different joins.
	 *
	 * @return $this
	 */
	public function set_join( array $join ) {
		$this->join = $join;

		return $this;
	}

	/**
	 * Set the different where clauses available.
	 *
	 * @since 6.0.0
	 *
	 * @param  array<string, string>  $where  The list of where clauses to be modified.
	 *
	 * @return $this
	 */
	public function set_where( array $where ) {
		$this->where = $where;

		return $this;
	}

	/**
	 * Allow to set the order_by clause
	 *
	 * @since 6.0.0
	 *
	 * @param string|array<string>|array<string,string> $order_by The order by clause, in any format supported by
	 *                                                            `WP_Query`.
	 *
	 * @return $this
	 */
	public function set_order_by( $order_by ) {
		$this->order_by = $order_by;

		return $this;
	}

	/**
	 * Custom setter for the after order by clause.
	 *
	 * @since 6.0.0
	 *
	 * @param string|array<string>|array<string,string> $after_order_by The order by clause, in any format supported
	 *                                                                  by `WP_Query`.
	 */
	public function set_after_order_by( array $after_order_by ) {
		$this->after_order_by = $after_order_by;
	}

	/**
	 * Parses the current filters, as set up by the default version of the class, and
	 * redirects them to the Custom Tables where required.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string,array<mixed>> The redirected query vars.
	 */
	public function build() {
		global $wpdb;

		$redirects                = [];
		$redirected_join          = [];

		$where_search_replace = [];

		foreach ( $this->join as $join_sql ) {
			unset( $matches, $m );

			/*
			 * The JOIN will happen on the `postmeta` table and will look one of two ways:
			 * `... JOIN wp_postmeta <alias> ON ...` or `... JOIN wp_postmeta AS <alias> ON ...`
			 * Ignore case as `AS` and `as` are both valid SQL.
			 */
			preg_match_all( '/' .
			                '(?:(?:LEFT|RIGHT|INNER)\\s+)*' . // Any type of JOIN, if any.
			                'JOIN\\s+' . $wpdb->postmeta . '\\s+' . // JOIN on the meta table.
			                '(?:AS\\s+)*' . // Maybe there's an `AS` clause of the meta table.
			                '(?<alias>[\\w_-]*)' . // The alias used for the meta table.
			                '/ui',
				$join_sql,
				$matches
			);

			if ( empty( $matches['alias'] ) ) {
				// Not a JOIN on the meta table, move on and let it be.
				$redirected_join[] = $join_sql;
				continue;
			}

			foreach ( $matches['alias'] as $i => $alias ) {
				/*
				 * Now find the meta key: what we need to replace in the `WHERE` clause will look
				 * like `... <alias>.meta_value ...`.
				 */
				preg_match( '/' . $alias . '.meta_key\\s*=\\s*(?:[\'"])(?<meta_key>[\\w_-]+)(?:[\'"])/i', $join_sql, $m );

				if ( empty( $m['meta_key'] ) ) {
					// Not the kind of check we're looking for, move on.
					$redirected_join[] = $join_sql;
					continue;
				}

				$meta_key = $m['meta_key'];

				if (
					! isset ( $this->meta_key_redirection_map[ $meta_key ] )
					|| ! in_array( $meta_key, $this->redirectable_meta_keys, true )
				) {
					// We should not redirect this meta key at all, let this be.
					$this_join_clause_start  = strpos( $join_sql, $matches[0][ $i ] );
					$next_join_clause_start = isset( $matches[0][ $i + 1 ] )
						? strpos( $join_sql, $matches[0][ $i + 1 ] )
						: null;
					$this_join_clause_length = $next_join_clause_start - $this_join_clause_start;
					$this_join = $next_join_clause_start !== null
						? substr( $join_sql, $this_join_clause_start, $this_join_clause_length )
						: substr( $join_sql, $this_join_clause_start );
					$redirected_join[]       = trim( $this_join );
					continue;
				}

				$map_entry = $this->meta_key_redirection_map[ $meta_key ];
				$search    = $alias . '.meta_value';
				$table     = $map_entry['table'];
				$column    = $map_entry['column'];

				$replace = sprintf( "%s.%s", $table, $column );

				/*
				 * And we should not `JOIN` as planned on the meta table anymore.
				 * The reason we use a JOIN here is that the ORM query filters will
				 * delegate `EXIST` type of queries to the default `WP_Query` implementation.
				 * As such, they will be handled later in the Custom Tables Query code.
				 */
				$redirected_join[] = sprintf(
					'JOIN %1$s ON %2$s.ID = %1$s.%3$s',
					$table,
					$wpdb->posts,
					$map_entry['join_posts_on']
				);

				$redirects[ $alias ] = [
					'meta_alias'   => $alias,
					'meta_key'     => $meta_key,
					'custom_table' => $table,
					'column'       => $column,
				];

				$where_search_replace[] = [ 'search' => $search, 'replace' => $replace ];
			}
		}

		$redirected_where = str_replace(
			array_column( $where_search_replace, 'search' ),
			array_column( $where_search_replace, 'replace' ),
			$this->where
		);

		$redirected_fields = str_replace(
			array_column( $where_search_replace, 'search' ),
			array_column( $where_search_replace, 'replace' ),
			$this->fields
		);

		list( $redirected_orderby, $redirected_orderby_after ) = $this->redirect_orderbys( $redirects );

		return [
			'redirects'                      => $redirects,
			'like'                           => $this->redirect_like(),
			'status'                         => [],
			'join'                           => array_unique( $redirected_join ),
			'where'                          => array_unique( $redirected_where ),
			'orderby'                        => $redirected_orderby,
			Query_Filters::AFTER . 'orderby' => $redirected_orderby_after,
			'fields'                         => $redirected_fields,
		];
	}

	/**
	 * Sets the array of `LIKE` clauses that should be redirected.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array<string,string>>|null $like An array of `LIKE` clauses that should be redirected to
	 *                                                      the custom tables.
	 */
	public function set_like( array $like = null ) {
		foreach ( $like as $field => &$values ) {
			// Uniform to an array of arrays.
			$values = (array) $values;
		}
		unset( $values );

		$this->like = $like;
	}

	/**
	 * Redirects the `ORDER BY` clauses, both before and after the default WordPress ones, to the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array<string,mixed>> $redirects A list of the redirections that should be applied as built
	 *                                                     from the `JOIN` and `WHERE` clauses.
	 *
	 * @return array<array<string,string>> An array specifying the redirected `ORDER BY` clauses to apply before and
	 *                                     after the WordPress one, in this order.
	 */
	private function redirect_orderbys( array $redirects ) {
		$orderby_search           = array_column( $redirects, 'meta_alias' );
		$orderby_replace          = array_map( static function ( array $redirect ) {
			return sprintf( "%s.%s", $redirect['custom_table'], $redirect['column'] );
		}, $redirects );
		$normalized_orderby       = tribe_normalize_orderby( $this->order_by );
		$normalized_after_orderby = tribe_normalize_orderby( $this->after_order_by );

		$redirected_orderby = array_combine(
			str_replace( $orderby_search, $orderby_replace, array_keys( $normalized_orderby ) ),
			$normalized_orderby
		);

		$redirected_orderby_after = array_combine(
			str_replace( $orderby_search, $orderby_replace, array_keys( $normalized_after_orderby ) ),
			$normalized_after_orderby
		);

		return array( $redirected_orderby, $redirected_orderby_after );
	}

	/**
	 * Redirects the `LIKE` clauses to the custom tables.
	 *
	 * The current implementation is just a pass-through logic as only post fields are supported
	 * in the base logic and those are not redirected to the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string,array<string,string>>|null The redirected `LIKE` clauses.
	 */
	private function redirect_like( array $redirects = [] ) {
		return $this->like;
	}
}
