<?php
class Tribe__Events__Tickets__Tickets_Pro {
	/**
	 * Singleton instance of this class
	 *
	 * @var Tribe__Events__Tickets__Tickets_Pro
	 * @static
	 */
	protected static $instance;

	/**
	 * Path to this plugin
	 * @var string
	 */
	protected $path;

	/**
	 * Post Meta key for the ticket header
	 * @var string
	 */
	protected $image_header_field = '_tribe_ticket_header';

	/**
	 * Slug of the admin page for attendees
	 * @var string
	 */
	public static $attendees_slug = 'tickets-attendees';

	/**
	 * Hook of the admin page for attendees
	 * @var
	 */
	private $attendees_page;

	/**
	 * WP_Post_List children for Attendees
	 * @var Tribe__Events__Tickets__Attendees_Table
	 */
	private $attendees_table;

	/**
	 * Slug of the admin page for orders
	 * @var string
	 */
	public static $orders_slug = 'tickets-orders';

	/**
	 * @var Tribe__Events__Tickets__Google_Event_Data
	 */
	protected $google_event_data;


	/**
	 *    Class constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_tribe-ticket-email-attendee-list', array( $this, 'ajax_handler_attendee_mail_list' ) );
		add_action( 'save_post_' . Tribe__Events__Main::POSTTYPE, array( $this, 'save_image_header' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'attendees_page_register' ) );
		add_filter( 'post_row_actions', array( $this, 'attendees_row_action' ) );

		// This is sort of hacky and won't exist in 4.0 as the Orders Report has been relocated to
		// event-tickets-plus. BUT, for 3.12, let's make sure the Orders Report isn't reachable unless
		// WooTickets is active
		if ( defined( 'EVENTS_TICKETS_WOO_DIR' ) && version_compare( Tribe__Events__Tickets__Woo__Main::VERSION, '3.12.1', '>=' ) ) {
			add_action( 'admin_menu', array( $this, 'orders_page_register' ) );
			add_filter( 'post_row_actions', array( $this, 'orders_row_action' ) );
		}

		$this->path = trailingslashit( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) );
		$this->google_event_data = new Tribe__Events__Tickets__Google_Event_Data;
	}

	/**
	 * Adds the "attendees" link in the admin list row actions for each event.
	 *
	 * @param $actions
	 *
	 * @return array
	 */
	public function attendees_row_action( $actions ) {
		global $post;

		if ( $post->post_type == Tribe__Events__Main::POSTTYPE ) {
			$url = add_query_arg( array(
				'post_type' => Tribe__Events__Main::POSTTYPE,
				'page'      => self::$attendees_slug,
				'event_id'  => $post->ID,
			), admin_url( 'edit.php' ) );

			$actions['tickets_attendees'] = sprintf( '<a title="%s" href="%s">%s</a>', __( 'See who purchased tickets to this event', 'the-events-calendar' ), esc_url( $url ), __( 'Attendees', 'the-events-calendar' ) );
		}

		return $actions;
	}

	/**
	 * Registers the Attendees admin page
	 */
	public function attendees_page_register() {

		$this->attendees_page = add_submenu_page( null, 'Attendee list', 'Attendee list', 'edit_posts', self::$attendees_slug, array( $this, 'attendees_page_inside' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'attendees_page_load_css_js' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'attendees_page_load_pointers' ) );
		add_action( 'load-' . $this->attendees_page, array( $this, 'attendees_page_screen_setup' ) );

	}

	/**
	 * Enqueues the JS and CSS for the attendees page in the admin
	 *
	 * @param $hook
	 */
	public function attendees_page_load_css_js( $hook ) {
		if ( $hook != $this->attendees_page ) {
			return;
		}

		wp_enqueue_style( self::$attendees_slug, tribe_events_resource_url( 'tickets-attendees.css' ), array(), apply_filters( 'tribe_events_css_version', Tribe__Events__Main::VERSION ) );
		wp_enqueue_style( self::$attendees_slug . '-print', tribe_events_resource_url( 'tickets-attendees-print.css' ), array(), apply_filters( 'tribe_events_css_version', Tribe__Events__Main::VERSION ), 'print' );
		wp_enqueue_script( self::$attendees_slug, tribe_events_resource_url( 'tickets-attendees.js' ), array( 'jquery' ), apply_filters( 'tribe_events_js_version', Tribe__Events__Main::VERSION ) );

		$mail_data = array(
			'nonce'           => wp_create_nonce( 'email-attendee-list' ),
			'required'        => __( 'You need to select a user or type a valid email address', 'the-events-calendar' ),
			'sending'         => __( 'Sending...', 'the-events-calendar' ),
			'checkin_nonce'   => wp_create_nonce( 'checkin' ),
			'uncheckin_nonce' => wp_create_nonce( 'uncheckin' ),
		);

		wp_localize_script( self::$attendees_slug, 'Attendees', $mail_data );
	}

