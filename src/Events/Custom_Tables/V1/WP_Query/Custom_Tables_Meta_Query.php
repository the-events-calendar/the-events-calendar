<?php
/**
 * An extension of the default WordPress Meta Query to redirect some custom fields to
 * the plugins custom tables.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query;

use WP_Meta_Query;

/**
 * Class Custom_Tables_Meta_Query
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */
class Custom_Tables_Meta_Query extends WP_Meta_Query {

	/**
	 * A set of SQL comparison operators that will operate on numeric inputs.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string>
	 */
	static protected $numeric_operators = [
		'>',
		'>=',
		'<',
		'<=',
		'BETWEEN',
		'NOT BETWEEN',
	];

	/**
	 * A set of SQL comparison operators that do not necessarily operate on numeric inputs.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string>
	 */
	protected static $non_numeric_operators = [
		'=',
		'!=',
		'LIKE',
		'NOT LIKE',
		'IN',
		'NOT IN',
		'EXISTS',
		'NOT EXISTS',
		'RLIKE',
		'REGEXP',
		'NOT REGEXP',
	];
	/**
	 * A map from meta keys, e.g. '_EventStartDate', to the corresponding custom table name and column.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,<array<string>>
	 */
	protected $meta_key_redirection_map = [];

	/**
	 * A list of tables the object has already joined.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,true>
	 */
	protected $joined_tables = [];
	/**
	 * Whether to `LEFT JOIN` to the custom tables, thus yielding `NULL` values on the custom tables side
	 * on missing matches from the posts tables, or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	protected $left_join = false;

	/**
	 * Custom_Tables_Meta_Query constructor.
	 *
	 * This method overrides the default `WP_Meta_Query` constructor to set up the instance to suit
	 * our requirements.
	 *
	 * @since 6.0.0
	 *
	 * @param  false|array<string,mixed>  $meta_query  A meta query in a format supported by WordPress Meta Query.
	 */
	public function __construct( $meta_query = false ) {
		$this->meta_key_redirection_map = Redirection_Schema::get_filtered_meta_key_redirection_map();
		parent::__construct( $meta_query );
	}

	/**
	 * Overrides the base implementation to ensure the format of the meta query arguments is correct and the values are
	 * coherent.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string|int,mixed> $queries The meta query array representation.
	 *
	 * @return array<string|int,mixed> The sanitized meta query array representation.
	 */
	public function sanitize_query( $queries ) {
		// Start by letting `WP_Meta_Query` apply its usual prune and sanitization rules.
		$clean_queries = parent::sanitize_query( $queries );

		// Deduplicate queries, they can survive the previous steps ordered but still duplicated.
		$json_encoded_queries   = array_map( 'wp_json_encode', $clean_queries );
		$unique_encoded_queries = array_unique( $json_encoded_queries );
		$unique_queries         = array_filter( array_map( static function ( $json ) {
			return json_decode( $json, true );
		}, $unique_encoded_queries ) );

		/*
		 * Apply a string index to each query to avoid the default behaviour that would use the alias.
		 * This will implicitly deduplicate the queries, but we've taken care of that before and we
		 * need to control the aliases.
		 */
		$indexed_queries = [];
		foreach ( $unique_queries as $query_key => $query ) {
			$string_query_key                     = is_numeric( $query_key ) ? '_' . $query_key : $query_key;
			$indexed_queries[ $string_query_key ] = $query;
		}

		// Redirect the meta keys to the ones we use in the custom tables.
		$redirectable_keys = array_combine(
			array_keys( $this->meta_key_redirection_map ),
			array_column( $this->meta_key_redirection_map, 'column' )
		);

		foreach ( $indexed_queries as $index => &$query ) {
			if ( isset( $query['key'], $redirectable_keys[ $query['key'] ] ) ) {
				$query['original_meta_key'] = $query['key'];
				$query['key']               = $redirectable_keys[ $query['key'] ];
			}
		}

		return $indexed_queries;
	}

