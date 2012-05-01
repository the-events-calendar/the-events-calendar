<?php
/**
 * Organizer Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) ) {

	/**
	 * Organizer ID
	 *
	 * Returns the event Organizer ID.
	 *
	 * @param int $postId Can supply either event id or organizer id, if none specified, current post is used
	 * @return int Organizer
	 * @since 2.0
	 */
	function tribe_get_organizer_id( $postId = null)  {
		$postId = TribeEvents::postIdHelper( $postId );
		if (is_numeric($postId) && $postId > 0) {
			$tribe_ecp = TribeEvents::instance();
			// check if $postId is an organizer id
			if ($tribe_ecp->isOrganizer($postId)) {
				$organizer_id = $postId;
			} else {
				$organizer_id = tribe_get_event_meta( $postId, '_EventOrganizerID', true );
			}
		}
		return apply_filters('tribe_get_organizer_id', $organizer_id, $postId );
	}

	/**
	 * Get Organizer
	 *
	 * Returns the name of the Organizer
	 *
	 * @param int $postId Can supply either event id or organizer id, if none specified, current post is used
	 * @return string Organizer's Name
	 * @since 2.0
	 */
	function tribe_get_organizer( $postId = null)  {
		$postId = TribeEvents::postIdHelper( $postId );
		$organizer_id = (int) tribe_get_organizer_id( $postId );
		if ($organizer_id > 0) {
			$output = esc_html(get_the_title( $organizer_id ));
			return apply_filters( 'tribe_get_organizer', $output );
		}
		return null;
	}

	/**
	 * Organizer Test
	 *
	 * Returns true or false depending on if the post id has/is a n organizer
	 *
	 * @param int $postId Can supply either event id or organizer id, if none specified, current post is used
	 * @return bool
	 * @since 2.0
	 */
	function tribe_has_organizer( $postId = null) {
		$postId = TribeEvents::postIdHelper( $postId );
		return ( tribe_get_organizer_id( $postId ) > 0 ) ? true : false;
	}

	/**
	 * Organizer Email
	 *
	 * Returns the Organizer's Email
	 *
	 * @param int $postId Can supply either event id or organizer id, if none specified, current post is used
	 * @return string Organizer's Email
	 * @since 2.0
	 */
	function tribe_get_organizer_email( $postId = null)  {
		$postId = TribeEvents::postIdHelper( $postId );
		$output = esc_html(tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerEmail', true ));
		return apply_filters( 'tribe_get_organizer_email', $output);
	}

	/**
	 * Organizer Website Link
	 *
	 * Returns the event Organizer Name with a link to their supplied website url
	 *
	 * @param int $postId Can supply either event id or organizer id, if none specified, current post is used
	 * @param bool $full_link If true displays full html links around organizers name, if false returns just the link without displaying it
	 * @param bool $display If true, echo the link, otherwise return
	 * @return string Organizer Name and Url
	 * @since 2.0
	 */
	function tribe_get_organizer_link( $postId = null, $full_link = true, $display = true ) {
		$postId = TribeEvents::postIdHelper( $postId );
		$url = esc_url(tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerWebsite', true ));

		if( $full_link && $url != '' ) {
			$organizer_name = tribe_get_organizer($postId);
			$link = '<a href="'.$url.'">'.$organizer_name.'</a>';
		} else {
			$link = $url;
		}
		$link = apply_filters( 'tribe_get_organizer_link', $link, $postId, $display, $url );
		if ( $display ) {
			echo $link;
		} else {
			return $link;
		}
	}

	/**
	 * Organizer Phone
	 *
	 * Returns the event Organizer's phone number
	 *
	 * @param int $postId Can supply either event id or organizer id, if none specified, current post is used
	 * @return string Organizer's Phone Number
	 * @since 2.0
	 */
	function tribe_get_organizer_phone( $postId = null)  {
		$postId = TribeEvents::postIdHelper( $postId );
		$output = esc_html(tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerPhone', true ));
		return apply_filters( 'tribe_get_organizer_phone', $output ); 
	}

}
?>
