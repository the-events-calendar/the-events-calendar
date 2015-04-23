<?php


	class Tribe__Events__Pro__APM_Filters__Venue_Filter {

		protected $key  = 'ecp_venue_filter_key';
		protected $type = 'ecp_venue_filter';
		protected $meta = '_EventVenueID';

		public function __construct() {
			$type = $this->type;

			add_filter( 'tribe_custom_column' . $type, array( $this, 'column_value' ), 10, 3 );
			add_filter( 'tribe_custom_row' . $type, array( $this, 'form_row' ), 10, 4 );
			add_filter( 'tribe_maybe_active' . $type, array( $this, 'maybe_set_active' ), 10, 3 );

			add_filter( 'posts_join', array( $this, 'join_venue' ), 10, 2 );
			add_filter( 'posts_where', array( $this, 'where_venue' ) );

		}

		public function maybe_set_active( $return, $key, $filter ) {
			if ( isset( $_POST[ $this->key ] ) && ! empty( $_POST[ $this->key ] ) ) {
				return $_POST[ $this->key ];
			}

			return $return;
		}

		public function join_venue( $join, $wp_query ) {
			if ( empty( $_POST[ $this->key ] ) ) {
				return $join;
			}


			global $wpdb;
			$join .= " INNER JOIN {$wpdb->postmeta} AS venue_meta ON({$wpdb->posts}.ID = venue_meta.post_id AND venue_meta.meta_key='{$this->meta}') ";

			return $join;
		}

		public function where_venue( $where ) {
			if ( empty( $_POST[ $this->key ] ) ) {
				return $where;
			}

			global $wpdb;

			$venues = (array) $_POST[ $this->key ];

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
				return $venue->post_title;
			} else {
				return '';
			}
		}

		public function log( $data = array() ) {
			error_log( print_r( $data, 1 ) );
		}
	}

