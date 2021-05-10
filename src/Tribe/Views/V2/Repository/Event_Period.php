<?php
/**
 * A period-based repository to fetch events.
 *
 * @since   4.9.13
 *
 * @package Tribe\Events\Views\V2\Repository
 */

namespace Tribe\Events\Views\V2\Repository;

use Tribe\Repository\Core_Read_Interface;
use Tribe\Repository\Filter_Validation;
use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use Tribe__Repository__Read_Interface;
use Tribe__Timezones as Timezones;
use Tribe__Utils__Array as Arr;
use WP_Post;

/**
 * Class Event_Period
 *
 * @since   4.9.13
 *
 * @package Tribe\Events\Views\V2\Repository
 */
class Event_Period implements Core_Read_Interface {
	use Filter_Validation;

	/**
	 * A definition of each filter required argument count and nature.
	 *
	 * @since 4.9.13
	 *
	 * @var array
	 */
	protected static $filter_args_map = [
		'period' => [
			'start date' => [ Dates::class, 'is_valid_date' ],
			'end date'   => [ Dates::class, 'is_valid_date' ]
		],
	];
	/**
	 * Whether the repository should cache sets and results in WP cache or not.
	 *
	 * @since 4.9.13
	 *
	 * @var bool
	 */
	public $cache_results = false;
	/**
	 * The period start date.
	 *
	 * @since 4.9.13
	 *
	 * @var \DateTime
	 */
	protected $period_start;

	/**
	 * The period end date.
	 *
	 * @since 4.9.13
	 *
	 * @var \DateTime
	 */
	protected $period_end;

	/**
	 * A flag property to indicate whether the sets should be fetched and built using the site timezone or not.
	 *
	 * @since 4.9.13
	 *
	 * @var bool
	 */
	protected $use_site_timezone;

	/**
	 * The last fetched sets.
	 *
	 * @since 4.9.13
	 *
	 * @var Events_Result_Set[]
	 */
	protected $sets;

	/**
	 * The "base" repository used by this repository.
	 * This repository will handle any non-period related filter.
	 *
	 * @since 4.9.13
	 *
	 * @var \Tribe__Repository__Interface
	 */
	protected $base_repository;

	/**
	 * A flag property to indicate whether there are filters for the base repository or not.
	 *
	 * @since 4.9.13
	 *
	 * @var bool
	 */
	protected $has_base_filters = false;

	/**
	 * Batch filter application method.
	 *
	 * This is the same as calling `by` multiple times with different arguments.
	 *
	 * @since 4.7.19
	 *
	 * @param array $args An associative array of arguments to filter
	 *                    the posts by in the shape [ <key>, <value> ]. * * @return Tribe__Repository__Read_Interface
	 */
	public function by_args( array $args ) {
		// @todo [BTRIA-595]: Implement by_args() method.
	}

	/**
	 * Just an alias of the `by` method to allow for easier reading.
	 *
	 * @since 4.7.19
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function where( $key, $value = null ) {
		return $this->by( ...func_get_args() );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 4.9.13
	 */
	public function by( $key, $value = null ) {
		$original_by_key = $key;
		$key             = preg_replace( '/^(on|in)_/', '', $key );

		$call_args = func_get_args();

		$method = 'by_' . $key;

		if ( ! method_exists( $this, $method ) ) {
			// Redirect the call to the base repository.
			$this->has_base_filters = true;
			$this->base_repository()->by( $original_by_key, ...array_slice( $call_args, 1 ) );

			return $this;
		}

		$this->ensure_args_for_filter( $key, $call_args );

		array_shift( $call_args );

		return $this->{$method}( ...$call_args );
	}

	/**
	 * Returns the base event repository used by this repository.
	 *
	 * @since 4.9.13
	 *
	 * @return \Tribe__Repository__Interface The base repository instance used by this repository.
	 */
	public function base_repository() {
		if ( null !== $this->base_repository ) {
			return $this->base_repository;
		}

		$this->base_repository = tribe_events();

		return $this->base_repository;
	}

	/**
	 * Sets the page of posts to fetch.
	 *
	 * Mind that this implementation does not support a `by( 'page', 2 )`
	 * filter to force more readable code.
	 *
	 * @since 4.7.19
	 *
	 * @param int $page
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function page( $page ) {
		// @todo [BTRIA-595]: Implement page() method.
	}

	/**
	 * Sets the number of posts to retrieve per page.
	 *
	 * Mind that this implementation does not support a `by( 'per_page', 5 )`
	 * filter to force more readable code; by default posts per page is set to
	 * the pagination defaults for the post type.
	 *
	 * @param int $per_page
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function per_page( $per_page ) {
		// @todo [BTRIA-595]: Implement per_page() method.
	}

	/**
	 * Returns the number of posts found matching the query.
	 *
	 * Mind that this value ignores the offset returning the
	 * number of results if limits where not applied.
	 *
	 * @since 4.7.19
	 *
	 * @return int
	 */
	public function found() {
		// @todo [BTRIA-595]: Implement found() method.
	}

