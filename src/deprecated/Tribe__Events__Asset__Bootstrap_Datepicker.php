<?php
_deprecated_file( __FILE__, '4.6.21', 'Deprecated class in favor of using `tribe_asset` registration' );

class Tribe__Events__Asset__Bootstrap_Datepicker extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$css_path = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'bootstrap-datepicker/css/bootstrap-datepicker.standalone.css', true );
		$path = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'bootstrap-datepicker/js/bootstrap-datepicker.min.js', true );
		wp_enqueue_style( $this->prefix . '-bootstrap-datepicker-css', $css_path );

		$months_full     = Tribe__Date_Utils::get_localized_months_full();
		$months_short    = Tribe__Date_Utils::get_localized_months_short();
		$days_week       = Tribe__Date_Utils::get_localized_weekdays_full();
		$days_week_short = Tribe__Date_Utils::get_localized_months_short();
		$days_week_min   = Tribe__Date_Utils::get_localized_weekdays_initial();

		$handle = $this->prefix . '-bootstrap-datepicker';
		wp_enqueue_script( $handle, $path, [ 'jquery' ], '3.2' );
		Tribe__Events__Template_Factory::add_vendor_script( $handle );

		$localized_datepicker_array = [
			'days'        => $days_week,
			'daysShort'   => $days_week_short,
			'daysMin'     => $days_week_min,
			'months'      => array_values( $months_full ),
			'monthsShort' => array_values( $months_short ),
			'clear'       => esc_attr__( 'Clear', 'the-events-calendar' ),
			'today'       => esc_attr__( 'Today', 'the-events-calendar' ),
			'titleFormat' => esc_attr( 'MM yyyy' ),
			'firstDay'    => get_option( 'start_of_week' ),
		];
		wp_localize_script( $handle, 'tribe_bootstrap_datepicker_strings', [ 'dates' => $localized_datepicker_array ] );
	}
}
