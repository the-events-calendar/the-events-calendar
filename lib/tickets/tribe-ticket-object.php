<?php
if ( ! class_exists( 'TribeEventsTicketObject' ) ) {
	/**
	 *    Generic object to hold information about a single ticket
	 */
	class TribeEventsTicketObject {

		/**
		 * Unique identifier
		 * @var
		 */
		public $ID;
		/**
		 * Name of the ticket
		 * @var string
		 */
		public $name;

		/**
		 * Free text with a description of the ticket
		 * @var string
		 */
		public $description;

		/**
		 * Price, without any sign. Just a float.
		 * @var float
		 */
		public $price;

		/**
		 * Link to the admin edit screen for this ticket in the provider system,
		 * or null if the provider doesn't have any way to edit the ticket.
		 * @var string
		 */
		public $admin_link;

		/**
		 * Link to the front end of this ticket, if the providers has single view
		 * for this ticket.
		 * @var string
		 */
		public $frontend_link;

		/**
		 * Class name of the provider handling this ticket
		 * @var
		 */
		public $provider_class;

		/**
		 * Amount of tickets of this kind in stock
		 * @var int
		 */
		public $stock;

		/**
		 * Amount of tickets of this kind sold
		 * @var int
		 */
		public $qty_sold;

		/**
		 * When the ticket should be put on sale
		 * @var
		 */
		public $start_date;

		/**
		 * When the ticket should be stop being sold
		 * @var
		 */
		public $end_date;

	}
}