<?php


	class Tribe__Events__Pro__APM_Filters__Content_Filter {

		protected $key    = 'ecp_content';
		protected $type   = 'content';
		protected $is_key = 'is_ecp_content';

		public function __construct() {
			$type = $this->type;
			add_filter( 'tribe_custom_row' . $type, array( $this, 'form_row' ), 10, 4 );
			add_filter( 'tribe_maybe_active' . $type, array( $this, 'maybe_set_active' ), 10, 3 );
			add_action( 'tribe_after_parse_query', array( $this, 'parse_query' ), 10, 2 );
		}

		public function parse_query( $wp_query, $active ) {
			if ( ! isset( $active[ $this->key ] ) ) {
				return;
			}
			$this->active = $active[ $this->key ];
			add_filter( 'posts_where', array( $this, 'where' ), 10, 2 );
		}

		public function where( $where, $wp_query ) {
			global $ecp_apm, $wpdb;
			// run once
			remove_filter( 'posts_where', array( $this, 'where' ), 10, 2 );
			$value = "%{$this->active}%";
			$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_content LIKE %s ", $value );

			return $where;
		}

		public function maybe_set_active( $return, $key, $filter ) {
			if ( isset( $_POST[ $this->key ] ) && ! empty( $_POST[ $this->key ] ) ) {
				return $_POST[ $this->key ];
			}

			return $return;
		}

		public function form_row( $return, $key, $value, $filter ) {
			// in case we have a blank row
			$value = (string) $value;

			return sprintf( '<input name="%s" value="%s" type="text" />', $this->key, esc_attr( $value ) );
		}
	}


