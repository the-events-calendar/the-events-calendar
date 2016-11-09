<?php
/**
 * Enqueues assets required for functionality specific to the event post editor.
 */
class Tribe__Events__Asset__Event_Editor extends Tribe__Events__Asset__Abstract_Asset {
	public function handle() {
		$deps = array_merge(
			$this->deps,
			array(
				'jquery',
			)
		);

		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'event-editor.js' ), true );
		wp_enqueue_script( $this->prefix . '-event-editor', $path, $deps, $this->filter_js_version(), true );
	}
}