	/**
	 * Overrides the base method to redirect some clauses to the custom tables.
	 *
	 * We cannot, possibly, handle and thus redirect ALL the custom fields queries to the custom tables.
	 * As such, we still rely on the `WP_Meta_Query` implementation to redirect the custom fields we control
	 * to the custom tables and let the base class handle the others.
	 *
	 * @since 6.0.0
	 *
	 * @param  array<string,mixed>         $clause         The clause in array format, specifying the key, value,
	 *                                                     comparison operator and so on.
	 * @param  array<array<string,mixed>>  $parent_query   The parent query, a set of clauses.
	 * @param  string|int                  $clause_key     Optional. The array key used to name the clause in the
	 *                                                     original `$meta_query` parameters. If not provided, a key
	 *                                                     will be generated automatically.
	 *
	 * @return array<string,array<string>> Array containing JOIN and WHERE SQL clauses to append to a first-order query.
	 */
	public function get_sql_for_clause( &$clause, $parent_query, $clause_key = '' ) {
		if ( ! isset(
			$clause['original_meta_key'],
			$clause['key'],
			$this->meta_key_redirection_map[ $clause['original_meta_key'] ]['table']
		) ) {
			return parent::get_sql_for_clause( $clause, $parent_query, $clause_key );
		}

		$clause['compare']     = $this->normalize_clause_compare( $clause );
		$clause['compare_key'] = $this->normalize_clause_compare_key( $clause );
		$meta_compare          = $clause['compare'];
		$meta_compare_key      = $clause['compare_key'];
		$column                = $clause['key'];

		/*
		 * Differently from the base `WP_Meta_Query` class, this implementation will not support the `compare_key`
		 * operator if not `=`; in the custom tables there is no `meta_key`, just columns.
		 */
		if ( $meta_compare_key !== '=' ) {
			return parent::get_sql_for_clause( $clause, $parent_query, $clause_key );
		}

		// If we're here, then we know the clause is targeting a custom field we should redirect to the custom tables.
		$original_meta_key   = $clause['original_meta_key'];
		$required_table_join = $this->meta_key_redirection_map[ $original_meta_key ]['table'];

		$join  = [];
		$where = [];

		// We're always joining posts to the custom table taking care not to do it more than once per custom table.
		if ( ! isset( $this->joined_tables[ $required_table_join ] ) ) {
			global $wpdb;
			/*
			 * We might need to use a LEFT JOIN to make it so that any value on the custom table side will be NULL
			 * if not present; this might be required to check for an Event post meta existence.
			 */
			$join_type   = $this->left_join ? 'LEFT JOIN' : 'INNER JOIN';
			$join[] = " {$join_type} {$required_table_join} ON {$wpdb->posts}.ID = {$required_table_join}.post_id";

			$this->joined_tables[ $required_table_join ] = true;
		}

		$alias = $this->meta_key_redirection_map[ $original_meta_key ]['table'];

		// Save the alias to this clause, for future siblings to find.
		$clause['alias'] = $alias;

		// Determine the data type.
		$_meta_type     = isset( $clause['type'] ) ? $clause['type'] : '';
		$meta_type      = $this->get_cast_for_type( $_meta_type );
		$clause['cast'] = $meta_type;

		// Fallback for clause keys is the table alias. Key must be a string.
		if ( is_int( $clause_key ) || ! $clause_key ) {
			$clause_key = $clause['alias'];
		}

		// Ensure unique clause keys, so none are overwritten.
		$iterator        = 1;
		$clause_key_base = $clause_key;
		while ( isset( $this->clauses[ $clause_key ] ) ) {
			$clause_key = $clause_key_base . '-' . $iterator;
			$iterator++;
		}

		// Store the clause in our flat array.
		$this->clauses[ $clause_key ] =& $clause;

		$table_and_column = $this->build_type_casted_table_column( $alias, $column, $meta_type );

		// Build the WHERE clause.
		switch ( $meta_compare ) {
			case '=':
			case '!=':
			case '>':
			case '>=':
			case '<':
			case '<=':
				$where[] = $this->build_where_operator_sql( $table_and_column, $meta_compare, $clause['value'] );
				break;
			case 'EXISTS':
				$this->left_join = true;
				$where[] = $this->build_where_exists_sql( $alias );
				break;
			case 'NOT EXISTS':
				$this->left_join = true;
				$where[] = $this->build_where_not_exists_sql( $alias );
				break;
			case 'LIKE':
			case 'NOT LIKE':
				$where[] = $this->build_where_like_sql( $table_and_column, $meta_compare, $clause['value'] );
				break;
			case 'IN':
			case 'NOT IN':
				$where[] = $this->build_where_in_sql( $table_and_column, $meta_compare, $clause['value'] );
				break;
			case 'RLIKE':
			case 'REGEXP':
			case 'NOT REGEXP':
				$where[] = $this->build_where_regexp_sql( $table_and_column, $meta_compare, $clause );
				break;
			case 'BETWEEN':
			case 'NOT BETWEEN':
				$where[] = $this->build_where_between_sql( $table_and_column, $meta_compare, $clause['value'] );
				break;
			default:
				$where[] = $this->build_where_default_sql( $clause['value'] );
				break;
		}

		return [ 'join' => $join, 'where' => $where ];
	}

