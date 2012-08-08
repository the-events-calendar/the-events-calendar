<?php


	if ( !class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}


	class TribeEventsTicketsAttendeesTable extends WP_List_Table {

		function __construct() {
			global $status, $page;

			parent::__construct( array( 'singular'  => 'attendee',
			                            'plural'    => 'attendees',
			                            'ajax'      => true ) );
		}

		function search_box(){
			return '';
		}

		function get_columns() {
			$columns = array( 'cb'              => '<input type="checkbox" />',
			                  'order_id'        => __( 'Order #', "tribe-events-calendar" ),
			                  'attendee'        => __( 'Attendee', "tribe-events-calendar" ),
			                  'ticket'          => __( 'Kind of Ticket', "tribe-events-calendar" ),
			                  'qty'             => __( 'Quantity', "tribe-events-calendar" ),
			                  'check_in'        => __( 'Check in', "tribe-events-calendar" ) );
			return $columns;
		}


		function column_default() {
			return '';
		}

		function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item["order_id"] . '_' . $item["product_id"] );
		}

		function column_order_id( $item ) {
			return "<a class='row-title' href='" . get_edit_post_link( $item["order_id"], true ) . "'>" . $item["order_id"] . "</a>";
		}

		function column_attendee( $item ) {
			return $item["attendee"];
		}

		function column_ticket( $item ) {
			return $item["ticket"];
		}

		function column_qty( $item ) {
			return $item["qty"];
		}

		function column_check_in( $item ){
			return sprintf("<a href='' class='button-secondary'>Check in</a>");
		}


		function extra_tablenav( $which ) {

			if ( $which == "top" ) {
				echo sprintf( "%s: <input type='text' name='filter_attendee' id='filter_attendee' value=''>", __( "Filter by attendee name or order #", "tribe-events-calendar" ) );
			}
		}

		function get_bulk_actions() {

			$actions = array( 'check_in'    => 'Check in' );
			return $actions;

		}


		function process_bulk_action() {
			if ( 'check_in' === $this->current_action() ) {
				echo "Check in!";
			}

		}

		function prepare_items() {

			global $wpdb;

			$this->process_bulk_action();

			$per_page = 100000;

			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = array();

			$this->_column_headers = array( $columns,
			                                $hidden,
			                                $sortable );

			$current_page = $this->get_pagenum();

			if ( isset( $_GET['s'] ) && $_GET['s'] !== "" ) {
			}

			if ( isset( $_GET['post_status'] ) ) {
			}

			$event_id = isset( $_GET['event_id'] ) ? $_GET['event_id'] : 0;

			$items = TribeEventsTickets::get_event_attendees( $event_id );

			$this->items = $items;

			$total_items = count( $this->items );

			$this->set_pagination_args( array( 'total_items' => $total_items,
			                                   'per_page'    => $per_page,
			                                   'total_pages' => ceil( $total_items / $per_page ) ) );
		}


	}
