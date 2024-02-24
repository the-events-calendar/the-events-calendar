<?php

use Tribe__Events__Venue as Venue;

/**
 * Class Tribe__Events__Meta__Save
 *
 * Conditionally saves an event meta to the database.
 *
 * @since 4.2.5
 */
class Tribe__Events__Meta__Save {

	/**
	 * @var int
	 */
	protected $post_id;
	/**
	 * @var WP_Post
	 */
	protected $post;
	/**
	 * @var Tribe__Events__Meta__Context
	 */
	protected $context;

	/**
	 * Tribe__Events__Meta__Save constructor.
	 *
	 * @param                                   $post_id
	 * @param WP_Post                           $post
	 * @param Tribe__Events__Meta__Context|null $context
	 */
	public function __construct( $post_id, WP_Post $post, Tribe__Events__Meta__Context $context = null ) {
		$this->post_id = $post_id;
		$this->post    = $post;
		$this->context = $context ? $context : new Tribe__Events__Meta__Context();
	}

	/**
	 * ensure only one venue or organizer is created during post preview
	 * subsequent previews will reuse that same post
	 *
	 * ensure that preview post is the one that's used when the event is published,
	 * unless we're publishing with a saved venue
	 *
	 * @param string $post_type Can be 'venue' or 'organizer'
	 */
	protected function manage_preview_metapost( $post_type, $event_id ) {

		if ( ! in_array( $post_type, [ 'venue', 'organizer' ] ) ) {
			return;
		}

		$posttype        = ucfirst( $post_type );
		$posttype_id     = $posttype . 'ID';
		$meta_key        = '_preview_' . $post_type . '_id';
		$valid_post_id   = "tribe_get_{$post_type}_id";
		$create          = "create$posttype";
		$preview_post_id = get_post_meta( $event_id, $meta_key, true );
		$doing_preview = isset( $_REQUEST['wp-preview'] ) && ( $_REQUEST['wp-preview'] == 'dopreview' );

		if ( empty( $_POST[ $posttype ][ $posttype_id ] ) ) {
			// the event is set to use a new metapost
			if ( $doing_preview ) {
				// we're previewing
				if ( $preview_post_id && $preview_post_id == $valid_post_id( $preview_post_id ) ) {
					// a preview post has been created and is valid, update that
					wp_update_post(
						[
							'ID'         => $preview_post_id,
							'post_title' => $_POST[ $posttype ][ $posttype ],
						]
					);
				} else {
					// a preview post has not been created yet, or is not valid - create one and save the ID
					$preview_post_id = Tribe__Events__API::$create( $_POST[ $posttype ], 'draft' );
					update_post_meta( $event_id, $meta_key, $preview_post_id );
				}
			}

			if ( $preview_post_id ) {
				// set the preview post id as the event metapost id in the $_POST array
				// so Tribe__Events__API::saveEventVenue() doesn't make a new post
				$_POST[ $posttype ][ $posttype_id ] = (int) $preview_post_id;
			}
		} else {
			// we're using a saved metapost, discard any preview post
			if ( $preview_post_id ) {
				wp_delete_post( $preview_post_id );
				global $wpdb;
				$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE `meta_key` = '$meta_key' AND `meta_value` = $preview_post_id" );
			}
		}
	}

	/**
	 * Save the meta for the event if the user has the capability to.
	 *
	 * @return bool `true` if event meta was updated, `false` otherwise.
	 */
	public function save() {
		// Save only the meta that does not have blocks when the Gutenberg editor is present.
		if ( tribe( 'editor' )->should_load_blocks() && has_blocks( $this->post_id ) ) {
			return $this->save_block_editor_metadata( $this->post_id, $_POST, $this->post );
		}

		if ( ! $this->context->has_nonce() ) {
			return false;
		}

		if ( ! $this->context->verify_nonce() ) {
			return false;
		}

		if ( ! $this->context->current_user_can_edit_events() ) {
			return false;
		}

		$_POST['Organizer'] = isset( $_POST['organizer'] ) ? stripslashes_deep( $_POST['organizer'] ) : null;
		$_POST['Venue']     = isset( $_POST['venue'] ) ? stripslashes_deep( $_POST['venue'] ) : null;

		/**
		 * handle previewed venues and organizers
		 */
		$this->manage_preview_metapost( 'venue', $this->post_id );
		$this->manage_preview_metapost( 'organizer', $this->post_id );
		Tribe__Events__API::saveEventMeta( $this->post_id, $_POST, $this->post );

		return true;
	}