	/**
	 * Normalizes, setting it to a default value if not set, the clause `compare` entry.
	 *
	 * @since 6.0.0
	 *
	 * @param  array<string,mixed>  $clause  The clause in array format.
	 *
	 * @return string The normalized `compare` operator.
	 */
	protected function normalize_clause_compare( array $clause ) {
		if ( isset( $clause['compare'] ) ) {
			// Normalize the compare operator, e.g. from `between` to `BETWEEN`.
			$compare = strtoupper( $clause['compare'] );
			if (
				! in_array( $compare, static::$non_numeric_operators, true )
				&& ! in_array( $compare, static::$numeric_operators, true )
			) {
				// Default to equality if the comparison operator is not a known one.
				$compare = '=';
			}

			return $compare;
		}

		if ( isset( $clause['value'] ) ) {
			$compare = is_array( $clause['value'] ) ? 'IN' : '=';
		} else {
			$compare = 'EXISTS';
		}

		return $compare;
	}

	/**
	 * Normalizes, setting it to a default value if not set, the clause `compare_key` entry.
	 *
	 * While the `compare` key will apply to the custom field value (`meta_value` in the `postmeta`
	 * table), the `compare_key` will apply to the custom field name (`meta_key` in the `postmeta`
	 * table).
	 *
	 * @since 6.0.0
	 *
	 * @param  array<string,mixed>  $clause  The clause in array format.
	 *
	 * @return string The normalized `compare_key` operator.
	 */
	protected function normalize_clause_compare_key( array $clause ) {
		if ( isset( $clause['compare_key'] ) ) {
			// Normalize the compare operator, e.g. from `between` to `BETWEEN`.
			$compare_key = strtoupper( $clause['compare_key'] );
			if ( ! in_array( $compare_key, static::$non_numeric_operators, true ) ) {
				// Default to equality if the comparison operator is not a known one.
				$compare_key = '=';
			}

			return $compare_key;
		}

		// If the operator is not specified, then default to `=` for single values, else `BETWEEN`.
		return isset( $clause['key'] ) && is_array( $clause['key'] ) ? 'IN' : '=';
	}

	/**
	 * Returns the SQL string to use for the table and column with awareness of the
	 * MySQL CAST, if required by the type.
	 *
	 * @since 6.0.0
	 *
	 * @param  string  $table      The  table name to build the table and column string for.
	 * @param  string  $column     The column to build the table and column string for.
	 * @param  string  $meta_type  The meta type, set by the clause `type` key.
	 *
	 * @return string The `table.column` string, type-cast if required.
	 */
	protected function build_type_casted_table_column( $table, $column, $meta_type ) {
		$table_and_column = "{$table}.{$column}";
		if ( 'CHAR' !== $meta_type ) {
			$table_and_column = "CAST({$table}.{$column} AS {$meta_type})";
		}

		return $table_and_column;
	}

	/**
	 * Returns the SQL fragment required to run a comparison or similar statement.
	 *
	 * @since 6.0.0
	 *
	 * @param  string  $table_and_column  The table and column string, with CAST if required.
	 * @param  string  $meta_compare      The comparison operator, e.g. `=` or `>=`.
	 * @param  string  $value             The value to build the statement for.
	 *
	 * @return string The SQL for the comparison clause.
	 */
	protected function build_where_operator_sql( $table_and_column, $meta_compare, $value ) {
		global $wpdb;

		return $wpdb->prepare( "{$table_and_column} {$meta_compare} %s", $value );
	}

	/**
	 * Returns a SQL statement to check if a redirected custom field exists.
	 *
	 * @since 6.0.0
	 *
	 * @param  string  $table  The name of the table to build the statement for.
	 *
	 * @return string The SQL for the `EXISTS` check.
	 */
	protected function build_where_exists_sql( $table ) {
		/*
		 * Either a post is fully represented in the custom tables, or it's not. It does
		 * exist if LEFT JOIN does not yield a NULL `post_id`.
		 */

		return "{$table}.post_id IS NOT NULL";
	}

	/**
	 * Returns a SQL statement to check if a redirected custom field not exists.
	 *
	 * @since 6.0.0
	 *
	 * @param  string  $table  The name of the table to build the statement for.
	 *
	 * @return string The SQL for the `NOT EXISTS` check.
	 */
	protected function build_where_not_exists_sql( $table ) {
		/*
		 * Either a post is fully represented in the custom tables, or it's not. It does not
		 * exist if LEFT JOIN yields a NULL `post_id`.
		 */
		return "{$table}.post_id IS NULL";
	}

	/**
	 * Returns the SQL fragment required to run a `LIKE` or similar statement.
	 *
	 * @since 6.0.0
	 *
	 * @param  string  $table_and_column  The table and column string, with CAST if required.
	 * @param  string  $meta_compare      The comparison operator, one of `LIKE` or `NOT LIKE`.
	 * @param  string  $value             The value to build the `LIKE` statement for.
	 *
	 * @return string The SQL for the `LIKE` clause.
	 */
	protected function build_where_like_sql( $table_and_column, $meta_compare, $value ) {
		global $wpdb;
		$like_escaped_value = '%' . $wpdb->esc_like( $value ) . '%';

		return $wpdb->prepare( "{$table_and_column} {$meta_compare} %s", $like_escaped_value );
	}

