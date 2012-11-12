<?php

if ( ! class_exists( 'TribeEventsTickets' ) ) {
	abstract class TribeEventsTickets {

		// All TribeEventsTickets api consumers. It's static, so it's shared across childs.
		protected static $active_modules = array();
		public static $active = false;

		public $className;
		private $parentPath;
		private $parentUrl;
		private $attendees_slug = 'tickets-attendees';
		private $attendees_page;

		// prevent re-doing the metabox by different childs
		private static $done_metabox = false;
		private static $done_attendees_admin_page = false;

		// start API Definitions
		// Child classes must implement all these functions / properties

		public $pluginName;
		protected $pluginPath;
		protected $pluginUrl;

		abstract function get_event_reports_link( $event_id );

		abstract function get_ticket_reports_link( $event_id, $ticket_id );

		abstract function save_ticket( $event_id, $ticket, $raw_data = array() );

		abstract protected function get_tickets( $event_id );

		abstract protected function get_attendees( $event_id );

		abstract public function checkin( $attendee_id );

		abstract public function uncheckin( $attendee_id );

		abstract function get_ticket( $event_id, $ticket_id );

		abstract function delete_ticket( $event_id, $ticket_id );

		abstract function do_metabox_advanced_options( $event_id, $ticket_id );

		abstract function front_end_tickets_form( $content );

		abstract static function get_instance();

		// end API Definitions

		function __construct() {

			// As this is an abstract class, we want to know which child
			// instantiated it
			$this->className = get_class( $this );

			$this->parentPath = trailingslashit( dirname( dirname( dirname( __FILE__ ) ) ) );
			$this->parentUrl  = trailingslashit( plugins_url( '', $this->parentPath ) );

			// Register all TribeEventsTickets api consumers
			self::$active_modules[$this->className] = $this->pluginName;

			self::$active = true;

			if ( is_admin() ) {
				add_action( 'tribe_events_event_save', array( $this, 'save_tickets' ), 10, 1 );
				add_action( 'tribe_events_tickets_metabox_advanced', array( $this, 'do_metabox_advanced_options' ), 10, 2 );
			}

			add_filter( 'tribe_events_tickets_modules', array( $this, 'modules' ) );

			// Admin AJAX actions
			add_action( 'wp_ajax_tribe-ticket-add-' . $this->className, array( $this, 'ajax_handler_ticket_add' ) );
			add_action( 'wp_ajax_tribe-ticket-delete-' . $this->className, array( $this, 'ajax_handler_ticket_delete' ) );
			add_action( 'wp_ajax_tribe-ticket-edit-' . $this->className, array( $this, 'ajax_handler_ticket_edit' ) );
			add_action( 'wp_ajax_tribe-ticket-checkin-' . $this->className, array( $this, 'ajax_handler_attendee_checkin' ) );
			add_action( 'wp_ajax_tribe-ticket-uncheckin-' . $this->className, array( $this, 'ajax_handler_attendee_uncheckin' ) );

			// Attendees list
			add_filter( 'post_row_actions', array( $this, 'attendees_row_action' ) );
			add_action( 'admin_menu', array( $this, 'attendees_page_register' ) );

			// Front end
			add_filter( 'tribe_get_ticket_form', array( $this, 'front_end_tickets_form' ) );

		}

		public final function do_meta_box( $post_id ) {

			if ( self::$done_metabox )
				return;

			$startMinuteOptions   = TribeEventsViewHelpers::getMinuteOptions( null );
			$endMinuteOptions     = TribeEventsViewHelpers::getMinuteOptions( null );
			$startHourOptions     = TribeEventsViewHelpers::getHourOptions( null, true );
			$endHourOptions       = TribeEventsViewHelpers::getHourOptions( null, false );
			$startMeridianOptions = TribeEventsViewHelpers::getMeridianOptions( null, true );
			$endMeridianOptions   = TribeEventsViewHelpers::getMeridianOptions( null );

			$tickets = self::get_event_tickets( $post_id );
			include $this->parentPath . 'admin-views/tickets-meta-box.php';
			self::$done_metabox = true;

		}

		protected final function load_pdf_libraries() {
			if ( class_exists( 'FPDF' ) )
				return;

			include $this->parentPath . 'vendor/fpdf/fpdf.php';


		}

		public final function generate_attendees_PDF( $tickets_list ) {

			$this->load_pdf_libraries();

			$pdf = new FPDF();
			$ecp = TribeEvents::instance();

			$pdf->AddFont('OpenSans','','opensans.php');
			$pdf->AddFont('SteelFish','','steelfish.php');

			$pdf->SetTitle('EventTicket');
			$pdf->SetAuthor('The Events Calendar');
			$pdf->SetCreator('The Events Calendar');

			$defaults = array( 'event_id'      => 0,
			                   'ticket_name'   => '',
			                   'holder_name'   => '',
			                   'order_id'      => '',
			                   'ticket_id'     => '',
			                   'security_code' => ''
			);

			foreach ( $tickets_list as $ticket ) {

				$ticket  = wp_parse_args( $ticket, $defaults );
				$event   = get_post( $ticket['event_id'] );

				$venue_id = tribe_get_venue_id( $event->ID );
				$venue    = ( !empty( $venue_id ) ) ? get_post( $venue_id )->post_title : '';

				$address = tribe_get_address( $event->ID );
				$zip     = tribe_get_zip( $event->ID );
				$state   = tribe_get_stateprovince( $event->ID );
				$city    = tribe_get_city( $event->ID );

				$pdf->AddPage();

				$pdf->SetDrawColor( 28, 166, 205 );
				$pdf->SetFillColor( 28, 166, 205 );
				$pdf->Rect( 15, 10, 180, 34, 'F' );

				$pdf->SetTextColor( 255 );

				$pdf->SetFont( 'OpenSans', '', 10 );
				$pdf->SetXY( 30, 15 );
				$pdf->Write( 5, __( 'EVENT NAME:', 'tribe-events-calendar' ) );

				$pdf->SetXY( 30, 28 );
				$pdf->SetFont( 'SteelFish', '', 53 );

				$title = strtoupper( utf8_decode( $event->post_title ) );
				$size  = 53;

				while ( $pdf->GetStringWidth( $title ) > 151 ) {
					$size--;
					$pdf->SetFontSize( $size );
				}

				$pdf->Write( 5, $title );

				$pdf->SetTextColor( 41 );

				$pdf->SetFont( 'OpenSans', '', 10 );
				$pdf->SetXY( 30, 50 );
				$pdf->Write( 5, __( 'TICKET HOLDER:', 'tribe-events-calendar' ) );
				$pdf->SetXY( 104, 50 );
				$pdf->Write( 5, __( 'LOCATION:', 'tribe-events-calendar' ) );

				$pdf->SetFont( 'SteelFish', '', 30 );

				$pdf->SetXY( 30, 59 );
				$holder = strtoupper( utf8_decode( $ticket['holder_name'] ) );
				$size  = 30;
				while ( $pdf->GetStringWidth( $holder ) > 70 ) {
					$size--;
					$pdf->SetFontSize( $size );
				}
				$pdf->Write( 5, $holder );


				$pdf->SetXY( 104, 59 );
				$venue = strtoupper( utf8_decode( $venue ) );
				$size  = 30;
				while ( $pdf->GetStringWidth( $venue ) > 70 ) {
					$size--;
					$pdf->SetFontSize( $size );
				}
				$pdf->Write( 5, $venue );

				$pdf->SetXY( 104, 71 );

				$address = strtoupper( utf8_decode( $address ) );
				$size  = 30;
				while ( $pdf->GetStringWidth( $address ) > 70 ) {
					$size--;
					$pdf->SetFontSize( $size );
				}
				$pdf->Write( 5, $address );

				$pdf->SetXY( 104, 83 );

				$address2 = array( $city, $state, $zip );
				$address2 = array_filter( $address2 );
				$address2 = join( ', ', $address2 );
				$address2 = strtoupper( utf8_decode( $address2 ) );

				$size = 30;
				while ( $pdf->GetStringWidth( $address2 ) > 70 ) {
					$size--;
					$pdf->SetFontSize( $size );
				}
				$pdf->Write( 5, $address2 );

				$pdf->Line( 15, 97, 195, 97 );

				$pdf->SetFont( 'OpenSans', '', 10 );
				$pdf->SetXY( 30, 105 );
				$pdf->Write( 5, __( 'ORDER:', 'tribe-events-calendar' ) );
				$pdf->SetXY( 80, 105 );
				$pdf->Write( 5, __( 'TICKET:', 'tribe-events-calendar' ) );
				$pdf->SetXY( 120, 105 );
				$pdf->Write( 5, __( 'VERIFICATION:', 'tribe-events-calendar' ) );

				$pdf->SetFont( 'SteelFish', '', 53 );
				$pdf->SetXY( 30, 118 );
				$pdf->Write( 5, $ticket['order_id'] );
				$pdf->SetXY( 80, 118 );
				$pdf->Write( 5, $ticket['ticket_id'] );
				$pdf->SetXY( 120, 118 );
				$pdf->Write( 5, $ticket['security_code'] );

				$pdf->Rect( 15, 135, 180, 15, 'F' );

				$pdf->SetTextColor( 255 );

				$pdf->SetFont( 'OpenSans', '', 10 );
				$pdf->SetXY( 30, 140 );
				$pdf->Write( 5, get_bloginfo('name') );
				$pdf->SetXY( 104, 140 );
				$pdf->Write( 5, get_home_url() );


			}

			$upload_path = wp_upload_dir();
			$upload_url  = $upload_path['url'];
			$upload_path = $upload_path['path'];

			$filename = wp_unique_filename( $upload_path, sanitize_file_name( md5( time() ) ) . '.pdf' );

			$upload_path = trailingslashit( $upload_path ) . $filename;
			$upload_url  = trailingslashit( $upload_url ) . $filename;

			$pdf->Output( $upload_path, 'F' );

			return array( $upload_path, $upload_url );

		}

		/* AJAX Handlers */

		public final function ajax_handler_ticket_add() {

			if ( ! isset( $_POST["formdata"] ) ) $this->ajax_error( 'Bad post' );
			if ( ! isset( $_POST["post_ID"] ) ) $this->ajax_error( 'Bad post' );

			$data    = wp_parse_args( $_POST["formdata"] );
			$post_id = $_POST["post_ID"];

			if ( !isset( $data["ticket_provider"] ) || !$this->module_is_valid( $data["ticket_provider"] ) ) $this->ajax_error( 'Bad module' );

			$ticket = new TribeEventsTicketObject();

			$ticket->ID          = isset( $data["ticket_id"] ) ? $data["ticket_id"] : null;
			$ticket->name        = isset( $data["ticket_name"] ) ? $data["ticket_name"] : null;
			$ticket->description = isset( $data["ticket_description"] ) ? $data["ticket_description"] : null;
			$ticket->price       = isset( $data["ticket_price"] ) ? trim( $data["ticket_price"] ) : 0;

			if ( empty( $ticket->price ) ) {
				$ticket->price = 0;
			} else {
				//remove non-money characters
				$ticket->price = preg_replace( '/[^0-9\.]/Uis', '', $ticket->price );
			}

			if ( !empty( $data['ticket_start_date'] ) ) {
				$meridian           = !empty( $data['ticket_start_meridian'] ) ? " " . $data['ticket_start_meridian'] : "";
				$ticket->start_date = date( TribeDateUtils::DBDATETIMEFORMAT, strtotime( $data['ticket_start_date'] . " " . $data['ticket_start_hour'] . ":" . $data['ticket_start_minute'] . ":00" . $meridian ) );
			}

			if ( !empty( $data['ticket_end_date'] ) ) {
				$meridian         = !empty( $data['ticket_end_meridian'] ) ? " " . $data['ticket_end_meridian'] : "";
				$ticket->end_date = date( TribeDateUtils::DBDATETIMEFORMAT, strtotime( $data['ticket_end_date'] . " " . $data['ticket_end_hour'] . ":" . $data['ticket_end_minute'] . ":00" . $meridian ) );
			}

			$ticket->provider_class = $this->className;

			// Pass the control to the child object
			$return = $this->save_ticket( $post_id, $ticket, $data );

			// If saved OK, let's create a tickets list markup to return
			if ( $return ) {
				$tickets = $this->get_event_tickets( $post_id );
				$return  = $this->get_ticket_list_markup( $tickets );

				$return = $this->notice( __( 'Your ticket has been saved.', 'tribe-events-calendar' ) ) . $return;
			}

			$this->ajax_ok( $return );
		}

		public final function ajax_handler_attendee_checkin() {

			if ( ! isset( $_POST["order_ID"] ) || intval( $_POST["order_ID"] ) == 0 )
				$this->ajax_error( 'Bad post' );
			if ( ! isset( $_POST["provider"] ) || ! $this->module_is_valid( $_POST["provider"] ) )
				$this->ajax_error( 'Bad module' );

			$order_id = $_POST["order_ID"];

			// Pass the control to the child object
			$return = $this->checkin( $order_id );

			$this->ajax_ok( $return );
		}

		public final function ajax_handler_attendee_uncheckin() {

			if ( !isset( $_POST["order_ID"] ) || intval( $_POST["order_ID"] ) == 0 )
				$this->ajax_error( 'Bad post' );
			if ( !isset( $_POST["provider"] ) || !$this->module_is_valid( $_POST["provider"] ) )
				$this->ajax_error( 'Bad module' );

			$order_id = $_POST["order_ID"];

			// Pass the control to the child object
			$return = $this->uncheckin( $order_id );

			$this->ajax_ok( $return );
		}

		public final function ajax_handler_ticket_delete() {

			if ( ! isset( $_POST["post_ID"] ) )
				$this->ajax_error( 'Bad post' );
			if ( ! isset( $_POST["ticket_id"] ) )
				$this->ajax_error( 'Bad post' );

			$post_id   = $_POST["post_ID"];
			$ticket_id = $_POST["ticket_id"];

			// Pass the control to the child object
			$return = $this->delete_ticket( $post_id, $ticket_id );

			// If deleted OK, let's create a tickets list markup to return
			if ( $return ) {
				$tickets = $this->get_event_tickets( $post_id );
				$return  = $this->get_ticket_list_markup( $tickets );

				$return = $this->notice( __( 'Your ticket has been deleted.', 'tribe-events-calendar' ) ) . $return;
			}

			$this->ajax_ok( $return );
		}

		public final function ajax_handler_ticket_edit() {

			if ( ! isset( $_POST["post_ID"] ) )
				$this->ajax_error( 'Bad post' );
			if ( ! isset( $_POST["ticket_id"] ) )
				$this->ajax_error( 'Bad post' );

			$post_id   = $_POST["post_ID"];
			$ticket_id = $_POST["ticket_id"];

			$return = get_object_vars( $this->get_ticket( $post_id, $ticket_id ) );

			ob_start();
			$this->do_metabox_advanced_options( $post_id, $ticket_id );
			$extra = ob_get_contents();
			ob_end_clean();

			$return["advanced_fields"] = $extra;

			$this->ajax_ok( $return );
		}

		protected function notice( $msg ) {
			return sprintf( '<div class="wrap"><div class="updated"><p>%s</p></div></div>', $msg );
		}

		protected final function ajax_error( $message = "" ) {
			header( 'Content-type: application/json' );

			echo json_encode( array( "success" => false,
									 "message" => $message ) );
			exit;
		}

		protected final function ajax_ok( $data ) {
			$return = array();
			if ( is_object( $data ) ) {
				$return = get_object_vars( $data );
			} elseif ( is_array( $data ) || is_string( $data ) ) {
				$return = $data;
			} elseif ( is_bool( $data ) && !$data ) {
				$this->ajax_error( "Something went wrong" );
			}

			header( 'Content-type: application/json' );
			echo json_encode( array( "success" => true,
									 "data"    => $return ) );
			exit;
		}

		// end AJAX Handlers

		// start Attendees

		public function attendees_row_action( $actions ) {
			global $post;

			if ( $post->post_type == TribeEvents::POSTTYPE ) {


				$url = add_query_arg( array( 'post_type' => TribeEvents::POSTTYPE,
				                             'page'      => $this->attendees_slug,
				                             'event_id'  => $post->ID ), admin_url( 'edit.php' ) );

				$actions['tickets_attendees'] = sprintf( '<a title="%s" href="%s">%s</a>', __( 'See who purchased tickets to this event', 'tribe-events-calendar' ), esc_url( $url ), __( 'Attendees', 'tribe-events-calendar' ) );
			}
			return $actions;
		}

		public function attendees_page_register() {
			if ( self::$done_attendees_admin_page )
				return;

			$this->attendees_page = add_submenu_page( null, 'Attendee list', 'Attendee list', 'edit_posts', $this->attendees_slug, array( $this, 'attendees_page_inside' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'attendees_page_load_css_js' ) );

			self::$done_attendees_admin_page = true;
		}

		public function attendees_page_load_css_js( $hook ) {

			if ( $hook != $this->attendees_page )
				return;

			$ecp = TribeEvents::instance();
			
			wp_enqueue_style( $this->attendees_slug, trailingslashit( $ecp->pluginUrl ) . '/resources/tickets-attendees.css' );
			wp_enqueue_script( $this->attendees_slug, trailingslashit( $ecp->pluginUrl ) . '/resources/tickets-attendees.js', array( 'jquery' ) );
		}

		public function attendees_page_inside() {

			require_once 'tribe-tickets-attendees.php';
			$attendees_table = new TribeEventsTicketsAttendeesTable();
			$attendees_table->prepare_items();

			include $this->parentPath . 'admin-views/tickets-attendees.php';
		}

		final static public function get_event_attendees( $event_id ) {
			$attendees = array();

			foreach ( self::$active_modules as $class=> $module ) {
				$obj = call_user_func( array( $class, 'get_instance' ) );
				$attendees = array_merge( $attendees, $obj->get_attendees( $event_id ) );
			}

			return $attendees;
		}


		// endA ttendees

		// start Helpers

		private function module_is_valid( $module ) {
			return array_key_exists( $module, self::$active_modules );
		}

		private function ticket_list_markup( $tickets = array() ) {
			if ( ! empty( $tickets ) )
				include $this->parentPath . 'admin-views/tickets-list.php';
		}

		private function get_ticket_list_markup( $tickets = array() ) {

			ob_start();
			$this->ticket_list_markup( $tickets );
			$return = ob_get_contents();
			ob_end_clean();

			return $return;
		}

		protected function tr_class() {
			echo "ticket_advanced ticket_advanced_" . $this->className;
		}

		public function modules() {
			return self::$active_modules;
		}

		final static public function get_event_tickets( $event_id ) {

			$tickets = array();

			foreach ( self::$active_modules as $class=> $module ) {
				$obj = call_user_func( array( $class, 'get_instance' ) );
				$tickets = array_merge( $tickets, $obj->get_tickets( $event_id ) );
			}

			return $tickets;
		}

		public function getTemplateHierarchy( $template ) {

			if ( substr( $template, -4 ) != '.php' ) {
				$template .= '.php';
			}

			if ( $theme_file = locate_template( array( 'events/' . $template ) ) ) {
				$file = $theme_file;
			} else {
				$file = $this->pluginPath . 'views/' . $template;
			}
			return apply_filters( 'tribe_events_tickets_template_' . $template, $file );
		}

		// end Helpers
	}
}