	/**
	 * Conditionally save the meta.
	 *
	 * Will save if the context is the expected one; will call `save` method.
	 *
	 * @return bool `true` if event meta was updated, `false` otherwise.
	 */
	public function maybe_save() {
		// only continue if it's an event post
		if ( ! $this->is_event() ) {
			return false;
		}

		if ( $this->context->doing_ajax() ) {
			return false;
		}

		// don't do anything on autosave or auto-draft either or massupdates
		if ( $this->is_autosave() || $this->is_auto_draft() || $this->context->is_bulk_editing() || $this->context->is_inline_save() ) {
			return false;
		}

		// don't do anything on other wp_insert_post calls
		if ( $this->is_auxiliary_save() ) {
			return false;
		}

		return $this->save();
	}

	/**
	 * @return bool
	 */
	protected function is_auxiliary_save() {
		return isset( $_POST['post_ID'] ) && $this->post_id != $_POST['post_ID'];
	}

	/**
	 * @return false|int
	 */
	protected function is_autosave() {
		return wp_is_post_autosave( $this->post_id );
	}

	/**
	 * @return bool
	 */
	protected function is_auto_draft() {
		return $this->post->post_status == 'auto-draft';
	}

	/**
	 * @return bool
	 */
	protected function is_event() {
		return $this->post->post_type === Tribe__Events__Main::POSTTYPE;
	}

	/**
	 * Used to save the event meta for events created in the block editor
	 *
	 * @param int     $event_id The event ID we are modifying meta for.
	 * @param array   $data     The post data
	 * @param WP_Post $event    The event post, itself.
	 *
	 * @return bool
	 */
	public function save_block_editor_metadata( $event_id, $data, $event = null ) {
		if ( ! isset( $_GET['meta-box-loader-nonce'] ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( $_GET['meta-box-loader-nonce'], 'meta-box-loader' ) ) {
			return false;
		}

		if ( ! $this->context->current_user_can_edit_events() ) {
			return false;
		}

		if ( empty( $data['EventHideFromUpcoming'] ) ) {
			delete_metadata( 'post', $event_id, '_EventHideFromUpcoming' );
		} else {
			update_metadata( 'post', $event_id, '_EventHideFromUpcoming', $data['EventHideFromUpcoming'] );
		}

		// Set sticky state for calendar view.
		if ( $event instanceof WP_Post ) {
			$show_in_cal = Tribe__Utils__Array::get( $data, [ 'EventShowInCalendar' ], false );
			if (
				$show_in_cal
				&& tribe_is_truthy( $show_in_cal )
				&& $event->menu_order != '-1'
			) {
				$update_event = [
					'ID'         => $event_id,
					'menu_order' => '-1',
				];
				wp_update_post( $update_event );
			} elseif (
				(
					! $show_in_cal
					|| ! tribe_is_truthy( $show_in_cal )
				)
				&& $event->menu_order == '-1'
			) {
				$update_event = [
					'ID'         => $event_id,
					'menu_order' => '0',
				];
				wp_update_post( $update_event );
			}
		}

		// When we save a block event, we need to make sure that the venue order is updated based on the venues in the post.
		$linked_posts_object = tribe( 'tec.linked-posts' );
		$venue_meta_key      = $linked_posts_object->get_meta_key( Venue::POSTTYPE );
		$current_venue_order = get_post_meta( $event_id, $venue_meta_key, false );
		$current_venue_order = array_map( 'absint', $current_venue_order );
		$new_venue_order     = $linked_posts_object->maybe_get_new_order_from_blocks( $event_id, Venue::POSTTYPE, $current_venue_order );

		$linked_posts_object->maybe_reorder_linked_posts_ids( $event_id, Venue::POSTTYPE, $new_venue_order, $current_venue_order );

		// Set featured status
		empty( $data['feature_event'] )
			? tribe( 'tec.featured_events' )->unfeature( $event_id )
			: tribe( 'tec.featured_events' )->feature( $event_id );

		return true;
	}

}