	/**
	 * Returns all posts matching the query.
	 *
	 * Mind that "all" means "all the posts matching all the filters" so pagination applies.
	 *
	 * @return array
	 */
	public function all() {
		// @todo [BTRIA-595]: Implement all() method.
	}

	/**
	 * Sets the offset on the query.
	 *
	 * Mind that this implementation does not support a `by( 'offset', 2 )`
	 * filter to force more readable code.
	 *
	 * @since 4.7.19
	 *
	 * @param int  $offset
	 * @param bool $increment Whether to increment the offset by the value
	 *                        or replace it.
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function offset( $offset, $increment = false ) {
		// @todo [BTRIA-595]: Implement offset() method.
	}

	/**
	 * Sets the order on the query.
	 *
	 * Mind that this implementation does not support a `by( 'order', 2 )`
	 * filter to force more readable code.
	 *
	 * @since 4.7.19
	 *
	 * @param string $order
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function order( $order = 'ASC' ) {
		// @todo [BTRIA-595]: Implement order() method.
	}

	/**
	 * Sets the order criteria results should be fetched by.
	 *
	 * Mind that this implementation does not support a `by( 'order_by', 'title' )`
	 * filter to force more readable code.
	 *
	 * @since 4.7.19
	 *
	 * @param string $order_by The post field, custom field or alias key to order posts by.
	 * @param string $order    The order direction; optional; shortcut for the `order` method; defaults
	 *                         to `DESC`.
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function order_by( $order_by, $order = 'DESC' ) {
		// @todo [BTRIA-595]: Implement order_by() method.
	}

	/**
	 * Sets the fields that should be returned by the query.
	 *
	 * Mind that this implementation does not support a `by( 'fields', 'ids' )`
	 * filter to force more readable code.
	 *
	 * @since 4.7.19
	 *
	 * @param string $fields
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function fields( $fields ) {
		// @todo [BTRIA-595]: Implement fields() method.
	}

	/**
	 * Sugar method to set the `post__in` argument.
	 *
	 * Successive calls will stack, not replace each one.
	 *
	 * @since 4.7.19
	 *
	 * @param array|int $post_ids
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function in( $post_ids ) {
		// @todo [BTRIA-595]: Implement in() method.
	}

	/**
	 * Sugar method to set the `post__not_in` argument.
	 *
	 * Successive calls will stack, not replace each one.
	 *
	 * @since 4.7.19
	 *
	 * @param array|int $post_ids
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function not_in( $post_ids ) {
		// @todo [BTRIA-595]: Implement not_in() method.
	}

	/**
	 * Sugar method to set the `post_parent__in` argument.
	 *
	 * Successive calls will stack, not replace each one.
	 *
	 * @since 4.7.19
	 *
	 * @param array|int $post_id
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function parent( $post_id ) {
		// @todo [BTRIA-595]: Implement parent() method.
	}

	/**
	 * Sugar method to set the `post_parent__in` argument.
	 *
	 * Successive calls will stack, not replace each one.
	 *
	 * @since 4.7.19
	 *
	 * @param array $post_ids
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function parent_in( $post_ids ) {
		// @todo [BTRIA-595]: Implement parent_in() method.
	}

	/**
	 * Sugar method to set the `post_parent__not_in` argument.
	 *
	 * Successive calls will stack, not replace each one.
	 *
	 * @since 4.7.19
	 *
	 * @param array $post_ids
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function parent_not_in( $post_ids ) {
		// @todo [BTRIA-595]: Implement parent_not_in() method.
	}

	/**
	 * Sugar method to set the `s` argument.
	 *
	 * Successive calls will replace the search string.
	 * This is the default WordPress searh, to search by title,
	 * content or excerpt only use the `title`, `content`, `excerpt` filters.
	 *
	 * @param $search
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function search( $search ) {
		// @todo [BTRIA-595]: Implement search() method.
	}

	/**
	 * Returns the number of posts found matching the query in the current page.
	 *
	 * While the `found` method will return the number of posts found
	 * across all pages this method will only return the number of
	 * posts found in the current page.
	 * Differently from the `found` method this method will apply the
	 * offset if set.
	 *
	 * @since 4.7.19
	 *
	 * @return int
	 */
	public function count() {
		// @todo [BTRIA-595]: Implement count() method.
	}

	/**
	 * Returns the first post of the page matching the current query.
	 *
	 * If, by default or because set with the `per_page` method, all
	 * posts matching the query should be returned then this will be
	 * the first post of all those matching the query.
	 *
	 * @since 4.7.19
	 *
	 * @return WP_Post|mixed|null
	 *
	 * @see   Tribe__Repository__Read_Interface::per_page()
	 */
	public function first() {
		// @todo [BTRIA-595]: Implement first() method.
	}

