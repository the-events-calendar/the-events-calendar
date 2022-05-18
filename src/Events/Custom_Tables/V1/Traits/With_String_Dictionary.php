<?php
/**
 * Provides methods to interact with the string dictionary.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Traits;
 */

namespace TEC\Events\Custom_Tables\V1\Traits;

/**
 * Trait With_String_Dictionary.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Traits;
 */
trait With_String_Dictionary {
	/**
	 * Returns the markup for the event edit link.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return string The HTML markup with the event link.
	 */
	private function get_event_link_markup( $post_id ) {
		$post = get_post( $post_id );

		return '<a target="_blank" href="' . get_edit_post_link( $post_id ) . '">' . $post->post_title . '</a>';
	}
}