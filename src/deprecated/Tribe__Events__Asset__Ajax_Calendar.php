<?php
_deprecated_file( __FILE__, '4.6.21', 'Deprecated class in favor of using `tribe_asset` registration' );

class Tribe__Events__Asset__Ajax_Calendar extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$deps      = array_merge( $this->deps, [
			'jquery',
			$this->prefix . '-bootstrap-datepicker',
			$this->prefix . '-calendar-script',
		] );
		$ajax_data = [ 'ajaxurl' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) ];
		$path      = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'tribe-events-ajax-calendar.js' ), true );

		$handle = 'the-events-calendar';
		wp_enqueue_script( $handle, $path, $deps, $this->filter_js_version(), true );
		wp_localize_script( $handle, 'TribeCalendar', $ajax_data );
	}
}