	/**
	 * Returns the last post of the page matching the current query.
	 *
	 * If, by default or because set with the `per_page` method, all
	 * posts matching the query should be returned then this will be
	 * the last post of all those matching the query.
	 *
	 * @since 4.7.19
	 *
	 * @return WP_Post|mixed|null
	 *
	 * @see   Tribe__Repository__Read_Interface::per_page()
	 */
	public function last() {
		// @todo [BTRIA-595]: Implement last() method.
	}

	/**
	 * Returns the nth post (1-based) of the page matching the current query.
	 *
	 * Being 1-based the second post can be fetched using `nth( 2 )`.
	 * If, by default or because set with the `per_page` method, all
	 * posts matching the query should be returned then this will be
	 * the nth post of all those matching the query.
	 *
	 * @since 4.7.19
	 *
	 * @param int $n
	 *
	 * @return WP_Post|mixed|null
	 *
	 * @see   Tribe__Repository__Read_Interface::per_page()
	 */
	public function nth( $n ) {
		// @todo [BTRIA-595]: Implement nth() method.
	}

	/**
	 * Returns the first n posts of the page matching the current query.
	 *
	 * If, by default or because set with the `per_page` method, all
	 * posts matching the query should be returned then this method will
	 * return the first n posts of all those matching the query.
	 *
	 * @since 4.7.19
	 *
	 * @return array An array of posts matching the query.
	 *
	 * @see   Tribe__Repository__Read_Interface::per_page()
	 */
	public function take( $n ) {
		// @todo [BTRIA-595]: Implement take() method.
	}

	/**
	 * Plucks a field from all results and returns it.
	 *
	 * This method will implicitly build and use a `WP_List_Util` instance on the return
	 * value of a call to the `all` method.
	 *
	 * @since 4.9.5
	 *
	 * @param string $field The field to pluck from each result.
	 *
	 * @return array An array of the plucked results.
	 *
	 * @see   \wp_list_pluck()
	 */
	public function pluck( $field ) {
		// @todo [BTRIA-595]: Implement pluck() method.
	}

	/**
	 * Filters the results according to the specified criteria.
	 *
	 * This method will implicitly build and use a `WP_List_Util` instance on the return
	 * value of a call to the `all` method.
	 *
	 * @since 4.9.5
	 *
	 * @param array  $args     Optional. An array of key => value arguments to match
	 *                         against each object. Default empty array.
	 * @param string $operator Optional. The logical operation to perform. 'AND' means
	 *                         all elements from the array must match. 'OR' means only
	 *                         one element needs to match. 'NOT' means no elements may
	 *                         match. Default 'AND'.
	 *
	 * @return array An array of the filtered results.
	 *
	 * @see   \wp_list_filter()
	 */
	public function filter( $args = array(), $operator = 'AND' ) {
		// @todo [BTRIA-595]: Implement filter() method.
	}

	/**
	 * Sorts the results according to the specified criteria.
	 *
	 * This method will implicitly build and use a `WP_List_Util` instance on the return
	 * value of a call to the `all` method.
	 *
	 * @since 4.9.5
	 *
	 * @param string|array $orderby       Optional. Either the field name to order by or an array
	 *                                    of multiple orderby fields as $orderby => $order.
	 * @param string       $order         Optional. Either 'ASC' or 'DESC'. Only used if $orderby
	 *                                    is a string.
	 * @param bool         $preserve_keys Optional. Whether to preserve keys. Default false.
	 *
	 * @return array An array of the sorted results.
	 *
	 * @see   \wp_list_sort()
	 */
	public function sort( $orderby = array(), $order = 'ASC', $preserve_keys = false ) {
		// @todo [BTRIA-595]: Implement sort() method.
	}

	/**
	 * Builds a collection on the result of the `all()` method call.
	 *
	 * @since 4.9.5
	 *
	 * @return \Tribe__Utils__Post_Collection
	 */
	public function collect() {
		// @todo [BTRIA-595]: Implement collect() method.
	}

	/**
	 * Gets the ids of the posts matching the query.
	 *
	 * @since 4.9.13
	 *
	 * @return array An array containing the post IDs to update.
	 */
	public function get_ids() {
		return $this->get_sets_ids( $this->get_sets() );
	}

	/**
	 * Flattens and returns the post IDs of all events in the a sets collection.
	 *
	 * @since 4.9.13
	 *
	 * @param array $sets The sets to parse.
	 *
	 * @return int[] An array of the sets post IDs.
	 */
	protected function get_sets_ids( array $sets ) {
		$ids = array_filter( array_map(
			static function ( Events_Result_Set $set ) {
				return $set->pluck( 'ID' );
			},
			$sets
		) );

		if ( ! count( $ids ) ) {
			return [];
		}

		$ids = array_values( array_map( 'absint', array_unique( array_merge( ...array_values( $ids ) ) ) ) );

		return $ids;
	}

