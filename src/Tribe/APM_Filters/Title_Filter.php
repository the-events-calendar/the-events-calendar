<?php


	class Tribe__Events__Pro__APM_Filters__Title_Filter {

		protected $key    = 'ecp_title';
		protected $type   = 'title';
		protected $is_key = 'is_ecp_title';

		private $query_search_options = array();

		public function __construct() {
			$this->query_search_options = array(
				'like' => __( 'Search', 'tribe-events-calendar-pro' ),
				'is' => __( 'Is', 'tribe-events-calendar-pro' ),
				'not' => __( 'Is Not', 'tribe-events-calendar-pro' ),
				'gt' => '>',
				'lt' => '<',
				'gte' => '>=',
				'lte' => '<=',
			);
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
			$compare = $ecp_apm->filters->map_query_option( $this->active['is'] );
			$value = $this->active['value'];
			if ( 'LIKE' === $compare ) {
				$value = "%$value%";
			}
			$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_title {$compare} %s ", $value );

			return $where;
		}

		public function maybe_set_active( $return, $key, $filter ) {
			if ( isset( $_POST[ $this->key ] ) && ! empty( $_POST[ $this->key ] ) && isset( $_POST[ $this->is_key ] ) && ! empty( $_POST[ $this->is_key ] ) ) {
				return array( 'value' => $_POST[ $this->key ], 'is' => $_POST[ $this->is_key ] );
			}

			return $return;
		}

		public function form_row( $return, $key, $value, $filter ) {
			// in case we have a blank row
			$value = (array) $value;
			$value = array_merge( array( 'is' => '', 'value' => '' ), $value );
			$return = tribe_select_field( $this->is_key, $this->query_search_options, $value['is'] );
			$return .= sprintf( '<input name="%s" value="%s" type="text" />', $this->key, esc_attr( $value['value'] ) );

			return $return;
		}

		public function log( $data = array() ) {
			error_log( print_r( $data, 1 ) );
		}
	}

