<?php
/**
 * Handles the rendering of the event status classic editor metabox.
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status
 */

namespace Tribe\Events\Event_Status;

use Tribe__Context as Context;
use Tribe__Events__Main as Events_Plugin;
use Tribe__Utils__Array as Arr;
use WP_Post;

/**
 * Class Classic_Editor
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status
 */
class Classic_Editor {

	/**
	 * ID for the Classic_Editor in WP.
	 *
	 * @since 5.11.0
	 *
	 * @var string
	 */
	public static $id = 'tribe-events-status';

	/**
	 * Action name used for the nonce on saving the metabox.
	 *
	 * @since 5.11.0
	 *
	 * @var string
	 */
	public static $nonce_action = 'tribe-events-status-nonce';

	/**
	 * Stores the template class used.
	 *
	 * @since 5.11.0
	 *
	 * @var Admin_Template
	 */
	protected $admin_template;

	/**
	 * Metabox constructor.
	 *
	 * @since 5.11.0
	 *
	 * @param Admin_Template $admin_template An instance of the plugin template handler.
	 * @param Context|null   $context        The instance of the Context the metabox should use
	 *                                       or `null` to use the global one.
	 */
	public function __construct( Admin_Template $admin_template, Context $context = null ) {
		$this->context        = null !== $context ? $context : tribe_context();
		$this->admin_template = $admin_template;
	}

	/**
	 * Fetches the Metabox title.
	 *
	 * @since 5.11.0
	 *
	 * @return string The translated metabox title for Event Status.
	 */
	public function get_title() {
		return esc_html_x( 'Events Status', 'Meta box title for the Event Status', 'the-events-calendar' );
	}

	/**
	 * Render the metabox contents.
	 *
	 * @since 5.11.0
	 *
	 * @param WP_Post $post Which post we are using here.
	 *
	 * @return string The metabox template for event status or an empty string if not an event.
	 */
	public function render( $post ) {
		$event = tribe_get_event( $post );

		if ( ! $event instanceof WP_Post ) {
			return '';
		}

		return $this->admin_template->template(
			'metabox/event-status',
			[
				'metabox' => $this,
				'event'   => $event,
			],
			true
		);
	}

	/**
	 * Register the metabox in WP system.
	 *
	 * @since 5.11.0
	 */
	public function register_metabox() {
		add_meta_box(
			static::$id,
			$this->get_title(),
			[ $this, 'render' ],
			Events_Plugin::POSTTYPE,
			'side',
			'default'
		);
	}

	/**
	 * Register all the fields in the Rest API for event status.
	 *
	 * @since 5.11.0
	 */
	public function register_fields() {
		foreach ( Event_Meta::$event_status_keys as $key ) {
			register_post_meta(
				'tribe_events',
				$key,
				[
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'string',
					'auth_callback' => static function() {
						return current_user_can( 'edit_posts' );
					},
				]
			);
		}
	}

	/**
	 * Saves the metabox, which will be triggered in `save_post`.
	 *
	 * @since 5.11.0
	 *
	 * @param int     $post_id Which post ID we are dealing with when saving.
	 * @param WP_Post $post    WP Post instance we are saving.
	 * @param boolean $update  If we are updating the post or not.
	 */
	public function save( $post_id, $post, $update ) {
		// Skip non-events.
		if ( ! tribe_is_event( $post_id ) ) {
			return;
		}

		// All fields will be stored in the same array for simplicity.
		$data = $this->context->get( 'events_status_data', [] );

		// Add nonce for security and authentication.
		$nonce_name = Arr::get( $data, 'nonce', false );

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, static::$nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_tribe_events', $post_id ) ) {
			return;
		}

		if ( tribe_context()->is( 'bulk_edit' ) ) {
			return;
		}

		if ( tribe_context()->is( 'inline_save' ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		/**
		 * Fires before the Metabox saved the data from the current request.
		 *
		 * @since 5.11.0
		 *
		 * @param int $post_id The post ID of the event currently being saved.
		 * @param array<string,mixed> The whole data received by the metabox.
		 */
		do_action( 'tribe_events_event_status_before_metabox_save', $post_id, $data );

		$status = Arr::get( $data, 'status' );
		if ( 'scheduled' !== $status ) {
			$this->update_fields( $post_id, $data );
		} else {
			$this->delete_fields( $post_id, $data );
		}

		/**
		 * Fires after the Metabox saved the data from the current request.
		 *
		 * @since 5.11.0
		 *
		 * @param int $post_id The post ID of the event currently being saved.
		 * @param array<string,mixed> The whole data received by the metabox.
		 */
		do_action( 'tribe_events_event_status_after_metabox_save', $post_id, $data );
	}

	/**
	 * Update event status meta fields.
	 *
	 * @since 5.11.0
	 *
	 * @param int   $post_id Which post ID we are dealing with when saving.
	 * @param array $data    An array of meta field values.
	 */
	public function update_fields( $post_id, $data ) {
		update_post_meta( $post_id, Event_Meta::$key_status, Arr::get( $data, 'status', false ) );
		update_post_meta( $post_id, Event_Meta::$key_status_reason, Arr::get( $data, 'status-reason', false ) );

		/**
		 * Allows extensions and compatibilities to save their associated meta.
		 *
		 * @since 5.11.0
		 *
		 * @param int   $post_id ID of the post we're saving.
		 * @param array $data    The meta data we're trying to save.
		 */
		do_action( 'tribe_events_event_status_update_post_meta', $post_id, $data );
	}

	/**
	 * Delete event status meta fields.
	 *
	 * @since 5.11.0
	 *
	 * @param int   $post_id Which post ID we are dealing with when saving.
	 * @param array $data    An array of meta field values.
	 */
	public function delete_fields( $post_id, $data ) {
		foreach ( Event_Meta::$event_status_keys as $key ) {
			delete_post_meta( $post_id, $key );
		}
	}
}