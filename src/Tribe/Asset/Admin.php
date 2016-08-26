<?php

class Tribe__Events__Asset__Admin extends Tribe__Events__Asset__Abstract_Asset {
	public function handle() {
		$deps = array_merge(
			$this->deps,
			array(
				'jquery',
				'jquery-ui-datepicker',
				'jquery-ui-sortable',
				'tribe-bumpdown',
				'wp-util',
			)
		);

		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'events-admin.js' ), true );

		wp_enqueue_script( $this->prefix . '-admin', $path, $deps, $this->filter_js_version(), true );

		$localize = array();

		if ( ! empty( $_GET['post'] ) && Tribe__Events__Ignored_Events::instance()->can_ignore( $_GET['post'] ) ) {
			$post = get_post( $_GET['post'] );
			if ( 'tribe-ignored' === $post->post_status ) {
				$localize['ignored_events'] = array(
					'link_text' => esc_html__( 'Delete Permanently', 'the-events-calendar' ),
					'link_title' => esc_attr__( 'Ignored events that are deleted will be removed permanently. They can be recreated via import.', 'the-events-calendar' ),
					'link_nonce' => wp_create_nonce( 'delete-post_' . $post->ID ),
					'link_post' => $post->ID,
					'link_status' => esc_html__( 'Ignored', 'the-events-calendar' ),
				);
			} else {
				$localize['ignored_events'] = array(
					'link_text' => esc_html__( 'Hide & Ignore', 'the-events-calendar' ),
					'link_title' => esc_attr__( 'Ignored events do not show on the calendar but can be updated with future imports', 'the-events-calendar' ),
				);
			}
		}

		wp_localize_script( $this->prefix . '-admin', 'tribe_events_admin', $localize );
	}
}
