<?php


	class Tribe__Events__Asset__Datepicker extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$handle = 'jquery-ui-datepicker';
			wp_enqueue_script( $handle );
			wp_enqueue_style( $handle );
			Tribe__Events__Template_Factory::add_vendor_script( $handle );
		}
	}