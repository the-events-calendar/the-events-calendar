<?php
if ( !class_exists( 'TribeEventsTicketObject' ) ) {
	class TribeEventsTicketObject {

		/**
		 * @var
		 */
		public $ID;
		/**
		 * @var string
		 */
		public $name;

		/**
		 * @var string
		 */
		public $description;

		/**
		 * @var float
		 */
		public $price;
		/**
		 * @var string
		 */
		public $admin_link;
		/**
		 * @var string
		 */
		public $frontend_link;

		/**
		 * @var
		 */
		public $provider_class;

		/**
		 * @var int
		 */
		public $stock;
		/**
		 * @var int
		 */
		public $qty_sold;

		/**
		 * @var
		 */
		public $start_date;

		/**
		 * @var
		 */
		public $end_date;

	}
}