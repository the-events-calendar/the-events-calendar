<?php
/**
 * Handles the registration of link modifications.
 *
 * @since   6.0.11
 * @since TBD Migrated to The Events Calendar from Events Calendar Pro.
 *
 * @package TEC\Events\Custom_Tables\V1\Links
 */

namespace TEC\Events\Custom_Tables\V1\Links;

use TEC\Common\Contracts\Service_Provider;
use WP_Post;

/**
 * Class Provider.
 *
 * @since   6.0.11
 *
 * @package TEC\Events\Custom_Tables\V1\Links;
 */
class Provider extends Service_Provider {

	/**
	 * Registers the implementations and filters required to integrate Custom Tables v1 with Events' links.
	 *
	 * @since 6.0.11
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( __CLASS__, $this );
		$this->container->singleton( Links::class, Links::class );

		if ( ! has_filter( "tec_events_pro_recurring_event_permalink_sequence_number", [
			$this,
			'filter_recurring_event_sequence_number'
		] ) ) {
			add_filter( "tec_events_pro_recurring_event_permalink_sequence_number", [
				$this,
				'filter_recurring_event_sequence_number'
			], 10, 2 );
		}

		// The following filters are used to filter the edit and permalinks for recurring Events in Admin and non-Admin context.
		if ( ! has_filter( 'get_edit_post_link', [ $this, 'update_event_edit_link' ] ) ) {
			add_filter( 'get_edit_post_link', [ $this, 'update_event_edit_link' ], 10, 2 );
		}

		if ( ! has_filter( 'post_type_link', [ $this, 'update_recurrence_view_link' ] ) ) {
			// Use priority 20 to run after PRO.
			add_filter( 'post_type_link', [ $this, 'update_recurrence_view_link' ], 20, 4 );
		}
	}

	/**
	 * Unregister hooks.
	 */
	public function unregister() {
		remove_filter( "tec_events_pro_recurring_event_permalink_sequence_number", [
			$this,
			'filter_recurring_event_sequence_number'
		] );
		remove_filter( 'get_edit_post_link', [ $this, 'update_event_edit_link' ], );
		remove_filter( 'post_type_link', [ $this, 'update_recurrence_view_link' ], 20 );
	}

	/**
	 * Update the edit link for an event to open the next available occurrence when editing a recurring event, for
	 * normal events just keep on using the default edit link.
	 *
	 * @since 6.0.0
	 * @since 6.0.11 Looser type params and some validation on this public hook.
	 *
	 * @param null|string $link    The edit link.
	 * @param numeric     $post_id Post ID.
	 *
	 * @return string|null The edit post link for the given post. Null if the post type does not exist or does not
	 *                     allow an editing UI.
	 */
	public function update_event_edit_link( $link, $post_id ): ?string {
		if ( ! ( is_string( $link ) && is_numeric( $post_id ) ) ) {
			return $link;
		}

		return $this->container->make( Links::class )->update_event_edit_link( $link, (int) $post_id );
	}

	/**
	 * Updates the link to view an Occurrence in the context of the Administration UI.
	 *
	 * @since 6.0.0
	 *
	 * @param string  $post_link   The View post link, as produced by WordPress and previous filters.
	 * @param WP_Post $post        A reference to the Post instance.
	 * @param bool    $leavename   Whether to leave the post name in the link or not.
	 * @param bool    $sample      Whether the link is being produced for the purpose of providing a sample of
	 *                             the view link, or not.
	 *
	 * @return string The updated view link, if required.
	 */
	public function update_recurrence_view_link( $post_link, $post, $leavename, $sample ) {
		if ( ! $post instanceof WP_Post ) {
			return $post_link;
		}

		return $this->container->make( Links::class )
		                       ->update_recurrence_view_link( $post_link, $post, $leavename, $sample );
	}

	/**
	 * Filter the `eventSequence` number in some scenarios.
	 *
	 * @since 6.0.11
	 *
	 * @param mixed   $sequence_number The initial number or null if none defined.
	 * @param WP_Post $post            The event post object.
	 *
	 * @return mixed The resolved occurrence ID or original sequence number.
	 */
	public function filter_recurring_event_sequence_number( $sequence_number, WP_Post $post ) {
		return $this->container->make( Event_Links::class )
		                       ->filter_recurring_event_sequence_number( $sequence_number, $post );
	}
}