	/**
	 * Returns an array of result sets, one for each period day.
	 *
	 * @since 4.9.13
	 *
	 * @return Events_Result_Set[] An array of result sets, in the shape `[ <Y-m-d> => <Event_Result_Set> ]`.
	 */
	public function get_sets() {
		if ( null !== $this->sets ) {
			// Do we have them here?
			return $this->get_sub_set( $this->sets, $this->period_start, $this->period_end );
		}

		$results = $this->query_for_sets( $this->period_start, $this->period_end );

		if ( empty( $results ) ) {
			// Store the value, do not run again.
			$this->sets = [];

			return [];
		}

		$raw_sets = $this->group_sets_by_start_date( $results );

		$sets = $this->cast_sets( $raw_sets );
		$sets = $this->add_missing_sets( $sets );

		if ( $this->cache_results ) {
			$this->set_results_cache( $sets );
		}

		$this->sets = $sets;

		if ( $this->has_base_filters ) {
			$this->sets = $this->filter_sets_w_base_repository( $sets );
		}

		return $this->sets;
	}

	/**
	 * Returns the already fetched set, or a sub-set of it.
	 *
	 * @since 4.9.13
	 *
	 * @param array              $sets  The sets, by day, to get the subset from.
	 * @param \DateTimeInterface $start The sub-set start.
	 * @param \DateTimeInterface $end   The sub-set end.
	 *
	 * @return Events_Result_Set[] The result sub-set, or the whole set if the dates are the same.
	 */
	protected function get_sub_set( array $sets, \DateTimeInterface $start, \DateTimeInterface $end ) {
		// The sets might have been previously fetched and be cached.
		$days              = array_keys( $this->sets );
		$request_start_ymd = $start->format( Dates::DBDATEFORMAT );
		$request_end_ymd   = $end->format( Dates::DBDATEFORMAT );
		$same_start        = $request_start_ymd === reset( $days );
		$same_end          = $request_end_ymd === end( $days );

		if ( $same_start && $same_end ) {
			return $this->sets;
		}

		if ( $request_start_ymd === $request_end_ymd ) {
			// It's a single day query, just return it.
			return isset( $this->sets[ $request_start_ymd ] ) ? [ $this->sets[ $request_start_ymd ] ] : [];
		}

		// Let's restrict results to the current request period.
		$offset = array_search( $request_start_ymd, $days, true );
		$length = array_search( $request_end_ymd, $days, true ) - $offset;

		return array_slice( $sets, $offset, $length, true );
	}

	/**
	 * Queries the database to fetch the sets.
	 *
	 * @since 4.9.13
	 *
	 * @return array|false Either the results of the query, or `false` on error.
	 */
	protected function query_for_sets( \DateTimeInterface $start, \DateTimeInterface $end ) {
		// Let's try and set the LIMIT as high as we can.
		/** @var \Tribe__Feature_Detection $feature_detection */
		$feature_detection = tribe( 'feature-detection' );
		// Results will not be JSON, but this is a good approximation.
		$example = '{"ID":"23098402348023849","start_date":"2019-11-18 08:00:00",' .
		           '"end_date":"2019-11-18 17:00:00","timezone":"America\/New_York","all_day":null,' .
		           '"post_status":"publish"}';
		$limit   = $feature_detection->mysql_limit_for_string( $example );

		/**
		 * Filters the LIMIT that should be used to fetch event results set from the database.
		 *
		 * Lower this value on less powerful hosts.
		 *
		 * @since 4.9.13
		 *
		 * @param int                $limit The SQL LIMIT to use for result set fetching.
		 * @param static             $this  The current repository instance.
		 * @param \DateTimeInterface $start The period start date.
		 * @param \DateTimeInterface $end   The period end date.
		 */
		$limit = apply_filters( 'tribe_events_event_period_repository_set_limit', $limit, $this, $start, $end );
		$limit = absint( $limit );

		$starting_before_period_end = $this->query_for_sets_starting_before_period_end( $limit, $end );

		if ( empty( $starting_before_period_end ) ) {
			return [];
		}

		$results = $this->query_for_sets_ending_after_period_start(
			$limit,
			$start,
			wp_list_pluck( $starting_before_period_end, 'ID' )
		);

		if ( empty( $results ) ) {
			return [];
		}

		$starting_before_period_end = array_combine(
			wp_list_pluck( $starting_before_period_end, 'ID' ),
			$starting_before_period_end
		);

		foreach ( $results as &$result ) {
			$result['start_date']  = $starting_before_period_end[ $result['ID'] ]['start_date'];
			$result['post_status'] = $starting_before_period_end[ $result['ID'] ]['post_status'];
		}
		unset( $result );

		$post_ids         = wp_list_pluck( $results, 'ID' );
		$timezone_details = $this->query_for_meta( $limit, '_EventTimezone', $post_ids );
		$all_day_details  = $this->query_for_meta( $limit, '_EventAllDay', $post_ids, 'LEFT' );

		foreach ( $results as &$result ) {
			$result ['timezone'] = $timezone_details[ $result['ID'] ]['_EventTimezone'];
			$result ['all_day']  = (bool) $all_day_details[ $result['ID'] ]['_EventAllDay'];
		}
		unset( $result );

		return $results;
	}

