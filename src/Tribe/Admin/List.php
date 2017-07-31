<?php
/**
 * Controls Tribe Events Calendar admin list views for events
 */
class Tribe__Events__Admin__List extends Tribe__Template {
	protected static $start_col_active = true;
	protected static $end_col_active = true;
	protected static $start_col_first = true;

	/**
	 * Constructor for Tribe__Events__Admin__List
	 *
	 * @since  TBD
	 */
	public function __construct() {
		// Configure the Admin Template Variables
		$this->set_template_origin( Tribe__Events__Main::instance() );
		$this->set_template_folder( 'src/admin-views/list' );
	}

	/**
	 * Initialize the Listing of Events
	 *
	 * Currently Hooked to: `admin_init`
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function init() {
		$action = Tribe__Main::instance()->doing_ajax() ? 'admin_init' : 'current_screen';
		add_action( $action, array( $this, 'configure_screen' ) );
	}

	/**
	 * Initialize the Listing
	 *
	 * Currently Hooked to: `current_screen`
	 *
	 * @since  TBD
	 *
	 * @param  WP_Screen  $screen  Which screen in wp-admin we are in
	 *
	 * @return void
	 */
	public function configure_screen( $screen = null ) {
		$post_type = Tribe__Events__Main::POSTTYPE;

		// If we are dealing with a AJAX call just drop these checks
		if ( $screen instanceof WP_Post ) {
			if ( 'edit' !== $screen->base ) {
				return;
			}

			if ( $post_type !== $screen->post_type ) {
				return;
			}
		}

		// Logic for sorting events by event category or tags
		add_filter( 'posts_clauses', array( $this, 'sort_by_tax' ), 10, 2 );

		// Logic for sorting events by start or end date
		add_filter( 'posts_clauses', array( $this, 'sort_by_event_date' ), 11, 2 );

		add_filter( 'posts_fields', array( $this, 'events_search_fields' ), 10, 2 );

		// Pagination
		add_filter( 'post_limits', array( $this, 'events_search_limits' ), 10, 2 );

		add_filter( "tribe_apm_headers_{$post_type}" , array( $this, 'column_headers_check' ) );

		add_filter( "views_edit-{$post_type}", array( $this, 'filter_views' ), 100, 1 );
		add_filter( 'tribe_post_count_sql', array( $this, 'filter_add_join_event_start_meta' ) );

		add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'display_columns' ), 10, 2 );
		add_filter( "manage_{$post_type}_posts_columns", array( $this, 'column_headers' ) );

		// Registers event start/end date as sortable columns
		add_action( "manage_edit-{$post_type}_sortable_columns", array( $this, 'register_sortable_columns' ), 10, 2 );
	}

	/**
	 * Fields filter for standard wordpress templates.  Adds the start and end date to queries in the
	 * events category
	 *
	 * @since  TBD
	 *
	 * @param string   $fields The current fields query part.
	 * @param WP_Query $query
	 *
	 * @return string The modified form.
	 */
	public function events_search_fields( $fields, $query ) {
		if ( ! $query->is_main_query() || $query->get( 'post_type' ) != Tribe__Events__Main::POSTTYPE ) {
			return $fields;
		}

		$fields .= ', tribe_event_start_date.meta_value as EventStartDate, tribe_event_end_date.meta_value as EventEndDate ';

		return $fields;
	}

	/**
	 * Sets whether sorting will be ascending or descending based on input
	 *
	 * @since  TBD
	 *
	 * @param   WP_Query    $wp_query   Query for a library post type
	 *
	 * @return  string                  ASC/DESC prefixed with a single space
	 */
	public function get_sort_direction( WP_Query $wp_query ) {
		return 'ASC' == strtoupper( $wp_query->get( 'order' ) ) ? 'ASC' : 'DESC';
	}

	/**
	 * Defines custom logic for sorting events table by start/end date. No matter how user selects
	 * what should be is sorted, always include date sorting in some fashion
	 *
	 * @since  TBD
	 *
	 * @param   Array       $clauses    SQL clauses for fetching posts
	 * @param   WP_Query    $wp_query   A paginated query for items
	 *
	 * @return  Array                   Modified SQL clauses
	 */
	public function sort_by_event_date( Array $clauses, WP_Query $wp_query ) {
		// bail if this is not a query for event post type
		if ( $wp_query->get( 'post_type' ) !== Tribe__Events__Main::POSTTYPE ) {
			return $clauses;
		}

		global $wpdb;

		$sort_direction = self::get_sort_direction( $wp_query );

		// only add the start meta query if it is missing
		if ( ! preg_match( '/tribe_event_start_date/', $clauses['join'] ) ) {
			$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS tribe_event_start_date ON {$wpdb->posts}.ID = tribe_event_start_date.post_id AND tribe_event_start_date.meta_key = '_EventStartDate' ";
		}

		// only add the end meta query if it is missing
		if ( ! preg_match( '/tribe_event_end_date/', $clauses['join'] ) ) {
			$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS tribe_event_end_date ON {$wpdb->posts}.ID = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '_EventEndDate' ";
		}

		$append_orderby = false;
		$original_orderby = null;

		if ( ! empty( $clauses['orderby'] ) ) {
			$original_orderby = $clauses['orderby'];

			// if the ONLY orderby clause is the post date, then let's move that toss move that to the
			// end of the orderby. This will forever make post_date play second fiddle to the event start/end dates
			// and that's ok
			$append_orderby = preg_match( '/^[a-zA-Z0-9\-_]+\.post_date (DESC|ASC)$/i', $original_orderby );
		}

		$start_orderby = "tribe_event_start_date.meta_value {$sort_direction}";
		$end_orderby = "tribe_event_end_date.meta_value {$sort_direction}";

		$date_orderby = "{$start_orderby}, {$end_orderby}";

		if ( ! empty( $wp_query->query['orderby'] ) && 'end-date' == $wp_query->query['orderby'] ) {
			$date_orderby = "{$end_orderby}, {$start_orderby}";
		}

		// Add the date orderby rules *before* any pre-existing orderby rules (to stop them being "trumped")
		if ( empty( $original_orderby ) ) {
			$revised_orderby = $date_orderby;
		} elseif ( $append_orderby ) {
			$revised_orderby = "$date_orderby, $original_orderby";
		} else {
			$revised_orderby = "$original_orderby, $date_orderby";
		}

		$clauses['orderby'] = $revised_orderby;

		return $clauses;
	}

	/**
	 * Defines custom logic for sorting events table by category or tags
	 *
	 * @since  TBD
	 *
	 * @param   Array       $clauses    SQL clauses for fetching posts
	 * @param   WP_Query    $wp_query   A paginated query for items
	 *
	 * @return  Array                   Modified SQL clauses
	 */
	public function sort_by_tax( Array $clauses, WP_Query $wp_query ) {
		if ( ! isset( $wp_query->query['orderby'] ) ) {
			return $clauses;
		}

		switch ( $wp_query->query['orderby'] ) {
			case 'events-cats':
				$taxonomy = Tribe__Events__Main::TAXONOMY;
			break;

			case 'tags':
				$taxonomy = 'post_tag';
			break;

			default:
				return $clauses;
			break;
		}

		global $wpdb;

		// collect the terms in the desired taxonomy for the given post into a single string
		$smashed_terms_sql = "
			SELECT
				GROUP_CONCAT( $wpdb->terms.name ORDER BY name ASC ) smashed_terms
			FROM
				$wpdb->term_relationships
				LEFT JOIN $wpdb->term_taxonomy ON (
					$wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
					AND taxonomy = '%s'
				)
				LEFT JOIN $wpdb->terms ON (
					$wpdb->term_taxonomy.term_id = $wpdb->terms.term_id
				)
			WHERE $wpdb->term_relationships.object_id = $wpdb->posts.ID
		";

		$smashed_terms_sql = $wpdb->prepare( $smashed_terms_sql, $taxonomy );

		$clauses['fields'] .= ",( {$smashed_terms_sql} ) as smashed_terms ";
		$clauses['orderby'] = 'smashed_terms ' . self::get_sort_direction( $wp_query );
		return $clauses;
	}

	/**
	 * limit filter for admin queries
	 *
	 * @since  TBD
	 *
	 * @param  string    $limits  MySQl LIMIT clause
	 * @param  WP_Query  $query
	 *
	 * @return string modified limits clause
	 */
	public function events_search_limits( $limits, $query ) {
		if ( ! $query->is_main_query() || $query->get( 'post_type' ) != Tribe__Events__Main::POSTTYPE ) {
			return $limits;
		}

		global $current_screen;
		$paged = (int) $query->get( 'paged' );

		if ( empty( $paged ) ) {
			$paged = 1;
		}

		if ( is_admin() ) {
			$option   = str_replace( '-', '_', "{$current_screen->id}_per_page" );
			$per_page = get_user_option( $option );
			$per_page = ( $per_page ) ? (int) $per_page : 20; // 20 is default in backend
		} else {
			$per_page = (int) tribe_get_option( 'postsPerPage', 10 );
		}

		$page_start = ( $paged - 1 ) * $per_page;
		$limits     = 'LIMIT ' . $page_start . ', ' . $per_page;

		return $limits;
	}

	/**
	 * Add the proper column headers.
	 *
	 * @since  TBD
	 *
	 * @param  array $columns The columns.
	 *
	 * @return array The modified column headers.
	 */
	public function column_headers( $columns ) {
		$events_label_singular = tribe_get_event_label_singular();

		foreach ( (array) $columns as $key => $value ) {
			$mycolumns[ $key ] = $value;
			if ( $key == 'author' ) {
				$mycolumns['events-cats'] = sprintf( esc_html__( '%s Categories', 'the-events-calendar' ), $events_label_singular );
			}
		}
		$columns = $mycolumns;

		unset( $columns['date'] );
		$columns['start-date'] = esc_html__( 'Start Date', 'the-events-calendar' );
		$columns['end-date']   = esc_html__( 'End Date', 'the-events-calendar' );

		return $columns;
	}

	/**
	 * This will only be fired if Advanced Post Manger is active
	 * Helps ensure dates show correctly if only one or the other of
	 * start & end date columns is showing
	 *
	 * @since  TBD
	 *
	 * @param  array  $columns  The columns array.
	 *
	 * @return void
	 */
	public function column_headers_check( $columns ) {
		$active                 = array_keys( $columns );
		self::$end_col_active   = in_array( 'end-date', $active );
		self::$start_col_active = in_array( 'start-date', $active );
		// What if, oddly, end_col is placed first when both are active?
		if ( self::$end_col_active && self::$start_col_active ) {
			self::$start_col_first = ( array_search( 'start-date', $active ) < array_search( 'end-date', $active ) );
		}
	}

	/**
	 * Allows events to be sorted by start date/end date/category/tags
	 *
	 * @since  TBD
	 *
	 * @param  array  $columns  The columns array.
	 *
	 * @return array  The modified columns array.
	 */
	public function register_sortable_columns( $columns ) {
		foreach ( array( 'events-cats', 'tags', 'start-date', 'end-date' ) as $sortable ) {
			$columns[ $sortable ] = $sortable;
		}

		return $columns;
	}

	/**
	 * Our own catch all method for Columns that we are filtering
	 *
	 * @since  TBD
	 *
	 * @param  string $column   Which column we are dealing with
	 * @param  int    $post_id  Post we are printing the column for
	 *
	 * @return string
	 */
	public function display_columns( $column, $post_id ) {
		$methods = array(
			'events-cats' => 'column-categories',
			'start-date'  => 'column-start-date',
			'end-date'    => 'column-end-date',
		);

		if ( ! isset( $methods[ $column ] ) ) {
			return false;
		}

		$event = get_post( $post_id );
		$arguments = array(
			'event' => $event,
		);

		return $this->template( $methods[ $column ], $arguments );
	}

	/**
	 * Gets the URL for the Series Admin List
	 *
	 * @since  TBD
	 *
	 * @param  array $args Which param arguments will be added
	 *
	 * @return string
	 */
	public function get_url( $args = array() ) {
		$defaults = array();
		$args = wp_parse_args( $args, $defaults );

		// Always have the correct CPT
		$args['post_type'] = Tribe__Events__Main::POSTTYPE;

		// Which URL
		$url = admin_url( 'edit.php' );

		return add_query_arg( $args, $url );
	}

	/**
	 * Adds and updates the filters for the Events Listing
	 *
	 * @since  TBD
	 *
	 * @param  array  $views  List of Existing views
	 *
	 * @return array
	 */
	public function filter_views( $views ) {
		global $post_type, $post_type_object, $locked_post_status, $avail_post_stati;

		$total_posts = tribe_post_count( Tribe__Events__Main::POSTTYPE );
		$mine_posts  = tribe_post_count( Tribe__Events__Main::POSTTYPE, array(), get_current_user_id() );

		$total_count = array_sum( (array) $total_posts );
		$mine_count  = array_sum( (array) $mine_posts );

		foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
			$total_count -= $total_posts->$state;
			$mine_count -= $mine_posts->$state;
		}

		$views_parts = array();

		if ( 0 !== $total_count ) {
			$views_parts['all'] = array(
				'status' => 'all',
				'href'   => $this->get_url(),
				'class'  => array(),
				'text'   => sprintf( esc_html_x( 'All %s', '%s Event count in admin list', 'the-events-calendar' ), "<span class='count'>({$total_count})</span>" ),
			);
		} else {
			// If the total Count is 0 we reset the views
			$views = array();
		}

		if ( 0 !== $mine_count ) {
			$views_parts['mine'] = array(
				'status' => 'mine',
				'href'   => $this->get_url( array( 'post_status' => 'mine' ) ),
				'class'  => array(),
				'text'   => sprintf( esc_html_x( 'Mine %s', '%s Event count in admin list', 'the-events-calendar' ), "<span class='count'>({$mine_count})</span>" ),
			);
		}

		foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
			$name = $status->name;

			if ( ! in_array( $name, $avail_post_stati ) ) {
				continue;
			}

			if ( empty( $total_posts->$name ) ) {
				continue;
			}

			$count = $total_posts->$name;

			$views_parts[ $name ] = array(
				'status' => $name,
				'href'   => $this->get_url( array( 'post_status' => $name ) ),
				'class'  => array(),
				'text'   => sprintf( translate_nooped_plural( $status->label_count, $count ), number_format_i18n( $count ) )
			);
		}

		// Setup the current view
		if ( $name = tribe_get_request_var( 'post_status', 'all' ) ) {
			$views_parts[ $name ]['class'][] = 'current';
		}

		// Fetch the actual views from a template file
		foreach ( $views_parts as $key => $view ) {
			$views[ $key ] = $this->template( 'view-link', $view, false );
		}

		return $views;
	}

	/**
	 * Adds the LEFT JOIN for the eventStart field on tribe_post_count
	 *
	 * @param  array  $query  MySQL query params for the Count
	 *
	 * @return array
	 */
	public function filter_add_join_event_start_meta( $query ) {
		global $wpdb;
		$query['join']= "LEFT JOIN {$wpdb->postmeta} as eventStart ON( {$wpdb->posts}.ID = eventStart.post_id AND eventStart.meta_key = '_EventStartDate')";
		return $query;
	}
}
