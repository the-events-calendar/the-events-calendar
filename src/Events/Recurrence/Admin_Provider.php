<?php
/**
 * Registers the admin authoring surface of the Recurrence feature.
 *
 * The Event Dates metabox lets an editor author the additional, explicit dates of an
 * Event one by one; rule-based recurrence stays an Events Calendar Pro feature. The
 * metabox is a plain WordPress one, so it renders both in the Classic Editor and in
 * the Block Editor metaboxes area.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use TEC\Common\Contracts\Service_Provider;
use Tribe__Events__Main as TEC;
use WP_Post;

/**
 * Class Admin_Provider.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Admin_Provider extends Service_Provider {
	/**
	 * The name of the posted dates field.
	 *
	 * @since TBD
	 */
	public const FIELD = 'tec_events_recurrence_dates';

	/**
	 * The nonce action of the metabox.
	 *
	 * @since TBD
	 */
	public const NONCE_ACTION = 'tec_events_recurrence_dates_save';

	/**
	 * Registers the metabox and its save handler.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( self::class, $this );

		if ( class_exists( 'Tribe__Events__Pro__Main', false ) ) {
			// Events Calendar Pro provides the full recurrence UI.
			return;
		}

		if ( ! has_action( 'add_meta_boxes', [ $this, 'register_metabox' ] ) ) {
			add_action( 'add_meta_boxes', [ $this, 'register_metabox' ] );
		}

		if ( ! has_action( 'save_post_' . TEC::POSTTYPE, [ $this, 'save_dates' ] ) ) {
			add_action( 'save_post_' . TEC::POSTTYPE, [ $this, 'save_dates' ], 20, 2 );
		}
	}

	/**
	 * Unregisters the hooks managed by the provider.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'add_meta_boxes', [ $this, 'register_metabox' ] );
		remove_action( 'save_post_' . TEC::POSTTYPE, [ $this, 'save_dates' ], 20 );
	}

	/**
	 * Registers the Event Dates metabox on the Event edit screen.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_metabox(): void {
		add_meta_box(
			'tec-events-recurrence-dates',
			__( 'Event Dates', 'the-events-calendar' ),
			[ $this, 'render_metabox' ],
			TEC::POSTTYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Renders the Event Dates metabox.
	 *
	 * @since TBD
	 *
	 * @param WP_Post $post The Event post being edited.
	 *
	 * @return void
	 */
	public function render_metabox( WP_Post $post ): void {
		$dates = $this->container->make( Dates_Service::class )->get_dates( $post->ID );

		// The first Occurrence is the Event date itself; the metabox authors the additional ones.
		$additional = array_slice( $dates, 1 );

		$recurrence_meta = get_post_meta( $post->ID, '_EventRecurrence', true );
		$has_rules       = ! empty( $recurrence_meta ) && ! Date_Rules::is_dates_only_meta( $recurrence_meta );

		include TEC::instance()->pluginPath . 'src/admin-views/recurrence/event-dates.php';
	}

	/**
	 * Saves the additional dates posted from the metabox.
	 *
	 * @since TBD
	 *
	 * @param int     $post_id The Event post ID.
	 * @param WP_Post $post    The Event post being saved.
	 *
	 * @return void
	 */
	public function save_dates( $post_id, $post ): void {
		$nonce = tribe_get_request_var( self::NONCE_ACTION . '_nonce' );

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			// The metabox was not rendered on this save.
			return;
		}

		if (
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| wp_is_post_revision( $post_id )
			|| wp_is_post_autosave( $post_id )
			|| ! current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		$recurrence_meta = get_post_meta( $post_id, '_EventRecurrence', true );

		if ( ! empty( $recurrence_meta ) && ! Date_Rules::is_dates_only_meta( $recurrence_meta ) ) {
			// Rule-based recurrence is not authored here: leave the Pro data untouched.
			return;
		}

		$rows  = tribe_get_request_var( self::FIELD, [] );
		$dates = [];

		foreach ( (array) $rows as $row ) {
			if ( ! is_array( $row ) || empty( $row['date'] ) || empty( $row['start'] ) || empty( $row['end'] ) ) {
				continue;
			}

			$date  = sanitize_text_field( (string) $row['date'] );
			$start = sanitize_text_field( (string) $row['start'] );
			$end   = sanitize_text_field( (string) $row['end'] );

			if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) || ! preg_match( '/^\d{2}:\d{2}$/', $start ) || ! preg_match( '/^\d{2}:\d{2}$/', $end ) ) {
				continue;
			}

			if ( $end <= $start ) {
				// Same-day authoring only: an end before the start would author a negative duration.
				continue;
			}

			$dates[] = [
				'start' => "{$date} {$start}:00",
				'end'   => "{$date} {$end}:00",
			];
		}

		$service = $this->container->make( Dates_Service::class );

		if ( count( $dates ) ) {
			$service->set_dates( (int) $post_id, $dates );
		} else {
			$service->remove_dates( (int) $post_id );
		}
	}
}