	/**
	 * Loads the WP-Pointer for the Attendees screen
	 *
	 * @param $hook
	 */
	public function attendees_page_load_pointers( $hook ) {
		if ( $hook != $this->attendees_page ) {
			return;
		}

		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		$pointer   = null;

		if ( version_compare( get_bloginfo( 'version' ), '3.3', '>' ) && ! in_array( 'attendees_filters', $dismissed ) ) {
			$pointer = array(
				'pointer_id' => 'attendees_filters',
				'target'     => '#screen-options-link-wrap',
				'options'    => array(
					'content' => sprintf( '<h2> %s </h2> <p> %s </p>', __( 'Columns', 'the-events-calendar' ), __( 'You can use Screen Options to select which columns you want to see. The selection works in the table below, in the email, for print and for the CSV export.', 'tribe-events-calendar' ) ),
					'position' => array( 'edge' => 'top', 'align' => 'center' ),
				),
			);
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_style( 'wp-pointer' );
		}

		wp_localize_script( self::$attendees_slug, 'AttendeesPointer', $pointer );
	}

	/**
	 *    Setups the Attendees screen data.
	 */
	public function attendees_page_screen_setup() {

		$this->attendees_table = new Tribe__Events__Tickets__Attendees_Table();

		$this->maybe_generate_attendees_csv();

		wp_enqueue_script( 'jquery-ui-dialog' );

		add_filter( 'admin_title', array( $this, 'attendees_admin_title' ), 10, 2 );

	}

	/**
	 * Sets the browser title for the Attendees admin page.
	 * Uses the event title.
	 *
	 * @param $admin_title
	 * @param $title
	 *
	 * @return string
	 */
	public function attendees_admin_title( $admin_title, $title ) {
		if ( ! empty( $_GET['event_id'] ) ) {
			$event       = get_post( $_GET['event_id'] );
			$admin_title = sprintf( '%s - Attendee list', $event->post_title );
		}

		return $admin_title;
	}

	/**
	 * Renders the Attendees page
	 */
	public function attendees_page_inside() {
		include $this->path . 'src/admin-views/tickets/attendees.php';
	}

