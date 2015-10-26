<?php

if ( ! class_exists( 'Tribe__Events__Tickets__Tickets' ) ) {
	/**
	 * Abstract class with the API definition and common functionality
	 * for Tribe Tickets Pro. Providers for this functionality need to
	 * extend this class. For a functional example of how this works
	 * see Tribe WooTickets.
	 */
	abstract class Tribe__Events__Tickets__Tickets {

		/**
		 * All Tribe__Events__Tickets__Tickets api consumers. It's static, so it's shared across all child.
		 *
		 * @var array
		 */
		protected static $active_modules = array();

		/**
		 * Name of this class. Note that it refers to the child class.
		 * @var string
		 */
		public $className;

		/**
		 * Path of the parent class
		 * @var string
		 */
		private $parentPath;

		/**
		 * URL of the parent class
		 * @var string
		 */
		private $parentUrl;

		// start API Definitions
		// Child classes must implement all these functions / properties

		/**
		 * Name of the provider
		 * @var
		 */
		public $pluginName;

		/**
		 * Path of the child class
		 * @var
		 */
		protected $pluginPath;

		/**
		 * URL of the child class
		 * @var
		 */
		protected $pluginUrl;

		/**
		 * Returns link to the report interface for sales for an event or
		 * null if the provider doesn't have reporting capabilities.
		 * @abstract
		 *
		 * @param $event_id
		 *
		 * @return mixed
		 */
		abstract function get_event_reports_link( $event_id );

		/**
		 * Returns link to the report interface for sales for a single ticket or
		 * null if the provider doesn't have reporting capabilities.
		 *
		 * @abstract
		 *
		 * @param $event_id
		 * @param $ticket_id
		 *
		 * @return mixed
		 */
		abstract function get_ticket_reports_link( $event_id, $ticket_id );

		/**
		 * Returns a single ticket
		 *
		 * @abstract
		 *
		 * @param $event_id
		 * @param $ticket_id
		 *
		 * @return mixed
		 */
		abstract function get_ticket( $event_id, $ticket_id );

		/**
		 * Returns the event post corresponding to the possible ticket object/ticket ID.
		 *
		 * This is used to help differentiate between products which act as tickets for an
		 * event and those which do not. If $possible_ticket is not related to any events
		 * then boolean false will be returned.
		 *
		 * This stub method should be treated as if it were an abstract method - ie, the
		 * concrete class ought to provide the implementation.
		 *
		 * @todo convert to abstract method in 4.0
		 *
		 * @param $possible_ticket
		 *
		 * @return bool|WP_Post
		 */
		public function get_event_for_ticket( $possible_ticket ) {
			return false;
		}

		/**
		 * Deletes a ticket
		 *
		 * @abstract
		 *
		 * @param $event_id
		 * @param $ticket_id
		 *
		 * @return mixed
		 */
		abstract function delete_ticket( $event_id, $ticket_id );

		/**
		 * Saves a ticket
		 *
		 * @abstract
		 *
		 * @param int   $event_id
		 * @param int   $ticket
		 * @param array $raw_data
		 *
		 * @return mixed
		 */
		abstract function save_ticket( $event_id, $ticket, $raw_data = array() );

		/**
		 * Get all the tickets for an event
		 *
		 * @abstract
		 *
		 * @param int $event_id
		 *
		 * @return array mixed
		 */
		abstract protected function get_tickets( $event_id );

		/**
		 * Get all the attendees (sold tickets) for an event
		 * @abstract
		 *
		 * @param $event_id
		 *
		 * @return mixed
		 */
		abstract protected function get_attendees( $event_id );

		/**
		 * Mark an attendee as checked in
		 *
		 * @abstract
		 *
		 * @param $attendee_id
		 *
		 * @return mixed
		 */
		abstract public function checkin( $attendee_id );

		/**
		 * Mark an attendee as not checked in
		 *
		 * @abstract
		 *
		 * @param $attendee_id
		 *
		 * @return mixed
		 */
		abstract public function uncheckin( $attendee_id );


		/**
		 * Renders the advanced fields in the new/edit ticket form.
		 * Using the method, providers can add as many fields as
		 * they want, specific to their implementation.
		 *
		 * @abstract
		 *
		 * @param $event_id
		 * @param $ticket_id
		 *
		 * @return mixed
		 */
		abstract function do_metabox_advanced_options( $event_id, $ticket_id );

		/**
		 * Renders the front end form for selling tickets in the event single page
		 *
		 * @abstract
		 *
		 * @param $content
		 *
		 * @return mixed
		 */
		abstract function front_end_tickets_form( $content );

		/**
		 * Returns the markup for the price field
		 * (it may contain the user selected currency, etc)
		 *
		 * @param object|int $product
		 *
		 * @return string
		 */
		function get_price_html( $product ) {
			return '';
		}

		/**
		 * Returns instance of the child class (singleton)
		 *
		 * @static
		 * @abstract
		 * @return mixed
		 */
		static function get_instance() {}

		// end API Definitions

		/**
		 *
		 */
		function __construct() {

			// Start the singleton with the generic functionality to all providers.
			Tribe__Events__Tickets__Tickets_Pro::instance();

			// As this is an abstract class, we want to know which child instantiated it
			$this->className = get_class( $this );

			$this->parentPath = trailingslashit( dirname( dirname( dirname( __FILE__ ) ) ) );
			$this->parentUrl  = trailingslashit( plugins_url( '', $this->parentPath ) );

			// Register all Tribe__Events__Tickets__Tickets api consumers
			self::$active_modules[$this->className] = $this->pluginName;

			add_filter( 'tribe_events_tickets_modules', array( $this, 'modules' ) );
			add_action( 'tribe_events_tickets_metabox_advanced', array( $this, 'do_metabox_advanced_options' ), 10, 2 );

			// Admin AJAX actions for each provider
			add_action( 'wp_ajax_tribe-ticket-add-'       . $this->className, array( $this, 'ajax_handler_ticket_add' 		  ) );
			add_action( 'wp_ajax_tribe-ticket-delete-'    . $this->className, array( $this, 'ajax_handler_ticket_delete' 	  ) );
			add_action( 'wp_ajax_tribe-ticket-edit-'      . $this->className, array( $this, 'ajax_handler_ticket_edit' 		  ) );
			add_action( 'wp_ajax_tribe-ticket-checkin-'   . $this->className, array( $this, 'ajax_handler_attendee_checkin'   ) );
			add_action( 'wp_ajax_tribe-ticket-uncheckin-' . $this->className, array( $this, 'ajax_handler_attendee_uncheckin' ) );

			// Front end
			add_action( 'tribe_events_single_event_after_the_meta', array( $this, 'front_end_tickets_form' ), 5 );

			// Ensure ticket prices and event costs are linked
			add_filter( 'tribe_events_event_costs', array( $this, 'get_ticket_prices' ), 10, 2 );
		}


		/* AJAX Handlers */

		/**
		 *    Sanitizes the data for the new/edit ticket ajax call,
		 *  and calls the child save_ticket function.
		 */
		public final function ajax_handler_ticket_add() {

			if ( ! isset( $_POST["formdata"] ) ) {
				$this->ajax_error( 'Bad post' );
			}
			if ( ! isset( $_POST["post_ID"] ) )
				$this->ajax_error( 'Bad post' );

			/*
			 This is needed because a provider can implement a dynamic set of fields.
			 Each provider is responsible for sanitizing these values.
			*/
			$data = wp_parse_args( $_POST["formdata"] );


			$post_id = $_POST["post_ID"];

			if ( empty( $_POST["nonce"] ) || ! wp_verify_nonce( $_POST["nonce"], 'add_ticket_nonce' ) || ! current_user_can( 'edit_tribe_events' ) )
				$this->ajax_error( "Cheatin' huh?" );

			if ( ! isset( $data["ticket_provider"] ) || ! $this->module_is_valid( $data["ticket_provider"] ) )
				$this->ajax_error( 'Bad module' );

			$ticket = new Tribe__Events__Tickets__Ticket_Object();

			$ticket->ID          = isset( $data["ticket_id"] ) ? absint( $data["ticket_id"] ) : null;
			$ticket->name        = isset( $data["ticket_name"] ) ? esc_html( $data["ticket_name"] ) : null;
			$ticket->description = isset( $data["ticket_description"] ) ? esc_html( $data["ticket_description"] ) : null;
			$ticket->price       = ! empty( $data["ticket_price"] ) ? trim( $data["ticket_price"] ) : 0;
			$ticket->purchase_limit = isset( $data['ticket_purchase_limit'] ) ? absint( $data['ticket_purchase_limit' ] ) : apply_filters( 'tribe_tickets_default_purchase_limit', 0, $ticket->ID );

			if ( ! empty( $ticket->price ) ) {
				//remove non-money characters
				$ticket->price = preg_replace( '/[^0-9\.]/Uis', '', $ticket->price );
			}

			if ( ! empty( $data['ticket_start_date'] ) ) {
				$meridian           = ! empty( $data['ticket_start_meridian'] ) ? " " . $data['ticket_start_meridian'] : "";
				$ticket->start_date = date( Tribe__Events__Date_Utils::DBDATETIMEFORMAT, strtotime( $data['ticket_start_date'] . " " . $data['ticket_start_hour'] . ":" . $data['ticket_start_minute'] . ":00" . $meridian ) );
			}

			if ( ! empty( $data['ticket_end_date'] ) ) {
				$meridian         = ! empty( $data['ticket_end_meridian'] ) ? " " . $data['ticket_end_meridian'] : "";
				$ticket->end_date = date( Tribe__Events__Date_Utils::DBDATETIMEFORMAT, strtotime( $data['ticket_end_date'] . " " . $data['ticket_end_hour'] . ":" . $data['ticket_end_minute'] . ":00" . $meridian ) );
			}

			$ticket->provider_class = $this->className;

			// Pass the control to the child object
			$return = $this->save_ticket( $post_id, $ticket, $data );

			// Successful?
			if ( $return ) {
				// Let's create a tickets list markup to return
				$tickets = $this->get_event_tickets( $post_id );
				$return  = Tribe__Events__Tickets__Tickets_Pro::instance()->get_ticket_list_markup( $tickets );

				$return = $this->notice( __( 'Your ticket has been saved.', 'the-events-calendar' ) ) . $return;

				// Additionally ensure the event costs meta data is updated accordingly
				Tribe__Events__API::update_event_cost( $post_id );
			}

			$this->ajax_ok( $return );
		}


		/**
		 *    Handles the check-in ajax call, and calls the
		 *  checkin method.
		 */
		public final function ajax_handler_attendee_checkin() {

			if ( ! isset( $_POST["order_ID"] ) || intval( $_POST["order_ID"] ) == 0 )
				$this->ajax_error( 'Bad post' );
			if ( ! isset( $_POST["provider"] ) || ! $this->module_is_valid( $_POST["provider"] ) )
				$this->ajax_error( 'Bad module' );

			if ( empty( $_POST["nonce"] ) || ! wp_verify_nonce( $_POST["nonce"], 'checkin' ) || ! current_user_can( 'edit_tribe_events' ) )
				$this->ajax_error( "Cheatin' huh?" );

			$order_id = $_POST["order_ID"];

			// Pass the control to the child object
			$return = $this->checkin( $order_id );

			$this->ajax_ok( $return );
		}

		/**
		 *  Handles the check-in ajax call, and calls the
		 *  uncheckin method.
		 */
		public final function ajax_handler_attendee_uncheckin() {

			if ( ! isset( $_POST["order_ID"] ) || intval( $_POST["order_ID"] ) == 0 )
				$this->ajax_error( 'Bad post' );
			if ( ! isset( $_POST["provider"] ) || ! $this->module_is_valid( $_POST["provider"] ) )
				$this->ajax_error( 'Bad module' );

			if ( empty( $_POST["nonce"] ) || ! wp_verify_nonce( $_POST["nonce"], 'uncheckin' ) || ! current_user_can( 'edit_tribe_events' ) )
				$this->ajax_error( "Cheatin' huh?" );


			$order_id = $_POST["order_ID"];

			// Pass the control to the child object
			$return = $this->uncheckin( $order_id );

			$this->ajax_ok( $return );
		}

		/**
		 *  Sanitizes the data for the delete ticket ajax call,
		 *  and calls the child delete_ticket function.
		 */
		public final function ajax_handler_ticket_delete() {

			if ( ! isset( $_POST["post_ID"] ) )
				$this->ajax_error( 'Bad post' );
			if ( ! isset( $_POST["ticket_id"] ) )
				$this->ajax_error( 'Bad post' );

			if ( empty( $_POST["nonce"] ) || ! wp_verify_nonce( $_POST["nonce"], 'remove_ticket_nonce' ) || ! current_user_can( 'edit_tribe_events' ) )
				$this->ajax_error( "Cheatin' huh?" );

			$post_id   = $_POST["post_ID"];
			$ticket_id = $_POST["ticket_id"];

			// Pass the control to the child object
			$return = $this->delete_ticket( $post_id, $ticket_id );

			// Successfully deleted?
			if ( $return ) {
				// Let's create a tickets list markup to return
				$tickets = $this->get_event_tickets( $post_id );
				$return  = Tribe__Events__Tickets__Tickets_Pro::instance()->get_ticket_list_markup( $tickets );

				$return = $this->notice( __( 'Your ticket has been deleted.', 'the-events-calendar' ) ) . $return;

				// Additionally ensure the event costs meta data is updated accordingly
				Tribe__Events__API::update_event_cost( $post_id );
			}

			$this->ajax_ok( $return );
		}

		/**
		 * Returns the data from a single ticket to populate
		 * the edit form.
		 */
		public final function ajax_handler_ticket_edit() {

			if ( ! isset( $_POST["post_ID"] ) )
				$this->ajax_error( 'Bad post' );
			if ( ! isset( $_POST["ticket_id"] ) )
				$this->ajax_error( 'Bad post' );

			if ( empty( $_POST["nonce"] ) || ! wp_verify_nonce( $_POST["nonce"], 'edit_ticket_nonce' ) || ! current_user_can( 'edit_tribe_events' ) )
				$this->ajax_error( "Cheatin' huh?" );

			$post_id   = $_POST["post_ID"];
			$ticket_id = $_POST["ticket_id"];
			$ticket = $this->get_ticket( $post_id, $ticket_id );
			$ticket->purchase_limit = isset( $_POST['ticket_purchase_limit'] ) ? absint( $_POST['ticket_purchase_limit' ] ) : apply_filters( 'tribe_tickets_default_purchase_limit', 0, $ticket->ID );

			$return = get_object_vars( $ticket );

			/**
			 * Allow for the prevention of updating ticket price on update.
			 *
			 * @var boolean
			 * @var WP_Post
			 */
			$can_update_price = apply_filters( 'tribe_tickets_can_update_ticket_price', true, $ticket );

			$return['can_update_price'] = $can_update_price;

			if ( ! $can_update_price ) {
				/**
				 * Filter the no-update message that is displayed when updating the price is disallowed
				 *
				 * @var string
				 * @var WP_Post
				 */
				$return['disallow_update_price_message'] = apply_filters( 'tribe_tickets_disallow_update_ticket_price_message', esc_html__( 'Editing the ticket price is currently disallowed.', 'the-events-calendar' ), $ticket );
			}

			ob_start();
			$this->do_metabox_advanced_options( $post_id, $ticket_id );
			$extra = ob_get_contents();
			ob_end_clean();

			$return["advanced_fields"] = $extra;

			$this->ajax_ok( $return );
		}


		/**
		 * Returns the markup for a notice in the admin
		 *
		 * @param string $msg Text for the notice
		 *
		 * @return string Notice with markup
		 */
		protected function notice( $msg ) {
			return sprintf( '<div class="wrap"><div class="updated"><p>%s</p></div></div>', $msg );
		}


		// end AJAX Handlers

		// start Attendees

		/**
		 * Returns all the attendees for an event. Queries all registered providers.
		 *
		 * @static
		 *
		 * @param $event_id
		 *
		 * @return array
		 */
		public static function get_event_attendees( $event_id ) {
			$attendees = array();

			foreach ( self::$active_modules as $class => $module ) {
				$obj       = call_user_func( array( $class, 'get_instance' ) );
				$attendees = array_merge( $attendees, $obj->get_attendees( $event_id ) );
			}

			return $attendees;
		}

		/**
		 * Returns all tickets for an event (all providers are queried for this information).
		 *
		 * @param $event_id
		 *
		 * @return array
		 */
		public static function get_all_event_tickets( $event_id ) {
			$tickets = array();

			foreach ( self::$active_modules as $class => $module ) {
				$obj     = call_user_func( array( $class, 'get_instance' ) );
				$tickets = array_merge( $tickets, $obj->get_tickets( $event_id ) );
			}

			return $tickets;
		}

		/**
		 * Tests to see if the provided object/ID functions as a ticket for the event
		 * and returns the corresponding event if so (or else boolean false).
		 *
		 * All registered providers are asked to perform this test.
		 *
		 * @param $possible_ticket
		 * @return bool
		 */
		public static function find_matching_event( $possible_ticket ) {
			foreach ( self::$active_modules as $class => $module ) {
				$obj   = call_user_func( array( $class, 'get_instance' ) );
				$event = $obj->get_event_for_ticket( $possible_ticket );
				if ( false !== $event ) return $event;
			}

			return false;
		}

		/**
		 * Returns the sum of all checked-in attendees for an event. Queries all registered providers.
		 *
		 * @static
		 *
		 * @param $event_id
		 *
		 * @return mixed
		 */
		final static public function get_event_checkedin_attendees_count( $event_id ) {
			$checkedin = Tribe__Events__Tickets__Tickets::get_event_attendees( $event_id );

			return array_reduce( $checkedin, array( "Tribe__Events__Tickets__Tickets", "_checkedin_attendees_array_filter" ), 0 );
		}

		/**
		 * Internal function to use as a callback for array_reduce in
		 * get_event_checkedin_attendees_count. It increments the counter
		 * if the attendee is checked-in.
		 *
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
		 * Returns whether a class name is a valid active module/provider.
		 *
		 * @param $module
		 *
		 * @return bool
		 */
		private function module_is_valid( $module ) {
			return array_key_exists( $module, self::$active_modules );
		}

		/**
		 * Echos the class for the <tr> in the tickets list admin
		 */
		protected function tr_class() {
			echo "ticket_advanced ticket_advanced_" . $this->className;
		}

		/**
		 * Returns the array of active modules/providers.
		 *
		 * @static
		 * @return array
		 */
		public static function modules() {
			return self::$active_modules;
		}

		/**
		 * Get all the tickets for an event. Queries all active modules/providers.
		 *
		 * @static
		 *
		 * @param $event_id
		 *
		 * @return array
		 */
		final static public function get_event_tickets( $event_id ) {

			$tickets = array();

			foreach ( self::$active_modules as $class => $module ) {
				$obj     = call_user_func( array( $class, 'get_instance' ) );
				$tickets = array_merge( $tickets, $obj->get_tickets( $event_id ) );
			}

			return $tickets;
		}

		/**
		 * Sets an AJAX error, returns a JSON array and ends the execution.
		 *
		 * @param string $message
		 */
		protected final function ajax_error( $message = "" ) {
			header( 'Content-type: application/json' );

			echo json_encode(
				array(
					"success" => false,
					"message" => $message
				)
			);
			exit;
		}

		/**
		 * Sets an AJAX response, returns a JSON array and ends the execution.
		 *
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
			echo json_encode(
				array(
					"success" => true,
					"data"    => $return
				)
			);
			exit;
		}

		/**
		 * Generates and returns the email template for a group of attendees.
		 *
		 * @param $tickets
		 *
		 * @return string
		 */
		public function generate_tickets_email_content( $tickets ) {
			ob_start();
			include Tribe__Events__Templates::getTemplateHierarchy( 'tickets/email.php', array( 'namespace' => 'tickets' ) );

			return ob_get_clean();
		}

		/**
		 * Gets the view from the plugin's folder, or from the user's theme if found.
		 *
		 * @param $template
		 *
		 * @return mixed|void
		 */
		public function getTemplateHierarchy( $template ) {

			if ( substr( $template, - 4 ) != '.php' ) {
				$template .= '.php';
			}

			if ( $theme_file = locate_template( array( 'tribe-events/' . $template ) ) ) {
				$file = $theme_file;
			} else {
				$file = $this->pluginPath . 'src/views/' . $template;
			}

			return apply_filters( 'tribe_events_tickets_template_' . $template, $file );
		}

		/**
		 * Queries ticketing providers to establish the range of tickets/pricepoints for the specified
		 * event and ensures those costs are included in the $costs array.
		 *
		 * @param  array $prices
		 * @param  int   $event_id
		 *
		 * @return array
		 */
		public function get_ticket_prices( array $prices, $event_id ) {
			// Iterate through all tickets from all providers
			foreach ( self::get_all_event_tickets( $event_id ) as $ticket ) {
				// No need to add the pricepoint if it is already in the array
				if ( in_array( $ticket->price, $prices ) ) {
					continue;
				}


				// An empty price property can be ignored (but do add if the price is explicitly set to zero)
				elseif ( isset( $ticket->price ) && is_numeric( $ticket->price ) ) {
					$prices[] = $ticket->price;
				}
			}

			return $prices;
		}

		// end Helpers
	}
}
