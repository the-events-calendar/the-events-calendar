<?php


	class Tribe__Events__Asset__Bootstrap_Datepicker extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$css_path = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'bootstrap-datepicker/css/datepicker.css', true );
			$path = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'bootstrap-datepicker/js/bootstrap-datepicker.min.js', true );
			wp_enqueue_style( $this->prefix . '-bootstrap-datepicker-css', $css_path );

			$handle = $this->prefix . '-bootstrap-datepicker';
			wp_enqueue_script( $handle, $path, 'jquery', '3.2' );
			Tribe__Events__Template_Factory::add_vendor_script( $handle );
			$localized_datepicker_array = array(
				'days' => array_merge( $this->tec->daysOfWeek, array( $this->tec->daysOfWeek[0] ) ),
				'daysShort' => array_merge( $this->tec->daysOfWeekShort, array( $this->tec->daysOfWeekShort[0] ) ),
				'daysMin' => array_merge( $this->tec->daysOfWeekMin, array( $this->tec->daysOfWeekMin[0] ) ),
				'months' => array_values( $this->tec->monthsFull ),
				'monthsShort' => array_values( $this->tec->monthsShort ),
				'clear' => 'Clear',
				'today' => 'Today',
			);
			wp_localize_script( $handle, 'tribe_bootstrap_datepicker_strings', array( 'dates' => $localized_datepicker_array ) );
		}
	}
