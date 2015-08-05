<?php
class Tribe__Events__Asset__Timepicker extends Tribe__Events__Asset__Abstract_Asset {
	/**
	 * Setup our timepicker CSS and JS.
	 *
	 * We are using the Pickadate library by amsul.ca for this.
	 *
	 * @see tribe_events_timepicker()
	 * @see http://amsul.ca/pickadate.js/time
	 */
	public function handle() {
		$handle = $this->prefix . '-timepicker';

		// The pickadate lib doesn't follow the *.min.js convention for compiled assets
		$common_path = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG
			? $this->vendor_url . 'pickadate/lib/'
			: $this->vendor_url . 'pickadate/lib/compressed/';

		$css_base_path = $common_path . 'themes/default.css';
		$css_time_path = $common_path . 'themes/default.time.css';

		wp_enqueue_style( "$handle-theme-base", $css_base_path );
		wp_enqueue_style( "$handle-theme-time", $css_time_path );

		$base_lib = $common_path . 'picker.js';
		$time_lib = $common_path . 'picker.time.js';

		wp_enqueue_script( "$handle-lib", $base_lib, array( 'jquery' ), '3.5.6' );
		wp_enqueue_script( $handle, $time_lib, array( "$handle-lib" ), '3.5.6' );

		Tribe__Events__Template_Factory::add_vendor_script( $handle );
	}
}