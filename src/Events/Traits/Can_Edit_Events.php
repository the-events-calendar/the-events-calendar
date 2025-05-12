<?php
/**
 * Can_Edit_Events trait.
 *
 * @since 6.12.0
 */

declare( strict_types=1 );

namespace TEC\Events\Traits;

use Tribe__Events__Main as Events;
use WP_Post_Type;
use WP_User;

/**
 * Trait Can_Edit_Events
 *
 * @since 6.12.0
 */
trait Can_Edit_Events {

	/**
	 * Check if the current user can edit events.
	 *
	 * @since 6.12.0
	 *
	 * @return bool
	 */
	protected function current_user_can_edit_events(): bool {
		$current_user = wp_get_current_user();
		if ( ! $current_user instanceof WP_User ) {
			return false;
		}

		return $this->user_can_edit_events( $current_user->ID );
	}

	/**
	 * Check if the user can edit events.
	 *
	 * @since 6.12.0
	 *
	 * @param int $user_id The user to check.
	 *
	 * @return bool
	 */
	protected function user_can_edit_events( int $user_id ): bool {
		$event_post_type = get_post_type_object( Events::POSTTYPE );
		if ( ! $event_post_type instanceof WP_Post_Type ) {
			return false;
		}

		return user_can( $user_id, $event_post_type->cap->edit_posts );
	}
}
