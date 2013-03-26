<?php

if ( ! class_exists( 'TribeEventsTickets' ) ) {
	abstract class TribeEventsTickets {

		// All TribeEventsTickets api consumers. It's static, so it's shared across childs.
		/**
		 * @var array
		 */
		protected static $active_modules = array();
		/**
		 * @var bool
		 */
		public static $active = false;

		/**
		 * @var
		 */
		private $handler;

		/**
		 * @var string
		 */
		public $className;
		/**
		 * @var string
		 */
		private $parentPath;
		/**
		 * @var string
		 */
		private $parentUrl;

		// start API Definitions
		// Child classes must implement all these functions / properties

		/**
		 * @var
		 */
		public $pluginName;
		/**
		 * @var
		 */
		protected $pluginPath;
		/**
		 * @var
		 */
		protected $pluginUrl;

		/**
		 * @abstract
		 *
		 * @param $event_id
		 *
		 * @return mixed
		 */
		abstract function get_event_reports_link( $event_id );

		/**
		 * @abstract
		 *
		 * @param $event_id
		 * @param $ticket_id
		 *
		 * @return mixed
		 */
		abstract function get_ticket_reports_link( $event_id, $ticket_id );

		/**
		 * @abstract
		 *
		 * @param       $event_id
		 * @param       $ticket
		 * @param array $raw_data
		 *
		 * @return mixed
		 */
		abstract function save_ticket( $event_id, $ticket, $raw_data = array() );

		/**
		 * @abstract
		 *
		 * @param $event_id
		 *
		 * @return mixed
		 */
		abstract protected function get_tickets( $event_id );

		/**
		 * @abstract
		 *
		 * @param $event_id
		 *
		 * @return mixed
		 */
		abstract protected function get_attendees( $event_id );

		/**
		 * @abstract
		 *
		 * @param $attendee_id
		 *
		 * @return mixed
		 */
		abstract public function checkin( $attendee_id );

		/**
		 * @abstract
		 *
		 * @param $attendee_id
		 *
		 * @return mixed
		 */
		abstract public function uncheckin( $attendee_id );

		/**
		 * @abstract
		 *
		 * @param $event_id
		 * @param $ticket_id
		 *
		 * @return mixed
		 */
		abstract function get_ticket( $event_id, $ticket_id );

		/**
		 * @abstract
		 *
		 * @param $event_id
		 * @param $ticket_id
		 *
		 * @return mixed
		 */
		abstract function delete_ticket( $event_id, $ticket_id );

		/**
		 * @abstract
		 *
		 * @param $event_id
		 * @param $ticket_id
		 *
		 * @return mixed
		 */
		abstract function do_metabox_advanced_options( $event_id, $ticket_id );

		/**
		 * @abstract
		 *
		 * @param $content
		 *
		 * @return mixed
		 */
		abstract function front_end_tickets_form( $content );

		/**
		 * @static
		 * @abstract
		 * @return mixed
		 */
		abstract static function get_instance();

		// end API Definitions

		/**
		 *
		 */
		function __construct() {

			TribeEventsTicketsPro::instance();

			// As this is an abstract class, we want to know which child
			// instantiated it
			$this->className = get_class( $this );

			$this->parentPath = trailingslashit( dirname( dirname( dirname( __FILE__ ) ) ) );
			$this->parentUrl  = trailingslashit( plugins_url( '', $this->parentPath ) );

			// Register all TribeEventsTickets api consumers
			self::$active_modules[$this->className] = $this->pluginName;

			self::$active = true;

			add_filter( 'tribe_events_tickets_modules', array( $this, 'modules' ) );
			add_action( 'tribe_events_tickets_metabox_advanced', array( $this, 'do_metabox_advanced_options' ), 10, 2 );

			// Admin AJAX actions
			add_action( 'wp_ajax_tribe-ticket-add-' . $this->className, array( $this, 'ajax_handler_ticket_add' ) );
			add_action( 'wp_ajax_tribe-ticket-delete-' . $this->className, array( $this, 'ajax_handler_ticket_delete' ) );
			add_action( 'wp_ajax_tribe-ticket-edit-' . $this->className, array( $this, 'ajax_handler_ticket_edit' ) );
			add_action( 'wp_ajax_tribe-ticket-checkin-' . $this->className, array( $this, 'ajax_handler_attendee_checkin' ) );
			add_action( 'wp_ajax_tribe-ticket-uncheckin-' . $this->className, array( $this, 'ajax_handler_attendee_uncheckin' ) );

			// Front end
			add_filter( 'tribe_get_ticket_form', array( $this, 'front_end_tickets_form' ) );

		}


		/**
		 * @param $tickets
		 *
		 * @return string
		 */
		public function generate_tickets_email_content( $tickets ) {
			ob_start();
			include TribeEventsTemplates::getTemplateHierarchy( 'tickets-email.php' );
			return ob_get_clean();
		}

		/* AJAX Handlers */

		/**
		 *
		 */
		public final function ajax_handler_ticket_add() {

			if ( ! isset( $_POST["formdata"] ) ) $this->ajax_error( 'Bad post' );
			if ( ! isset( $_POST["post_ID"] ) ) $this->ajax_error( 'Bad post' );

			$data    = wp_parse_args( $_POST["formdata"] );
			$post_id = $_POST["post_ID"];

			if ( ! isset( $data["ticket_provider"] ) || ! $this->module_is_valid( $data["ticket_provider"] ) ) $this->ajax_error( 'Bad module' );

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

			if ( ! empty( $data['ticket_start_date'] ) ) {
				$meridian           = ! empty( $data['ticket_start_meridian'] ) ? " " . $data['ticket_start_meridian'] : "";
				$ticket->start_date = date( TribeDateUtils::DBDATETIMEFORMAT, strtotime( $data['ticket_start_date'] . " " . $data['ticket_start_hour'] . ":" . $data['ticket_start_minute'] . ":00" . $meridian ) );
			}

			if ( ! empty( $data['ticket_end_date'] ) ) {
				$meridian         = ! empty( $data['ticket_end_meridian'] ) ? " " . $data['ticket_end_meridian'] : "";
				$ticket->end_date = date( TribeDateUtils::DBDATETIMEFORMAT, strtotime( $data['ticket_end_date'] . " " . $data['ticket_end_hour'] . ":" . $data['ticket_end_minute'] . ":00" . $meridian ) );
			}

			$ticket->provider_class = $this->className;

			// Pass the control to the child object
			$return = $this->save_ticket( $post_id, $ticket, $data );

			// If saved OK, let's create a tickets list markup to return
			if ( $return ) {
				$tickets = $this->get_event_tickets( $post_id );
				$return  = TribeEventsTicketsPro::instance()->get_ticket_list_markup( $tickets );

				$return = $this->notice( __( 'Your ticket has been saved.', 'tribe-events-calendar' ) ) . $return;
			}

			$this->ajax_ok( $return );
		}


		/**
		 *
		 */
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

		/**
		 *
		 */
		public final function ajax_handler_attendee_uncheckin() {

			if ( ! isset( $_POST["order_ID"] ) || intval( $_POST["order_ID"] ) == 0 )
				$this->ajax_error( 'Bad post' );
			if ( ! isset( $_POST["provider"] ) || ! $this->module_is_valid( $_POST["provider"] ) )
				$this->ajax_error( 'Bad module' );

			$order_id = $_POST["order_ID"];

			// Pass the control to the child object
			$return = $this->uncheckin( $order_id );

			$this->ajax_ok( $return );
		}

		/**
		 *
		 */
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
				$return  = TribeEventsTicketsPro::instance()->get_ticket_list_markup( $tickets );

				$return = $this->notice( __( 'Your ticket has been deleted.', 'tribe-events-calendar' ) ) . $return;
			}

			$this->ajax_ok( $return );
		}

		/**
		 *
		 */
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


		/**
		 * @param $msg
		 *
		 * @return string
		 */
		protected function notice( $msg ) {
			return sprintf( '<div class="wrap"><div class="updated"><p>%s</p></div></div>', $msg );
		}


		// end AJAX Handlers

		// start Attendees

		/**
		 * @static
		 *
		 * @param $event_id
		 *
		 * @return array
		 */
		final static public function get_event_attendees( $event_id ) {
			$attendees = array();
			foreach ( self::$active_modules as $class=> $module ) {
				$obj       = call_user_func( array( $class, 'get_instance' ) );
				$attendees = array_merge( $attendees, $obj->get_attendees( $event_id ) );
			}
			return $attendees;
		}

		/**
		 * @static
		 *
		 * @param $event_id
		 *
		 * @return mixed
		 */
		final static public function get_event_checkedin_attendees_count( $event_id ) {
			$checkedin = TribeEventsTickets::get_event_attendees( $event_id );
			return array_reduce( $checkedin, array( "TribeEventsTickets", "_checkedin_attendees_array_filter" ), 0 );
		}

		/**
		 * @static
		 *
		 * @param $result
		 * @param $item
		 *
		 * @return mixed
		 */
		private static function _checkedin_attendees_array_filter( $result, $item ) {
			if ( ! empty( $item['check_in'] ) )
				return $result + 1;

			return $result;
		}


		// end Attendees

		// start Helpers

		/**
		 * @param $module
		 *
		 * @return bool
		 */
		private function module_is_valid( $module ) {
			return array_key_exists( $module, self::$active_modules );
		}

		/**
		 *
		 */
		protected function tr_class() {
			echo "ticket_advanced ticket_advanced_" . $this->className;
		}

		/**
		 * @static
		 * @return array
		 */
		public static function modules() {
			return self::$active_modules;
		}

		/**
		 * @static
		 *
		 * @param $event_id
		 *
		 * @return array
		 */
		final static public function get_event_tickets( $event_id ) {

			$tickets = array();

			foreach ( self::$active_modules as $class=> $module ) {
				$obj     = call_user_func( array( $class, 'get_instance' ) );
				$tickets = array_merge( $tickets, $obj->get_tickets( $event_id ) );
			}

			return $tickets;
		}

		/**
		 * @param string $message
		 */
		protected final function ajax_error( $message = "" ) {
			header( 'Content-type: application/json' );

			echo json_encode( array( "success" => false,
									 "message" => $message ) );
			exit;
		}

		/**
		 * @param $data
		 */
		protected final function ajax_ok( $data ) {
			$return = array();
			if ( is_object( $data ) ) {
				$return = get_object_vars( $data );
			} elseif ( is_array( $data ) || is_string( $data ) ) {
				$return = $data;
			} elseif ( is_bool( $data ) && ! $data ) {
				$this->ajax_error( "Something went wrong" );
			}

			header( 'Content-type: application/json' );
			echo json_encode( array( "success" => true,
									 "data"    => $return ) );
			exit;
		}

		/**
		 * @param $template
		 *
		 * @return mixed|void
		 */
		public function getTemplateHierarchy( $template ) {

			if ( substr( $template, - 4 ) != '.php' ) {
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
