<?php
/**
 * Plugin Name: Test List View
 */

// Define the test view class when we're sure the View class parent will be loaded.
add_action( 'plugins_loaded', static function () {
	class Test_List_View extends Tribe\Events\Views\V2\View {
		public function get_html() {
			return $this->template->render();
		}
	}
} );

// Register the view for the `test-list` slug.
add_filter( 'tribe_events_views', static function ( array $views ) {
	$views ['test-list'] = 'Test_List_View';

	return $views;
} );
