<?php
_deprecated_file( __FILE__, '4.2', 'Tribe__Events__Linked_Posts__Chooser_Meta_Box' );

class Tribe__Events__Admin__Organizer_Chooser_Meta_Box extends Tribe__Events__Linked_Posts__Chooser_Meta_Box {
	/**
	 * Render a single row of the organizers table
	 *
	 * @param int $organizer_id
	 *
	 */
	protected function single_organizer_dropdown( $organizer_id ) {
		$this->single_post_dropdown( $organizer_id );
	}

	/**
	 * Render a link to edit the organizer post
	 *
	 * @param int $organizer_id
	 *
	 */
	protected function edit_organizer_link( $organizer_id ) {
		$this->edit_post_link( $organizer_id );
	}

	/**
	 * Determine if the event can use the default organizer setting
	 *
	 * @param array $current_organizers
	 *
	 * @return bool
	 */
	protected function use_default_organizer( $current_organizers ) {
		return $this->use_default_post( $current_organizers );
	}

	/**
	 * Renders the "Add Another Organizer" button
	 *
	 */
	protected function render_add_organizer_button() {
		$this->render_add_post_button();
	}
}
