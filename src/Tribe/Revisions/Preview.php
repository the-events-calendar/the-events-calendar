<?php


class Tribe__Events__Revisions__Preview {

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * @var int
	 */
	protected $event_id;

	/**
	 * @var WP_Post
	 */
	protected $latest_revision;

	/**
	 * The class singleton constructor.
	 *
	 * @return Tribe__Events__Revisions__Preview
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function hook() {
		$is_event_revision = $this->is_previewing() || $this->is_saving_preview();

		if ( $is_event_revision ) {
			$this->event_id = $this->get_event_id();
			if ( empty( $this->event_id ) ) {
				return;
			}

			$revisions = wp_get_post_revisions( $this->event_id );

			if ( empty( $revisions ) ) {
				return;
			}

			$this->latest_revision = reset( $revisions );

			add_filter( 'get_post_metadata', array( $this, 'intercept_post_metadata' ), 50, 4 );
		}
	}

	public function intercept_post_metadata( $original_value, $object_id, $meta_key, $single ) {
		if ( $object_id != $this->event_id ) {
			return $original_value;
		}

		$revision_meta_value = get_metadata( 'post', $this->latest_revision->ID, $meta_key, $single );

		return empty( $revision_meta_value ) ? $original_value : $revision_meta_value;
	}

	/**
	 * @return bool
	 */
	protected function is_saving_preview() {
		return ! empty( $_POST['wp-preview'] ) && $_POST['wp-preview'] === 'dopreview' && ! empty( $_POST['post_type'] ) && ! empty( $_POST['post_ID'] ) && $_POST['post_type'] === Tribe__Events__Main::POSTTYPE;
	}

	protected function is_previewing() {
		global $wp_query;

		return $wp_query->is_preview() && tribe_is_event( $wp_query->post ) || ! empty( $_GET['preview_id'] ) && is_numeric( $_GET['preview_id'] ) && ! empty( $_GET['preview'] ) && tribe_is_event( $_GET['preview_id'] );
	}

	protected function get_event_id() {
		if ( $this->is_previewing() ) {
			global $wp_query;

			return $wp_query->is_preview() && tribe_is_event( $wp_query->post ) ? $wp_query->post->ID : $_GET['preview_id'];
		} else if ( $this->is_saving_preview() ) {
			return $_POST['post_ID'];
		}

		return 0;
	}
}