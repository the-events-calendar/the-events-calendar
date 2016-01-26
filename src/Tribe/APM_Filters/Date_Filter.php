<?php


	class Tribe__Events__Pro__APM_Filters__Date_Filter {

		protected $active = array();
		protected $type   = 'custom_date';

		private $query_search_options = array();

		public function __construct() {
			$this->query_search_options = array(
				'is'  => esc_html__( 'Is', 'tribe-events-calendar-pro' ),
				'not' => esc_html__( 'Is Not', 'tribe-events-calendar-pro' ),
				'gte' => esc_html__( 'On and After', 'tribe-events-calendar-pro' ),
				'lte' => esc_html__( 'On and Before', 'tribe-events-calendar-pro' ),
			);
			$type = $this->type;
			add_filter( 'tribe_custom_row' . $type, array( $this, 'form_row' ), 10, 4 );
			add_filter( 'tribe_maybe_active' . $type, array( $this, 'maybe_set_active' ), 10, 3 );
			add_action( 'tribe_after_parse_query', array( $this, 'parse_query' ), 10, 2 );

		}

		public function form_row( $return, $key, $value, $unused_filter ) {
			$value = (array) $value;
			$value = wp_parse_args( $value, array( 'is' => '', 'value' => '', 'is_date_field' => true ) );
			$return = tribe_select_field( 'is_' . $key, $this->query_search_options, $value['is'] );
			$return .= sprintf( '<input name="%s" value="%s" type="text" class="date tribe-datepicker" />', $key, esc_attr( $value['value'] ) );

			return $return;
		}

		public function maybe_set_active( $return, $key, $filter ) {
			global $ecp_apm;

			if ( ! empty( $_POST[ $key ] ) && ! empty( $_POST[ 'is_' . $key ] ) ) {
				return array( 'value' => $_POST[ $key ], 'is' => $_POST[ 'is_' . $key ], 'is_date_field' => true );
			}

			$active_filters = $ecp_apm->filters->get_active();

			if ( ! empty( $active_filters[ $key ] ) && ! empty( $active_filters[ 'is_' . $key ] ) ) {
				return array( 'value' => $active_filters[ $key ], 'is' => $active_filters[ 'is_' . $key ], 'is_date_field' => true );
			}

			return $return;
		}

		public function parse_query( $wp_query_current, $active ) {
			if ( empty( $active ) ) {
				return;
			}

			global $wp_query;

			foreach ( $active as $key => $field ) {
				if ( isset( $field['is_date_field'] ) ) {
					$this->active[ $key ] = $field;
				}
			}

			add_filter( 'posts_where', array( $this, 'where' ), 10, 2 );

		}

		public function where( $where, $wp_query ) {
			global $ecp_apm, $wpdb;
			// run once
			remove_filter( 'posts_where', array( $this, 'where' ), 10, 2 );

			foreach ( $this->active as $key => $active ) {

				$field = '';

				if ( $key === 'ecp_start_date' ) {
					$field = 'tribe_event_start_date.meta_value';
				}
				if ( $key === 'ecp_end_date' ) {
					$field = 'tribe_event_end_date.meta_value';
				}

				if ( empty( $field ) ) {
					continue;
				}

				$value = $active['value'];

				switch ( $active['is'] ) {
					case 'is':
						$where .= $wpdb->prepare( " AND $field BETWEEN %s AND %s ", tribe_beginning_of_day( $value ), tribe_end_of_day( $value ) );
						break;
					case 'not':
						$where .= $wpdb->prepare( " AND $field NOT BETWEEN %s AND %s ", tribe_beginning_of_day( $value ), tribe_end_of_day( $value ) );
						break;
					case 'gte':
						$where .= $wpdb->prepare( " AND $field >= %s ", tribe_beginning_of_day( $value ) );
						break;
					case 'lte':
						$where .= $wpdb->prepare( " AND $field <= %s ", tribe_end_of_day( $value ) );
						break;

				}
			}


			return $where;

		}

	}