	/**
	 * Queries for all the events that start before the period ends.
	 *
	 * @since 4.9.13
	 *
	 * @param int                $limit   The value of the LIMIT that should be respected to send queries (in respect
	 *                                    to the
	 *                                    `$post_in` parameter) or fetch results (the SQL LIMIT clause). This limit
	 *                                    should be defined using the
	 *                                    `Tribe__Feature_Detection::mysql_limit_for_example` method.
	 * @param \DateTimeInterface $end     The period end date.
	 * @param array              $post_in An array of post IDs to limit the search to.
	 *
	 * @return array A result set, an array of arrays in the shape `[ <ID> => [ 'ID' => <ID>, 'start_date' =>
	 *               <start_date> ] ]`;
	 */
	protected function query_for_sets_starting_before_period_end(
		$limit,
		\DateTimeInterface $end,
		array $post_in = []
	) {
		global $wpdb;
		$post_type = TEC::POSTTYPE;

		$query = "
		SELECT p.ID,
			p.post_status,
	   		start_date.meta_value AS 'start_date'

		FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} start_date
					ON (p.ID = start_date.post_id AND start_date.meta_key = %s)

		WHERE p.post_type = '{$post_type}'
			-- Starts before the period ends.
			AND start_date.meta_value <= %s";

		$prepare_args = [
			$this->use_site_timezone ? '_EventStartDateUTC' : '_EventStartDate',
			$end->format( Dates::DBDATETIMEFORMAT ),
		];

