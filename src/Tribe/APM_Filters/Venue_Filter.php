<?php
class Tribe__Events__Pro__APM_Filters__Venue_Filter {

	protected $key  = 'ecp_venue_filter_key';
	protected $type = 'ecp_venue_filter';
	protected $meta = '_EventVenueID';
	protected $active = array();

	public function __construct() {
		$type = $this->type;

		add_filter( 'tribe_custom_column' . $type, array( $this, 'column_value' ), 10, 3 );
		add_filter( 'tribe_custom_row' . $type, array( $this, 'form_row' ), 10, 4 );
		add_filter( 'tribe_maybe_active' . $type, array( $this, 'maybe_set_active' ), 10, 3 );

		add_action( 'tribe_after_parse_query', array( $this, 'parse_query' ), 10, 2 );
	}

	public function maybe_set_active( $return, $key, $filter ) {
		global $ecp_apm;

		if ( ! empty( $_POST[ $this->key ] ) ) {
			return $_POST[ $this->key ];
		}

		$active_filters = $ecp_apm->filters->get_active();

		if ( ! empty( $active_filters[ $this->key ] ) ) {
			return $active_filters[ $this->key ];
		}

		return $return;
	}

	public function parse_query( $wp_query_current, $active ) {
		if ( empty( $active[ $this->key ] ) ) {
			return;
		}

		$this->active = $active;
		add_filter( 'posts_join', array( $this, 'join_venue' ), 10, 2 );
		add_filter( 'posts_where', array( $this, 'where_venue' ), 10, 2 );
	}

	public function join_venue( $join, $wp_query ) {
		// bail if this is not a query for the event post type
		if ( $wp_query->get( 'post_type' ) !== Tribe__Events__Main::POSTTYPE ) {
			return $join;
		}

		global $ecp_apm;

		$active_filters = array();

		if ( isset( $ecp_apm ) && isset( $ecp_apm->filters ) ) {
			$active_filters = $ecp_apm->filters->get_active();
		}

		if ( empty( $_POST[ $this->key ] ) && empty( $active_filters[ $this->key ] ) ) {
			return $join;
		}

		global $wpdb;
		$join .= " INNER JOIN {$wpdb->postmeta} AS venue_meta ON({$wpdb->posts}.ID = venue_meta.post_id AND venue_meta.meta_key='{$this->meta}') ";

		return $join;
	}

	public function where_venue( $where, WP_Query $query ) {
		global $wpdb;

		// bail if this is not a query for the event post type
		if ( $query->get( 'post_type' ) !== Tribe__Events__Main::POSTTYPE ) {
			return $where;
		}

		$venues = $this->active[ $this->key ];

		$ids_format_string = rtrim( str_repeat( '%d,', count( $venues ) ), ',' );

		$where .= $wpdb->prepare( " AND venue_meta.meta_value in ($ids_format_string) ", $venues );

		return $where;
	}

	public function form_row( $return, $key, $value, $filter ) {
		$venues = get_posts( array( 'post_type' => Tribe__Events__Main::VENUE_POST_TYPE, 'nopaging' => true ) );

		$args = array();

		foreach ( $venues as $venues ) {
			$args[ $venues->ID ] = $venues->post_title;
		}

		return tribe_select_field( $key, $args, $value, true );
	}

	public function column_value( $value, $column_id, $post_id ) {
		$venue_id = get_post_meta( $post_id, '_EventVenueID', true );
		$venue = get_post( $venue_id );

		if ( $venue_id && $venue ) {
			return esc_html( $venue->post_title );
		} else {
			return '';
		}
	}

	public function log( $data = array() ) {
		error_log( print_r( $data, 1 ) );
	}
}
