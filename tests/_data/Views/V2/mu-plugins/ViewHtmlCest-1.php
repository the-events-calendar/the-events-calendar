<?php
add_action( 'plugins_loaded', static function () {
	class Test_View_1 extends Tribe\Events\Views\V2\View {
		public function get_html() {
			return '<p>Test View 1 HTML output</p>';
		}
	}
} );

add_filter( 'tribe_events_views', static function ( array $views ) {
	$views ['test'] = 'Test_View_1';

	return $views;
} );
