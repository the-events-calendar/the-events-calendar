<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/**
 * Class Tribe__Events__Tickets__Attendees_Table
 *
 * See documentation for WP_List_Table
 */
class Tribe__Events__Tickets__Attendees_Table extends WP_List_Table {

	/**
	 * Class constructor
	 *
	 * @param array $args  additional arguments/overrides
	 *
	 * @see WP_List_Table::__construct()
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'singular' => 'attendee',
			'plural'   => 'attendees',
			'ajax'     => true,
		) );
		parent::__construct( apply_filters( 'tribe_events_tickets_attendees_table_args', $args ) );
	}


	/**
	 * Display the search box.
	 * We don't want Core's search box, because we implemented our own jQuery based filter,
	 * so this function overrides the parent's one and returns empty.
	 *
	 * @access public
	 *
	 * @param string $text     The search button text
	 * @param string $input_id The search input id
	 */
	public function search_box( $text, $input_id ) {
		return;
	}

	/**
	 * Display the pagination.
	 * We are not paginating the attendee list, so it returns empty.
	 *
	 * @access protected
	 */
	public function pagination( $which ) {
		return '';
	}

	/**
	 * Checks the current user's permissions
	 *
	 * @access public
	 */
	public function ajax_user_can() {
		return current_user_can( get_post_type_object( $this->screen->post_type )->cap->edit_posts );
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'order_id'        => __( 'Order #', 'the-events-calendar' ),
			'order_status'    => __( 'Order Status', 'the-events-calendar' ),
			'purchaser_name'  => __( 'Purchaser name', 'the-events-calendar' ),
			'purchaser_email' => __( 'Purchaser email', 'the-events-calendar' ),
			'ticket'          => __( 'Ticket type', 'the-events-calendar' ),
			'attendee_id'     => __( 'Ticket #', 'the-events-calendar' ),
			'security'        => __( 'Security Code', 'the-events-calendar' ),
			'check_in'        => __( 'Check in', 'the-events-calendar' ),
		);

