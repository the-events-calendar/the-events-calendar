<?php
class Tribe__Events__Pro__APM_Filters__Organizer_Filter {

	protected $key = 'ecp_organizer_filter_key';
	protected $type = 'ecp_organizer_filter';
	protected $meta = '_EventOrganizerID';
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
		add_filter( 'posts_join', array( $this, 'join_organizer' ), 10, 2 );
		add_filter( 'posts_where', array( $this, 'where_organizer' ), 10, 2 );
	}

	public function join_organizer( $join, $wp_query ) {
		// bail if this is not a query for event post type
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
		$join .= " LEFT JOIN {$wpdb->postmeta} AS organizer_meta ON({$wpdb->posts}.ID = organizer_meta.post_id AND organizer_meta.meta_key='{$this->meta}') ";

		return $join;
	}

	public function where_organizer( $where, WP_Query $query ) {
		// bail if this is not a query for event post type
		if ( $query->get( 'post_type' ) !== Tribe__Events__Main::POSTTYPE ) {
			return $where;
		}

		$organizers = array_filter( array_map( 'absint', $this->active[ $this->key ] ) );

		if ( empty( $organizers ) ) {
			return $where;
		}

		$where .= ' AND organizer_meta.meta_value in ( ' . implode( ',', $organizers ) . ' ) ';

		return $where;
	}

	public function form_row( $return, $key, $value, $filter ) {
		$organizers = get_posts( array(
			'post_type' => Tribe__Events__Main::ORGANIZER_POST_TYPE,
			'nopaging' => true,
		) );

		$args = array();

		foreach ( $organizers as $organizers ) {
			$args[ $organizers->ID ] = $organizers->post_title;
		}

		return tribe_select_field( $key, $args, $value, true );
	}

	public function column_value( $value, $column_id, $post_id ) {
		$organizer_id = get_post_meta( $post_id, '_EventOrganizerID', true );
		$organizer = get_post( $organizer_id );

		if ( $organizer_id && $organizer ) {
			return esc_html( $organizer->post_title );
		} else {
			return $value;
		}
	}

	public function log( $data = array() ) {
		error_log( print_r( $data, 1 ) );
	}
}
