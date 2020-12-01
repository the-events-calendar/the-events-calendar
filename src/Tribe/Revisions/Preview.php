<?php


/**
 * Class Tribe__Events__Revisions__Preview
 */
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

	/**
	 * Conditionally hooks the filters needed to fetch a revision meta data.
	 */
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

			add_filter( 'get_post_metadata', [ $this, 'intercept_post_metadata' ], 50, 4 );
		}
	}

	/**
	 * Intercepts a meta value request for a revision
	 *
	 * Returns the revision associated meta if present or the original event meta otherwise.
	 *
	 * @param mixed $original_value
	 * @param int $object_id
	 * @param string $meta_key
	 * @param bool $single
	 *
	 * @return mixed
	 */
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

	/**
	 * @return bool
	 */
	protected function is_previewing() {
		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return;
		}

		return $wp_query->is_preview() && tribe_is_event( $wp_query->post ) || ! empty( $_GET['preview_id'] ) && is_numeric( $_GET['preview_id'] ) && ! empty( $_GET['preview'] ) && tribe_is_event( $_GET['preview_id'] );
	}

	/**
	 * @return int
	 */
	protected function get_event_id() {
		if ( $this->is_previewing() ) {

			if ( ! $wp_query = tribe_get_global_query_object() ) {
				return;
			}

			return $wp_query->is_preview() && tribe_is_event( $wp_query->post ) ? $wp_query->post->ID : $_GET['preview_id'];
		} else if ( $this->is_saving_preview() ) {
			return $_POST['post_ID'];
		}

		return 0;
	}
}
