<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/**
 * Class Tribe__Events__Tickets__Orders_Table
 *
 * See documentation for WP_List_Table
 */
class Tribe__Events__Tickets__Orders_Table extends WP_List_Table {
	public $event_id;
	public $total_purchased = 0;
	public $overall_total = 0;
	public $valid_order_items = array();
	public static $pass_fees_to_user = true;
	public static $fee_percent = 0;
	public static $fee_flat = 0;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$args = array(
			'singular' => 'order',
			'plural' => 'orders',
			'ajax' => true,
		);

		parent::__construct( $args );
	}//end __construct

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
	}//end search_box

	/**
	 * Display the pagination.
	 * We are not paginating the order list, so it returns empty.
	 *
	 * @access protected
	 */
	public function pagination( $which ) {
		return '';
	}//end pagination

	/**
	 * Checks the current user's permissions
	 *
	 * @access public
	 */
	public function ajax_user_can() {
		$post_type = get_post_type_object( $this->screen->post_type );

		return ! empty( $post_type->cap->edit_posts ) && current_user_can( $post_type->cap->edit_posts );
	}//end ajax_user_can

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'order'      => __( 'Order', 'the-events-calendar' ),
			'purchased'  => __( 'Purchased', 'the-events-calendar' ),
			'ship_to'    => __( 'Ship to', 'the-events-calendar' ),
			'date'       => __( 'Date', 'the-events-calendar' ),
		);

		if ( self::event_fees( $this->event_id ) ) {
			$columns['subtotal'] = __( 'Subtotal', 'the-events-calendar' );
			$columns['site_fee'] = __( 'Site Fee', 'the-events-calendar' );
		}

		$columns['total'] = __( 'Total', 'the-events-calendar' );

		return $columns;
	}//end get_columns

	/**
	 * Handler for the columns that don't have a specific column_{name} handler function.
	 *
	 * @param $item
	 * @param $column
	 *
	 * @return string
	 */
	public function column_default( $item, $column ) {
		$value = empty( $item->$column ) ? '' : $item->$column;

		return apply_filters( 'tribe_events_tickets_orders_table_column', $value, $item, $column );
	}//end column_default

	/**
	 * Handler for the date column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_date( $item ) {
		return Tribe__Events__Date_Utils::reformat( $item['completed_at'], Tribe__Events__Date_Utils::DATEONLYFORMAT );
	}//end column_date

	/**
	 * Handler for the ship to column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_ship_to( $item ) {
		$shipping = $item['shipping_address'];

		if (
			empty( $shipping['address_1'] )
			|| empty( $shipping['city'] )
			|| empty( $shipping['state'] )
			|| empty( $shipping['postcode'] )
			|| empty( $shipping['country'] )
		) {
			return '';
		}

		$address = trim( "{$shipping['first_name']} {$shipping['last_name']}" );

		if ( ! empty( $shipping['company'] ) ) {
			if ( $address ) {
				$address .= '<br>';
			}

			$address .= $shipping['company'];
		}

		$address .= "<br>{$shipping['address_1']}<br>";

		if ( ! empty( $shipping['address_2'] ) ) {
			$address .= "{$shipping['address_2']}<br>";
		}

		$address .= "{$shipping['city']}, {$shipping['state']} {$shipping['postcode']}";

		return $address;
	}//end column_ship_to

	/**
	 * Handler for the purchased column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_purchased( $item ) {

		$tickets = array();
		$num_items = 0;

		foreach ( $item['line_items'] as $line_item ) {
			$ticket_id = $line_item['product_id'];

			if ( ! isset( $this->valid_order_items[ $item['id'] ][ $ticket_id ] ) ) {
				continue;
			}

			$num_items += $line_item['quantity'];

			if ( empty( $tickets[ $line_item['name'] ] ) ) {
				$tickets[ $line_item['name'] ] = 0;
			}

			$tickets[ $line_item['name'] ] += $line_item['quantity'];
		}

		$this->total_purchased = $num_items;

		ksort( $tickets );

		$output = '';

		foreach ( $tickets as $name => $quantity ) {

			$output .= "<div class='tribe-line-item'>{$quantity} - {$name}</div>";
		}

		return $output;
	}//end column_purchased

	/**
	 * Handler for the order column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_order( $item ) {
		$icon    = '';
		$warning = false;

		$order_number = $item['order_number'];
		$customer = $item['customer'];
		$customer_email = empty( $customer['email'] ) ? '' : $customer['email'];
		$customer_name = '';

		if ( empty( $customer['first_name'] ) && empty( $customer['last_name'] ) ) {
			$customer_name = "{$item['billing_address']['first_name']} {$item['billing_address']['last_name']}";
		} else {
			$customer_name = empty( $customer['first_name'] ) ? '' : $customer['first_name'];
			$customer_name .= empty( $customer['last_name'] ) ? '' : ' ' . $customer['last_name'];
		}

		$customer_name = trim( $customer_name );

		$order_url = add_query_arg(
			array(
				'post' => $order_number,
				'action' => 'edit',
			),
			admin_url( 'post.php' )
		);

		$order_number_link = '<a href="' . esc_url( $order_url ) . '">#' . absint( $order_number ) . '</a>';

		$output = sprintf(
			esc_html__(
				'%1$s by %2$s',
				'the-events-calendar'
			),
			$order_number_link,
			esc_html( $customer_name )
		);

		if ( $customer_email ) {
			$output .= sprintf(
				'<br><a href="mailto:%1$s">%2$s</a>',
				esc_attr( $customer_email ),
				esc_html( $customer_email )
			);
		}

		if ( 'completed' !== $item['status'] ) {
			$output .= '<div class="order-status order-status-' . esc_attr( $item['status'] ) . '">' . esc_html( ucwords( $item['status'] ) ) . '</div>';
		}

		return $output;
	}//end column_order

	/**
	 * Handler for the subtotal column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_subtotal( $item ) {
		$total = 0;

		$price_format = get_woocommerce_price_format();

		foreach ( $this->valid_order_items[ $item['id'] ] as $line_item ) {
			$total += $line_item['subtotal'];
		}

		if ( ! self::$pass_fees_to_user ) {
			$total -= self::calc_site_fee( $total );
		}

		return sprintf( $price_format, get_woocommerce_currency_symbol(), number_format( $total, 2 ) );
	}//end column_subtotal

	/**
	 * Handler for the total column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_total( $item ) {
		$total = 0;

		$price_format = get_woocommerce_price_format();

		foreach ( $this->valid_order_items[ $item['id'] ] as $line_item ) {
			$total += $line_item['subtotal'];
		}

		if ( self::$pass_fees_to_user ) {
			$total += $this->calc_site_fee( $total );
		}

		return sprintf( $price_format, get_woocommerce_currency_symbol(), number_format( $total, 2 ) );
	}//end column_total

	/**
	 * Handler for the site fees column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_site_fee( $item ) {
		$total = 0;

		$price_format = get_woocommerce_price_format();

		foreach ( $this->valid_order_items[ $item['id'] ] as $line_item ) {
			$total += $line_item['subtotal'];
		}

		return sprintf( $price_format, get_woocommerce_currency_symbol(), number_format( $this->calc_site_fee( $total ), 2 ) );
	}//end column_site_fee

	/**
	 * Generates content for a single row of the table
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? ' alternate ' : '' );

		echo '<tr class="' . sanitize_html_class( $row_class ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}//end single_row

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 *
	 * Used for the Print, Email and Export buttons, and for the jQuery based search.
	 *
	 */
	public function extra_tablenav( $which ) {
	}//end extra_tablenav

	public static function get_orders( $event_id ) {
		if ( ! $event_id ) {
			return array();
		}

		WC()->api->includes();
		WC()->api->register_resources( new WC_API_Server( '/' ) );

		$main = Tribe__Events__Tickets__Woo__Main::get_instance();

		$tickets = $main->get_tickets( $event_id );

		$args = array(
			'post_type' => 'tribe_wooticket',
			'post_status' => array(
				'wc-pending',
				'wc-processing',
				'wc-on-hold',
				'wc-completed',
				'publish',
			),
			'meta_query' => array(
				array(
					'key' => '_tribe_wooticket_event',
					'value' => $event_id,
				),
			),
		);

		$orders = array();
		$query = new WP_Query( $args );
		foreach ( $query->posts as &$item ) {
			$order_id = get_post_meta( $item->ID, '_tribe_wooticket_order', true );

			if ( isset( $orders[ $order_id ] ) ) {
				continue;
			}

			$order = WC()->api->WC_API_Orders->get_order( $order_id );
			$orders[ $order_id ] = $order['order'];
		}

		return $orders;
	}

	public static function get_valid_order_items_for_event( $event_id, $items ) {
		$valid_order_items = array();

		$event_id = absint( $event_id );

		foreach ( $items as $order ) {
			if ( ! isset( $valid_order_items[ $order['id'] ] ) ) {
				$valid_order_items[ $order['id'] ] = array();
			}

			foreach ( $order['line_items'] as $line_item ) {
				$ticket_id = $line_item['product_id'];
				$ticket_event_id = absint( get_post_meta( $ticket_id, '_tribe_wooticket_for_event', true ) );

				// if the ticket isn't for the currently viewed event, skip it
				if ( $ticket_event_id !== $event_id ) {
					continue;
				}

				$valid_order_items[ $order['id'] ][ $ticket_id ] = $line_item;
			}
		}

		return $valid_order_items;
	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {
		$this->event_id = isset( $_GET['event_id'] ) ? $_GET['event_id'] : 0;

		$this->items = self::get_orders( $this->event_id );
		$total_items = count( $this->items );
		$per_page    = $total_items;

		$this->valid_order_items = self::get_valid_order_items_for_event( $this->event_id, $this->items );

		$this->set_pagination_args(
			 array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => 1,
			 )
		);
	}//end prepare_items

	/**
	 * Return sales (sans fees) for the given event
	 *
	 * @param int $event_id Event post ID
	 *
	 * @return float
	 */
	public static function event_sales( $event_id ) {
		$orders = self::get_orders( $event_id );
		$valid_order_items = self::get_valid_order_items_for_event( $event_id, $orders );

		$total = 0;

		foreach ( $valid_order_items as $order ) {
			$order_total = 0;

			foreach ( $order as $line_item ) {
				$order_total += $line_item['subtotal'];
			}

			if ( ! self::$pass_fees_to_user ) {
				$order_total -= self::calc_site_fee( $order_total, self::$pass_fees_to_user );
			}

			$total += $order_total;
		}

		return $total;
	}

	/**
	 * Return fees for the given event
	 *
	 * @param int $event_id Event post ID
	 *
	 * @return float
	 */
	public static function event_fees( $event_id ) {
		$orders = self::get_orders( $event_id );
		$valid_order_items = self::get_valid_order_items_for_event( $event_id, $orders );

		$fees = 0;

		foreach ( $valid_order_items as $order ) {
			$order_total = 0;

			foreach ( $order as $line_item ) {
				$order_total += $line_item['subtotal'];
			}

			$fees += self::calc_site_fee( $order_total, self::$pass_fees_to_user );
		}

		return $fees;
	}

	/**
	 * Return total revenue for the given event
	 *
	 * @param int $event_id Event post ID
	 *
	 * @return float
	 */
	public static function event_revenue( $event_id ) {
		return self::event_sales( $event_id, self::$pass_fees_to_user ) + self::event_fees( $event_id, self::$pass_fees_to_user );
	}

	/**
	 * Calculate site fees
	 *
	 * @param int $amount Total to calculate site fees on
	 *
	 * @return float
	 */
	public static function calc_site_fee( $amount ) {
		return round( $amount * ( self::$fee_percent / 100 ), 2 ) + self::$fee_flat;
	}
}//end class
