<?php


class Tribe__Events__Pro__Updater extends Tribe__Events__Updater {
	protected $version_option = 'pro-schema-version';
	protected $reset_version = '3.9';


	/**
	 * Returns an array of callbacks with version strings as keys.
	 * Any key higher than the version recorded in the DB
	 * and lower than $this->current_version will have its
	 * callback called.
	 *
	 * @return array
	 */
	public function get_update_callbacks() {
		return array(
			'3.5' => array( $this, 'recurring_events_from_meta_to_child_posts' ),
		);
	}

	/**
	 * Returns an array of callbacks that should be called
	 * every time the version is updated
	 *
	 * @return array
	 */
	public function get_constant_update_callbacks() {
		return array(
			array( $this, 'flush_rewrites' ),
		);
	}

	public function recurring_events_from_meta_to_child_posts() {
		$converter = new Tribe__Events__Pro__Updates__Recurrence_Meta_To_Child_Post_Converter();
		$converter->do_conversion();
	}
}
