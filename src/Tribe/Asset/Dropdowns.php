<?php
class Tribe__Events__Asset__Dropdowns extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$deps = array_merge(
			$this->deps,
			array(
				'jquery',
				'tribe-events-select2',
			)
		);

		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'tribe-dropdowns.js' ), true );

		wp_enqueue_script( $this->prefix . '-dropdowns', $path, $deps, $this->filter_js_version(), true );

		$ajax_data = array( 'ajaxurl' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) );
		wp_localize_script( $this->prefix . '-dropdowns', 'tribe_dropdowns', $ajax_data );
	}
}