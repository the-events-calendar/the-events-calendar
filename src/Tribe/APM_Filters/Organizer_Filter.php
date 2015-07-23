<?php


	class Tribe__Events__Pro__APM_Filters__Organizer_Filter {

		protected $key = 'ecp_organizer_filter_key';
		protected $type = 'ecp_organizer_filter';
		protected $meta = '_EventOrganizerID';

		public function __construct() {
			$type = $this->type;

			add_filter( 'tribe_custom_column' . $type, array( $this, 'column_value' ), 10, 3 );
			add_filter( 'tribe_custom_row' . $type, array( $this, 'form_row' ), 10, 4 );

			add_filter( 'posts_join', array( $this, 'join_organizer' ), 10, 2 );
			add_filter( 'posts_where', array( $this, 'where_organizer' ) );

			add_filter( 'tribe_maybe_active' . $type, array( $this, 'maybe_set_active' ), 10, 3 );
		}


		public function maybe_set_active( $return, $key, $filter ) {
			if ( isset( $_POST[ $this->key ] ) && ! empty( $_POST[ $this->key ] ) ) {
				return $_POST[ $this->key ];
			}

			return $return;
		}


		public function join_organizer( $join, $wp_query ) {

			if ( empty( $_POST[ $this->key ] ) ) {
				return $join;
			}

			global $wpdb;
			$join .= " LEFT JOIN {$wpdb->postmeta} AS organizer_meta ON({$wpdb->posts}.ID = organizer_meta.post_id AND organizer_meta.meta_key='{$this->meta}') ";

			return $join;
		}

		public function where_organizer( $where ) {
			if ( empty( $_POST[ $this->key ] ) ) {
				return $where;
			}

			$organizers = array_filter( array_map( 'absint', (array) $_POST[ $this->key ] ) );

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
				return $organizer->post_title;
			} else {
				return $value;
			}
		}

		public function log( $data = array() ) {
			error_log( print_r( $data, 1 ) );
		}
	}