	/**
	 * Registers the Orders admin page
	 */
	public function orders_page_register() {

		$this->orders_page = add_submenu_page(
			null, 'Order list', 'Order list', 'edit_posts', Tribe__Events__Tickets__Tickets_Pro::$orders_slug, array(
				$this,
				'orders_page_inside'
			)
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'attendees_page_load_css_js' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'attendees_page_load_pointers' ) );
		add_action( "load-$this->orders_page", array( $this, 'orders_page_screen_setup' ) );

	}

	/**
	 * Adds the "orders" link in the admin list row actions for each event.
	 *
	 * @param $actions
	 *
	 * @return array
	 */
	public function orders_row_action( $actions ) {
		global $post;

		if ( $post->post_type != Tribe__Events__Main::POSTTYPE ) {
			return $actions;
		}

		$url = add_query_arg(
			array(
				'post_type' => Tribe__Events__Main::POSTTYPE,
				'page'      => Tribe__Events__Tickets__Tickets_Pro::$orders_slug,
				'event_id'  => $post->ID,
			),
			admin_url( 'edit.php' )
		);

		$actions['tickets_orders'] = sprintf(
			'<a title="%s" href="%s">%s</a>',
			esc_html__( 'See purchases for this event', 'tribe-events-calendar' ),
			esc_url( $url ),
			esc_html__( 'Orders', 'tribe-events-calendar' )
		);

		return $actions;
	}

	/**
	 * Setups the Orders screen data.
	 */
	public function orders_page_screen_setup() {
		$this->orders_table = new Tribe__Events__Tickets__Orders_Table();
		wp_enqueue_script( 'jquery-ui-dialog' );

		add_filter( 'admin_title', array( $this, 'orders_admin_title' ), 10, 2 );
	}

	/**
	 * Sets the browser title for the Orders admin page.
	 * Uses the event title.
	 *
	 * @param $admin_title
	 * @param $title
	 *
	 * @return string
	 */
	public function orders_admin_title( $admin_title, $title ) {
		if ( ! empty( $_GET['event_id'] ) ) {
			$event       = get_post( $_GET['event_id'] );
			$admin_title = sprintf( "%s - Order list", $event->post_title );
		}

		return $admin_title;
	}

	/**
	 * Renders the Orders page
	 */
	public function orders_page_inside() {
		include $this->path . 'src/admin-views/tickets/orders.php';
	}

	/**
	 * Generates a list of attendees taking into account the Screen Options.
	 * It's used both for the Email functionality, as for the CSV export.
	 *
	 * @param $event_id
	 *
	 * @return array
	 */
	private function _generate_filtered_attendees_list( $event_id ) {

		if ( empty( $this->attendees_page ) ) {
			$this->attendees_page = 'tribe_events_page_tickets-attendees';
		}

		$columns = $this->attendees_table->get_columns();
		$hidden  = get_hidden_columns( $this->attendees_page );

		// We dont want to export html inputs or private data
		$hidden[] = 'cb';
		$hidden[] = 'provider';

		// remove the hidden fields from the final list of columns
		$hidden         = array_filter( $hidden );
		$hidden         = array_flip( $hidden );
		$export_columns = array_diff_key( $columns, $hidden );
		$columns_names  = array_filter( array_values( $export_columns ) );
		$export_columns = array_filter( array_keys( $export_columns ) );

		// Get the data
		$items = Tribe__Events__Tickets__Tickets::get_event_attendees( $event_id );

		$rows = array( $columns_names );
		//And echo the data
		foreach ( $items as $item ) {
			$row = array();
			foreach ( $item as $key => $data ) {
				if ( in_array( $key, $export_columns ) ) {
					if ( $key == 'check_in' && $data == 1 ) {
						$data = __( 'Yes', 'the-events-calendar' );
					}
					$row[ $key ] = $data;
				}
			}
			$rows[] = array_values( $row );
		}

		return array_filter( $rows );
	}

	/**
	 *    Checks if the user requested a CSV export from the attendees list.
	 *  If so, generates the download and finishes the execution.
	 */
	public function maybe_generate_attendees_csv() {

		if ( empty( $_GET['attendees_csv'] ) || empty( $_GET['attendees_csv_nonce'] ) || empty( $_GET['event_id'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['attendees_csv_nonce'], 'attendees_csv_nonce' ) || ! current_user_can( 'edit_tribe_events' ) ) {
			return;
		}


		$items = $this->_generate_filtered_attendees_list( $_GET['event_id'] );
		$event = get_post( $_GET['event_id'] );

		if ( ! empty( $items ) ) {

			$charset  = get_option( 'blog_charset' );
			$filename = sanitize_file_name( $event->post_title . '-' . __( 'attendees', 'the-events-calendar' ) );

			// output headers so that the file is downloaded rather than displayed
			header( "Content-Type: text/csv; charset=$charset" );
			header( "Content-Disposition: attachment; filename=$filename.csv" );

			// create a file pointer connected to the output stream
			$output = fopen( 'php://output', 'w' );

			//And echo the data
			foreach ( $items as $item ) {
				fputcsv( $output, $item );
			}

			fclose( $output );
			exit;
		}
	}

	/**
	 *    Handles the "send to email" action for the attendees list.
	 */
	public function ajax_handler_attendee_mail_list() {

		if ( ! isset( $_POST['event_id'] ) || ! isset( $_POST['email'] ) || ! ( is_numeric( $_POST['email'] ) || is_email( $_POST['email'] ) ) ) {
			$this->ajax_error( 'Bad post' );
		}
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'email-attendee-list' ) || ! current_user_can( 'edit_tribe_events' ) ) {
			$this->ajax_error( 'Cheatin Huh?' );
		}

		if ( is_email( $_POST['email'] ) ) {
			$email = $_POST['email'];
		} else {
			$user  = get_user_by( 'id', $_POST['email'] );
			$email = $user->data->user_email;
		}

		if ( empty( $GLOBALS['hook_suffix'] ) ) {
			$GLOBALS['hook_suffix'] = 'tribe_ajax';
		}

		$this->attendees_page_screen_setup();

		$items = $this->_generate_filtered_attendees_list( $_POST['event_id'] );

		$event = get_post( $_POST['event_id'] );

		ob_start();
		$attendee_tpl = Tribe__Events__Templates::getTemplateHierarchy( 'tickets/attendees-email.php', array( 'disable_view_check' => true ) );
		include $attendee_tpl;
		$content = ob_get_clean();

		add_filter( 'wp_mail_content_type', array( $this, 'set_contenttype' ) );
		if ( ! wp_mail( $email, sprintf( __( 'Attendee List for: %s', 'the-events-calendar' ), $event->post_title ), $content ) ) {
			$this->ajax_error( 'Error sending email' );
		}

		$this->ajax_ok( array() );
	}

	/**
	 * Sets the content type for the attendees to email functionality.
	 * Allows for sending an HTML email.
	 *
	 * @param $content_type
	 *
	 * @return string
	 */
	public function set_contenttype( $content_type ) {
		return 'text/html';
	}

	/* Tickets Metabox */

	/**
	 * Includes the tickets metabox inside the Event edit screen
	 *
	 * @param $post_id
	 */
	public function do_meta_box( $post_id ) {

		$startMinuteOptions   = Tribe__Events__View_Helpers::getMinuteOptions( null );
		$endMinuteOptions     = Tribe__Events__View_Helpers::getMinuteOptions( null );
		$startHourOptions     = Tribe__Events__View_Helpers::getHourOptions( null, true );
		$endHourOptions       = Tribe__Events__View_Helpers::getHourOptions( null, false );
		$startMeridianOptions = Tribe__Events__View_Helpers::getMeridianOptions( null, true );
		$endMeridianOptions   = Tribe__Events__View_Helpers::getMeridianOptions( null );

		$tickets = Tribe__Events__Tickets__Tickets::get_event_tickets( $post_id );
		include $this->path . 'src/admin-views/tickets/meta-box.php';
	}

	/**
	 * Echoes the markup for the tickets list in the tickets metabox
	 *
	 * @param array $tickets
	 */
	public function ticket_list_markup( $tickets = array() ) {
		if ( ! empty( $tickets ) ) {
			include $this->path . 'src/admin-views/tickets/list.php';
		}
	}

	/**
	 * Returns the markup for the tickets list in the tickets metabox
	 *
	 * @param array $tickets
	 *
	 * @return string
	 */
	public function get_ticket_list_markup( $tickets = array() ) {

		ob_start();
		$this->ticket_list_markup( $tickets );
		$return = ob_get_contents();
		ob_end_clean();

		return $return;
	}

	/**
	 * Returns the attachment ID for the header image for a event.
	 *
	 * @param $event_id
	 *
	 * @return mixed
	 */
	public function get_header_image_id( $event_id ) {
		return get_post_meta( $event_id, $this->image_header_field, true );
	}

	/**
	 * Save or delete the image header for tickets on an event
	 *
	 * @param $post_id
	 * @param $post
	 */
	public function save_image_header( $post_id, $post ) {
		// don't do anything on autosave or auto-draft either or massupdates
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( empty( $_POST['tribe_ticket_header_image_id'] ) ) {
			delete_post_meta( $post_id, $this->image_header_field );
		} else {
			update_post_meta( $post_id, $this->image_header_field, $_POST['tribe_ticket_header_image_id'] );
		}

		return;
	}


	/**
	 *
	 * @param string $message
	 */
	final protected function ajax_error( $message = '' ) {
		header( 'Content-type: application/json' );

		echo json_encode( array(
			'success' => false,
			'message' => $message,
		) );
		exit;
	}

	/**
	 * @param $data
	 */
	final protected function ajax_ok( $data ) {
		$return = array();
		if ( is_object( $data ) ) {
			$return = get_object_vars( $data );
		} elseif ( is_array( $data ) || is_string( $data ) ) {
			$return = $data;
		} elseif ( is_bool( $data ) && ! $data ) {
			$this->ajax_error( 'Something went wrong' );
		}

		header( 'Content-type: application/json' );
		echo json_encode( array(
			'success' => true,
			'data'    => $return,
		) );
		exit;
	}

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Tickets__Tickets_Pro
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className;
		}

		return self::$instance;
	}

}