		return $columns;
	}


	/**
	 * Handler for the columns that don't have a specific column_{name} handler function.
	 *
	 * @param $item
	 * @param $column
	 *
	 * @return string
	 */
	public function column_default( $item, $column ) {
		$value = empty( $item[ $column ] ) ? '' : $item[ $column ];

		return apply_filters( 'tribe_events_tickets_attendees_table_column', $value, $item, $column );
	}

	/**
	 * Handler for the checkbox column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', esc_attr( $this->_args['singular'] ), esc_attr( $item['attendee_id'] . '|' . $item['provider'] ) );
	}

	/**
	 * Handler for the order id column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_order_id( $item ) {

		//back compat
		if ( empty( $item['order_id_link'] ) ) {
			$item['order_id_link'] = sprintf( '<a class="row-title" href="%s">%s</a>', esc_url( get_edit_post_link( $item['order_id'], true ) ), esc_html( $item['order_id'] ) );
		}

		return $item['order_id_link'];
	}

	/**
	 * Handler for the order status column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_order_status( $item ) {
		$icon    = '';
		$warning = false;

		// Check if the order_warning flag has been set (to indicate the order has been cancelled, refunded etc)
		if ( isset( $item['order_warning'] ) && $item['order_warning'] ) {
			$warning = true;
		}

		// If the warning flag is set, add the appropriate icon
		if ( $warning ) {
			$tec  = Tribe__Events__Main::instance();
			$icon = sprintf( "<span class='warning'><img src='%s'/></span> ", trailingslashit( $tec->pluginUrl ) . 'resources/images/warning.png' );
		}

		// Look for an order_status_label, fall back on the actual order_status string @todo remove fallback in 3.4.3
		$label = isset( $item['order_status_label'] ) ? $item['order_status_label'] : ucwords( $item['order_status'] );

		return $icon . $label;
	}

	/**
	 * Handler for the check in column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_check_in( $item ) {
		$checkin   = sprintf( '<a href="#" data-attendee-id="%d" data-provider="%s" class="button-secondary tickets_checkin">%s</a>', esc_attr( $item['attendee_id'] ), esc_attr( $item['provider'] ), __( 'Check in', 'the-events-calendar' ) );
		$uncheckin = sprintf( '<span class="delete"><a href="#" data-attendee-id="%d" data-provider="%s" class="tickets_uncheckin">%s</a></span>', esc_attr( $item['attendee_id'] ), esc_attr( $item['provider'] ), __( 'Undo Check in', 'the-events-calendar' ) );

		return $checkin . $uncheckin;
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? ' alternate ' : '' );

		$checked = '';
		if ( intval( $item['check_in'] ) === 1 ) {
			$checked = ' tickets_checked ';
		}

		echo '<tr class="' . sanitize_html_class( $row_class ) . esc_attr( $checked ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}


	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 *
	 * Used for the Print, Email and Export buttons, and for the jQuery based search.
	 *
	 * @param string $which (top|bottom)
	 * @see WP_List_Table::display()
	 */
	public function extra_tablenav( $which ) {

		$export_url = add_query_arg(
			array(
				'attendees_csv' => true,
				'attendees_csv_nonce' => wp_create_nonce( 'attendees_csv_nonce' ),
			)
		);

		$nav = array(
			'left' => array(
				'print' => sprintf( '<input type="button" name="print" class="print button action" value="%s">', esc_attr__( 'Print', 'the-events-calendar' ) ),
				'email' => sprintf( '<input type="button" name="email" class="email button action" value="%s">', esc_attr__( 'Email', 'the-events-calendar' ) ),
				'export' => sprintf( '<a href="%s" class="export button action">%s</a>', esc_url( $export_url ), esc_html__( 'Export', 'the-events-calendar' ) ),
			),
			'right' => array(),
		);

		if ( 'top' == $which ) {
			$nav['right']['filter_box'] = sprintf( '%s: <input type="text" name="filter_attendee" id="filter_attendee" value="">', __( 'Filter by purchaser name, ticket #, order # or security code', 'the-events-calendar' ) );
		}

		$nav = apply_filters( 'tribe_events_tickets_attendees_table_nav', $nav, $which );

		?>
		<div class="alignleft actions"><?php echo implode( $nav['left'] ); ?></div>
		<div class="alignright"><?php echo implode( $nav['right'] ) ?></div>
		<?php
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'check_in'        => esc_attr__( 'Check in', 'the-events-calendar' ),
			'uncheck_in'      => esc_attr__( 'Undo Check in', 'the-events-calendar' ),
			'delete_attendee' => esc_attr__( 'Delete', 'the-events-calendar' ),
		);

		return (array) apply_filters( 'tribe_events_tickets_attendees_table_bulk_actions', $actions );
	}


	/**
	 * Handler for the different bulk actions
	 */
	public function process_bulk_action() {
		switch ( $this->current_action() ) {
			case 'check_in':
				$this->bulk_check_in();
				break;
			case 'uncheck_in':
				$this->bulk_uncheck_in();
				break;
			case 'delete_attendee':
				$this->bulk_delete();
				break;
			default:
				do_action( 'tribe_events_tickets_attendees_table_process_bulk_action', $this->current_action() );
				break;
		}
	}

	protected function bulk_check_in() {
		if ( ! isset( $_POST['attendee'] ) ) {
			return;
		}

		foreach ( (array) $_POST['attendee'] as $attendee ) {
			list( $id, $addon ) = $this->attendee_reference( $attendee );
			if ( false === $id ) {
				continue;
			}
			$addon->checkin( $id );
		}
	}

	protected function bulk_uncheck_in() {
		if ( ! isset( $_POST['attendee'] ) ) {
			return;
		}

		foreach ( (array) $_POST['attendee'] as $attendee ) {
			list( $id, $addon ) = $this->attendee_reference( $attendee );
			if ( false === $id ) {
				continue;
			}
			$addon->uncheckin( $id );
		}
	}

	protected function bulk_delete() {
		if ( ! isset( $_POST['attendee'] ) ) {
			return;
		}

		foreach ( (array) $_POST['attendee'] as $attendee ) {
			list( $id, $addon ) = $this->attendee_reference( $attendee );
			if ( false === $id ) {
				continue;
			}
			$addon->delete_ticket( null, $id );
		}
	}

	/**
	 * Returns the attendee ID and instance of the specific ticketing solution or "addon" used
	 * to handle it.
	 *
	 * This is used in the context of bulk actions where each attendee table entry is identified
	 * by a string of the pattern {id}|{ticket_class} - where possible this method turns that into
	 * an array consisting of the attendee object ID and the relevant ticketing object.
	 *
	 * If this cannot be determined, both array elements will be set to false.
	 *
	 * @param $reference
	 *
	 * @return array
	 */
	protected function attendee_reference( $reference ) {
		$failed = array( false, false );
		if ( false === strpos( $reference, '|' ) ) {
			return $failed;
		}

		$parts = explode( '|', $reference );
		if ( count( $parts ) < 2 ) {
			return $failed;
		}

		$id = absint( $parts[0] );
		if ( $id <= 0 ) {
			return $failed;
		}

		$addon = call_user_func( array( $parts[1], 'get_instance' ) );
		if ( ! is_subclass_of( $addon, 'Tribe__Events__Tickets__Tickets' ) ) {
			return $failed;
		}

		return array( $id, $addon );
	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {

		$this->process_bulk_action();

		$event_id = isset( $_GET['event_id'] ) ? $_GET['event_id'] : 0;

		$items = Tribe__Events__Tickets__Tickets::get_event_attendees( $event_id );


		$this->items = $items;
		$total_items = count( $this->items );
		$per_page    = $total_items;

		$this->set_pagination_args(
			 array(
				 'total_items' => $total_items,
				 'per_page'    => $per_page,
				 'total_pages' => 1,
			 )
		);

	}


}