		return $this->query_w_limit( $limit, $query, $prepare_args, $post_in );
	}

	/**
	 * Runs a query within a SQL LIMIT.
	 *
	 * The method will run multiple queries if the limit is lower than the number of results or the number of post IDs
	 * in the `$post_in` parameter.
	 *
	 * @since 4.9.13
	 *
	 * @param int        $limit        The value of the LIMIT that should be respected to send queries (in respect to
	 *                                 the `$post_in` parameter) or fetch results (the SQL LIMIT clause). This limit
	 *                                 should be defined using the `Tribe__Feature_Detection::mysql_limit_for_example`
	 *                                 method.
	 * @param string     $query        The un-prepared SQL query to run, if should contains placeholders in the format
	 *                                 used by the `wpdb::prepare` method.
	 * @param array|null $prepare_args An array of arguments that will be used, in order, to prepare the query using
	 *                                 the
	 *                                 `wpdb::prepare` method.
	 * @param array|null $post_in      An array of post IDs that will be  used to pivot the query. The `$limit`
	 *                                 parameter will apply to these values too chunking them if they are too many to
	 *                                 avoid hitting MySQL packet size. When applied to post IDs the limit is overly
	 *                                 conservative.
	 *
	 * @return array An array of results. Whether one or more queries ran, the return value will always have the format
	 *               a single query run would have.
	 *
	 * @see   Tribe__Feature_Detection::mysql_limit_for_example for the method that should be used to set the limit.
	 * @see   wpdb::prepare() for the format of the placeholders to use to prepare the query.
	 */
	protected function query_w_limit( $limit, $query, array $prepare_args = [], array $post_in = [] ) {
		global $wpdb;

		$post_in = array_filter( array_unique( array_map( 'absint', $post_in ) ) );

		$prepared = $wpdb->prepare( $query, ...$prepare_args );

		$page    = 0;
		$results = [];
		$chunk   = array_splice( $post_in, 0, $limit );

		do {
			do {
				$interval_where_clause = count( $chunk )
					? 'AND p.ID IN (' . implode( ',', array_map( 'absint', $chunk ) ) . ')'
					: '';

				$limit_clause = sprintf( 'LIMIT %d,%d', $page * $limit, $limit );

				$page ++;

				$this_query    = $prepared . ' ' . $interval_where_clause . ' ' . $limit_clause;
				$these_results = (array) $wpdb->get_results( $this_query, ARRAY_A );
				$results[]     = $these_results;
			} while ( $chunk = array_splice( $post_in, 0, $limit ) );
		} while ( ! empty( $these_results ) && is_array( $these_results ) && count( $these_results ) === $limit );

		return array_merge( ...$results );
	}

	/**
	 * Queries for all the events that end after the period starts.
	 *
	 * @since 4.9.13
	 *
	 * @param int                $limit   The value of the LIMIT that should be respected to send queries (in respect
	 *                                    to the
	 *                                    `$post_in` parameter) or fetch results (the SQL LIMIT clause). This limit
	 *                                    should be defined using the
	 *                                    `Tribe__Feature_Detection::mysql_limit_for_example` method.
	 * @param \DateTimeInterface $start   The period start date.
	 * @param array              $post_in An array of post IDs to limit the search to.
	 *
	 * @return array A result set, an array of arrays in the shape `[ <ID> => [ 'ID' => <ID>, 'end_date' => <end_date>
	 *               ] ]`;
	 */
	protected function query_for_sets_ending_after_period_start(
		$limit,
		\DateTimeInterface $start,
		array $post_in = []
	) {
		global $wpdb;
		$post_type = TEC::POSTTYPE;

		$query = "
		SELECT p.ID,
	   		end_date.meta_value AS 'end_date'

		FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} end_date
					ON (p.ID = end_date.post_id AND end_date.meta_key = %s)

		WHERE p.post_type = '{$post_type}'
			-- Ends after the period starts.
			AND end_date.meta_value >= %s";


		$prepare_args = [
			$this->use_site_timezone ? '_EventEndDateUTC' : '_EventEndDate',
			$start->format( Dates::DBDATETIMEFORMAT )
		];

		return $this->query_w_limit( $limit, $query, $prepare_args, $post_in );
	}

	/**
	 * Queries the database to fetch all the values of a single meta entry for all the post IDs in the datatbase or in
	 * a defined interval.
	 *
	 * @since 4.9.13
	 *
	 * @param int        $limit           The value of the LIMIT that should be respected to send queries (in respect
	 *                                    to the
	 *                                    `$post_in` parameter) or fetch results (the SQL LIMIT clause). This limit
	 *                                    should be defined using the
	 *                                    `Tribe__Feature_Detection::mysql_limit_for_example` method.
	 * @param string     $meta_key        The meta key to fetch from the database, this is the value of the `meta_key`
	 *                                    column, e.g. `_EventTimezone`.
	 * @param array|null $post_ids        An array of post IDs to limit the query.
	 * @param string     $join            The type of JOIN to use; defaults to `INNER`, but `LEFT` should be used when
	 *                                    fetching meta that might be not set for all posts.
	 *
	 * @return array An array of meta results, the post IDs as keys.
	 *
	 * @see   Tribe__Feature_Detection::mysql_limit_for_example for the method that should be used to set the limit.
	 * @see   wpdb::prepare() for the format of the placeholders to use to prepare the query.
	 */
	protected function query_for_meta( $limit, $meta_key, array $post_ids = null, $join = 'INNER' ) {
		global $wpdb;
		$post_type = TEC::POSTTYPE;

		$query = "
		SELECT p.ID, m.meta_value AS %s

		FROM {$wpdb->posts} p
				{$join} JOIN {$wpdb->postmeta} m
					ON (p.ID = m.post_id AND m.meta_key = %s)

		WHERE p.post_type = '{$post_type}'";

		$prepare_args = [ $meta_key, $meta_key ];

		$results = $this->query_w_limit( $limit, $query, $prepare_args, $post_ids );

		return array_combine( wp_list_pluck( $results, 'ID' ), $results );
	}

	/**
	 * Groups a set of raw database results by start date.
	 *
	 * @since 4.9.13
	 *
	 * @param array $results A raw set of database results.
	 *
	 * @return array The database results, grouped by days, in the shape `[ <Y-m-d> => [ ...<results> ] ]`. Each result
	 *               is an instance `Event_Result`.
	 */
	protected function group_sets_by_start_date( $results ) {
		if ( empty( $results ) || ! is_array( $results ) ) {
			return [];
		}

		$site_timezone = Timezones::build_timezone_object();

		$use_site_timezone = $this->use_site_timezone;

		$one_day = Dates::interval( 'P1D' );

		$sets_by_day = array_reduce( $results,
			static function ( array $buffer, array $result ) use ( $use_site_timezone, $site_timezone, $one_day ) {
				$display_timezone = $use_site_timezone
					? $site_timezone
					: Timezones::build_timezone_object( $result['timezone'] );
				$start_date       = Dates::build_date_object( $result['start_date'], $display_timezone );
				$end_date         = Dates::build_date_object( $result['end_date'], $display_timezone );
				if (
					$start_date->format( Dates::DBDATEFORMAT ) === $end_date->format( Dates::DBDATEFORMAT )
				) {
					$overlapping_days = [ $start_date->format( Dates::DBDATEFORMAT ) ];
				} else {
					/*
					 * "Move" the end date, adding a day to it, to make sure the end date is included in the period.
					 * Else multi-day events would only overlap the first two dates.
					 */
					$moved_end_date = clone $end_date;
					$moved_end_date->add( $one_day );
					$period           = new \DatePeriod( $start_date, $one_day, $moved_end_date );
					$overlapping_days = [];
					/** @var \DateTimeInterface $d */
					foreach ( $period as $d ) {
						// This is skipping the end day on multi-day events.
						$overlapping_days[] = $d->format( Dates::DBDATEFORMAT );
						// Sanity check: break when the current day is equal to the event end date.
						$reached_end = $d->format( Dates::DBDATEFORMAT )
						               === $end_date->format( Dates::DBDATEFORMAT );
						if ( $reached_end ) {
							break;
						}
					}
				}

				// Normalize the timezone to the site one.
				$result['start_date'] = $start_date->setTimezone( $site_timezone )->format( 'Y-m-d H:i:s' );
				$result['end_date']   = $end_date->setTimezone( $site_timezone )->format( Dates::DBDATEFORMAT );

				foreach ( $overlapping_days as $overlap_day ) {
					if ( isset( $buffer[ $overlap_day ] ) ) {
						$buffer[ $overlap_day ][] = new Event_Result( $result );
					} else {
						$buffer[ $overlap_day ] = [ new Event_Result( $result ) ];
					}
				}

				return $buffer;
			}, [] );


		return $sets_by_day;
	}

	/**
	 * Casts each set to an `Event_Result_Set`.
	 *
	 * @since 4.9.13
	 *
	 * @param array $raw_sets The raw sets.
	 *
	 * @return array The set, each element cast to an `Event_Result_Set`.
	 */
	protected function cast_sets( array $raw_sets ) {
		$sets = array_combine(
			array_keys( $raw_sets ),
			array_map( static function ( $raw_set ) {
				return Events_Result_Set::from_value( $raw_set );
			}, $raw_sets )
		);

		return $sets;
	}

	/**
	 * Adds to the sets any missing day.
	 *
	 * @since 4.9.13
	 *
	 * @param array $sets The current sets, by day.
	 *
	 * @return array The filled sets.
	 */
	protected function add_missing_sets( array $sets ) {
		$period = new \DatePeriod( $this->period_start, Dates::interval( 'P1D' ), $this->period_end );
		foreach ( $period as $day ) {
			$day_string = $day->format( Dates::DBDATEFORMAT );
			if ( ! array_key_exists( $day_string, $sets ) ) {
				$sets[ $day_string ] = new Events_Result_Set();
			}
		}

		ksort( $sets );

		return $sets;
	}

	/**
	 * Caches the resulting sets using `Tribe__Cache`.
	 *
	 * As a result sets might be cached either in a real object cache or in transients.
	 *
	 * @param array $sets The sets to cache.
	 */
	protected function set_results_cache( $sets ) {
		$days = array_keys( $sets );
		// EOD cutoff does not apply here, we just do it for the interval.
		$start          = Dates::build_date_object( reset( $days ) )->setTime( 0, 0, 0 );
		$end            = Dates::build_date_object( end( $days ) )->setTime( 23, 59, 59 );
		$one_day        = Dates::interval( 'P1D' );
		$request_period = new \DatePeriod( $start, $one_day, $end );

		/** @var \Tribe__Cache $cache */
		$cache   = tribe( 'cache' );
		$trigger = Cache_Listener::TRIGGER_SAVE_POST;

		$periods_key      = self::get_cache_key( 'periods' );
		$cached_periods   = (array) $cache->get_transient( $periods_key, $trigger );
		$cached_periods[] = [ $start->format( Dates::DBDATEFORMAT ), $end->format( Dates::DBDATEFORMAT ) ];
		$cache->set_transient( $periods_key, $cached_periods, WEEK_IN_SECONDS, $trigger );

		/** @var \DateTime $day */
		foreach ( $request_period as $day ) {
			$day_string        = $day->format( Dates::DBDATEFORMAT );
			$day_event_results = Arr::get( $sets, $day_string, [] );
			$cache->set_transient(
				static::get_cache_key( $day_string . '_set' ),
				$day_event_results,
				WEEK_IN_SECONDS,
				$trigger
			);
		}
	}

	/**
	 * Returns the full cache key for a partial key.
	 *
	 * @since 4.9.13
	 *
	 * @param string $key The partial key.
	 *
	 * @return string The full cache key.
	 */
	private static function get_cache_key( $key ) {
		$key = preg_replace( '/^tribe_event_period_repository_/', '', $key );

		return 'tribe_event_period_repository_' . $key;
	}

	/**
	 * Further filters the sets using a default event repository to handle the non-period related filters.
	 *
	 * @since 4.9.13
	 *
	 * @param array $sets The sets found by this repository so far.
	 */
	protected function filter_sets_w_base_repository( array $sets ) {
		// Restrict the base repository to only operate on the IDs we already have.
		$matching_ids = $this->base_repository->in( $this->get_sets_ids( $sets ) )->get_ids();

		if ( empty( $matching_ids ) ) {
			return [];
		}

		/** @var Events_Result_Set $set */
		$filtered_sets = array_map( static function ( Events_Result_Set $set ) use ( $matching_ids ) {
			return $set->filter( static function ( Event_Result $result ) use ( $matching_ids ) {
				return in_array( $result->id(), $matching_ids, true );
			} );
		}, $sets );

		return $filtered_sets;
	}

	/**
	 * An alias of the `get_sets` method to stick with the convention of naming database-querying methods w/ "fetch".
	 *
	 * This method will "warm up" the instance cache of the repository fetching the events in the period.
	 *
	 * @since 4.9.13
	 */
	public function fetch() {
		$this->get_sets();
	}

	/**
	 * Short-hand to fetch events for a single date.
	 *
	 * A wrapper around the `by_period` method.
	 *
	 * @since 4.9.13
	 *
	 * @param string|int|\DateTimeInterface $date The day date.
	 *
	 * @return $this For chaining.
	 */
	public function by_date( $date ) {
		$normalized = Dates::build_date_object( $date )->format( Dates::DBDATEFORMAT );

		return $this->by_period( tribe_beginning_of_day( $normalized ), tribe_end_of_day( $normalized ) );
	}

	/**
	 * Sets up the filter to fetch events sets in a period.
	 *
	 * @since 4.9.13
	 *
	 * @param string|int|\DateTimeInterface $start_date The period start date.
	 * @param string|int|\DateTimeInterface $end_date   The period end date.
	 *
	 * @return static For chaining.
	 */
	public function by_period( $start_date, $end_date ) {
		if ( null !== $this->sets ) {
			// Do we REALLY need to re-fetch?
			$the_start = Dates::build_date_object( $start_date );
			$the_end   = Dates::build_date_object( $end_date );
			$set_days  = array_keys( $this->sets );

			if (
				$the_start->format( Dates::DBDATEFORMAT ) < reset( $set_days )
				|| $the_end->format( Dates::DBDATEFORMAT ) > end( $set_days )
			) {
				// We need to re-fetch.
				$this->sets = null;
			}
		}

		$this->period_start      = Dates::build_date_object( $start_date );
		$this->period_end        = Dates::build_date_object( $end_date );
		$this->use_site_timezone = Timezones::is_mode( Timezones::SITE_TIMEZONE );

		if ( $this->sets === null && $this->cache_results ) {
			// Maybe fetch them from the cache?
			$this->sets = $this->fetch_cached_sets();
		}

		return $this;
	}

	/**
	 * Try and fetch sets from cache to share data between diff. instances of the repository.
	 *
	 * In cache we store periods.
	 * A cached period has a start and an end.
	 * If the current request period overlaps a cached period, then we fetch sets for each day in the period from the
	 * cache.
	 *
	 * @since 4.9.13
	 *
	 * @return array|null Either a set of results fetched from the cache, or `null` if nothing was found in cache.
	 */
	protected function fetch_cached_sets() {
		/** @var \Tribe__Cache $cache */
		$cache   = tribe( 'cache' );
		$trigger = Cache_Listener::TRIGGER_SAVE_POST;
		// Try and fetch them from the shared cache.
		$periods_key = static::get_cache_key( 'periods' );

		$cached_periods = (array)$cache->get_transient( $periods_key, $trigger );

		foreach ( $cached_periods as list( $start_date, $end_date ) ) {
			if (
				$this->period_start->format( Dates::DBDATEFORMAT ) <= $end_date
				&& $this->period_end->format( Dates::DBDATEFORMAT ) >= $start_date
			) {
				$sets   = [];
				$period = new \DatePeriod(
					$this->period_start,
					Dates::interval( 'P1D' ),
					$this->period_end
				);
				/** @var \DateTimeInterface $day */
				foreach ( $period as $day ) {
					$day_string = $day->format( Dates::DBDATEFORMAT );
					$cached     = $cache->get_transient( static::get_cache_key( $day_string . '_set' ), $trigger );

					$sets[ $day_string ] = Events_Result_Set::from_value( $cached );
				}

				return $sets;
				break;
			}
		}

		return null;
	}

	/**
	 * Shorthand method to get the first set of a search.
	 *
	 * @since 4.9.13
	 *
	 * @return Events_Result_Set Either the first found set, or an empty set.
	 */
	public function get_set() {
		$sets = $this->get_sets();

		return count( $sets ) ? reset( $sets ) : new Events_Result_Set;
	}

	/**
	 * Sets, or unsets if the passed value is `null`, the base repository used by this repository.
	 *
	 * @since 4.9.13
	 *
	 * @param Core_Read_Interface $base_repository The base repository this repository should use; a `null` value will
	 *                                             unset it.
	 */
	public function set_base_repository( Core_Read_Interface $base_repository = null ) {
		$this->base_repository = $base_repository;
	}
}