	/**
	 * Returns the SQL fragment required to run a `IN` or similar statement.
	 *
	 * @since 6.0.0
	 *
	 * @param  string        $table_and_column  The table and column string, with CAST if required.
	 * @param  string        $meta_compare      The comparison operator, one of `IN` or `NOT IN`.
	 * @param  array<mixed>  $value             The values to build the IN set from.
	 *
	 * @return string The SQL for the `IN` clause.
	 */
	protected function build_where_in_sql( $table_and_column, $meta_compare, $value ) {
		global $wpdb;
		$values_set = (array) $value;
		$set_string = implode( ',', array_fill( 0, count( $values_set ), '%s' ) );

		return $wpdb->prepare( "{$table_and_column} {$meta_compare} ({$set_string})", ...$values_set );
	}

	/**
	 * Returns the SQL fragment required to run a `REGEXP` or similar statement.
	 *
	 * @since 6.0.0
	 *
	 * @param  string               $table_and_column  The table and column string, with CAST if required.
	 * @param  string               $meta_compare      The comparison operator, one of `REGEXP`, `REGLIKE` or
	 *                                                 `REGEXP_LIKE`.
	 * @param  array<string,mixed>  $clause            The clause in array format.
	 *
	 * @return string The SQL for the `REGEXP` clause.
	 */
	protected function build_where_regexp_sql( $table_and_column, $meta_compare, array $clause ) {
		global $wpdb;
		$cast = isset( $clause['type'] ) && 'BINARY' === strtoupper( $clause['type'] ) ? 'BINARY' : '';

		return $wpdb->prepare( "{$table_and_column} {$meta_compare} {$cast} %s", trim( $clause['value'] ) );
	}

	/**
	 * Returns the SQL fragment required to run a `BETWEEN` or `NOT BETWEEN` statement.
	 *
	 * @since 6.0.0
	 *
	 * @param  string        $table_and_column  The table and column string, with CAST if required.
	 * @param  string        $meta_compare      The comparison operator, either `BETWEEN` or `NOT BETWEEN`.
	 * @param  array<mixed>  $value             The two values to build the interval for.
	 *
	 * @return string The SQL for the `BETWEEN` clause.
	 */
	protected function build_where_between_sql( $table_and_column, $meta_compare, $value ) {
		global $wpdb;
		$values_set = array_slice( (array) $value, 0, 2 );

		return $wpdb->prepare( "{$table_and_column} {$meta_compare} %s AND %s", ... $values_set );
	}

	/**
	 * Returns a SQL statement for the default check.
	 *
	 * @since 6.0.0
	 *
	 * @param  mixed  $value  The value to prepare the SQL for.
	 *
	 * @return string The SQL for the check.
	 */
	protected function build_where_default_sql( $value ) {
		global $wpdb;

		return $wpdb->prepare( '%s', $value );
	}

	/**
	 * Overrides the base method to return, for the custom fields controlled by the query, the custom table name.
	 *
	 * @since 6.0.0
	 *
	 * @param  array<string,mixed>         $clause         The clause in array format, specifying the key, value,
	 *                                                     comparison operator and so on.
	 * @param  array<array<string,mixed>>  $parent_query   The parent query, a set of clauses.
	 *
	 * @return string|false A table name (for custom fields whose that should be redirected to the plugin custom
	 *                      tables), a `postmeta` table alias or `false` to indicate a table alias should be generated
	 *                      for the clause.
	 */
	protected function find_compatible_table_alias( $clause, $parent_query ) {
		if ( isset(
			$clause['original_meta_key'],
			$this->meta_key_redirection_map[ $clause['original_meta_key'] ]['table']
		) ) {
			return $this->meta_key_redirection_map[ $clause['original_meta_key'] ]['table'];
		}

		return parent::find_compatible_table_alias( $clause, $parent_query );
	}

	/**
	 * Returns whether the Custom Tables Meta Query did JOIN on a specific table or not.
	 *
	 * @since 6.0.0
	 *
	 * @param string|array<string> $table The table(s) to check.
	 *
	 * @return bool Whether the Custom Tables Meta Query did JOIN on a specific table or not.
	 */
	public function did_join_table( $table ) {
		foreach ( (array) $table as $t ) {
			if ( ! isset( $this->joined_tables[ $t ] ) ) {
				return false;
			}
		}

		return true;
	}
}
