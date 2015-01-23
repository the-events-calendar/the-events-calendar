<?php


	class Tribe__Events__Asset__Bootstrap_Datepicker extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$css_path = Tribe_Template_Factory::getMinFile( $this->vendor_url . 'bootstrap-datepicker/css/datepicker.css', true );
			$path = Tribe_Template_Factory::getMinFile( $this->vendor_url . 'bootstrap-datepicker/js/bootstrap-datepicker.js', true );
			wp_enqueue_style( $this->prefix . '-bootstrap-datepicker-css', $css_path );
			wp_enqueue_script( $this->prefix . '-bootstrap-datepicker', $path, 'jquery', '3.2' );
			Tribe_Template_Factory::add_vendor_script($this->prefix . '-bootstrap-datepicker');
			$localized_datepicker_array = array(
				'days' => array_merge( $this->tec->daysOfWeek, array( $this->tec->daysOfWeek[0] ) ),
				'daysShort' => array_merge( $this->tec->daysOfWeekShort, array( $this->tec->daysOfWeekShort[0] ) ),
				'daysMin' => array_merge( $this->tec->daysOfWeekMin, array( $this->tec->daysOfWeekMin[0] ) ),
				'months' => array_values( $this->tec->monthsFull ), 'monthsShort' => array_values( $this->tec->monthsShort ),
				'clear' => 'Clear', 'today' => 'Today',
			);
			wp_localize_script( $this->prefix . '-bootstrap-datepicker', 'tribe_bootstrap_datepicker_strings', array( 'dates' => $localized_datepicker_array ) );
		}
	}