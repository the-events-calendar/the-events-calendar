<?php
/**
 * Provides methods to interact with the string dictionary.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits;
 */

namespace TEC\Events\Custom_Tables\V1\Traits;

/**
 * Trait With_String_Dictionary.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits;
 */
trait With_String_Dictionary {
	/**
	 * Returns the markup for the event edit link.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return string The HTML markup with the event link.
	 */
	private function get_event_link_markup( $post_id ) {
		$post             = get_post( $post_id );
		$post_title = $post->post_title;

		if ( empty( $post_title ) ) {
			$post_title = sprintf( esc_html__( 'ID %1$d (Untitled)', 'the-events-calendar' ), $post->ID );
		}

		$action           = '&action=edit';
		$post_type_object = get_post_type_object( $post->post_type );
		$url              = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );

		return '<a target="_blank" href="' . $url . '">' . $post_title . '</a>';
	}
}