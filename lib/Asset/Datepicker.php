<?php


	class Tribe__Events__Asset__Datepicker extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-datepicker' );
			Tribe_Template_Factory::add_vendor_script( 'jquery-ui-datepicker' );
		}
	}