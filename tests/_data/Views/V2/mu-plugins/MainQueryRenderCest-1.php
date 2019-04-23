<?php
/**
 * Plugin Name: Main Query Render 1.
 */

// Define the test view class when we're sure the View class parent will be loaded.
add_action( 'plugins_loaded', static function () {
	class Test_View_2 extends Tribe\Events\Views\V2\View {
		public function get_html() {
			$events = tribe_events()->all();

			return $this->template->render( [ 'events' => $events ] );
		}
	}
} );

// Register the view for the `test` slug.
add_filter( 'tribe_events_views', static function ( array $views ) {
	$views ['main-query-render-1'] = 'Test_View_2';

	return $views;
} );

