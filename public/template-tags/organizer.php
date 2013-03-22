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
		$organizer_id = null;
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
		$has_organizer = ( tribe_get_organizer_id( $postId ) > 0 ) ? true : false;
		return apply_filters('tribe_has_organizer', $has_organizer);
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
	 * @param bool $echo If true, echo the link, otherwise return
	 * @return string Organizer Name and Url
	 * @since 2.0
	 */
	function tribe_get_organizer_link( $postId = null, $full_link = true, $echo = true ) {
		$postId = TribeEvents::postIdHelper( $postId );
		if ( class_exists( 'TribeEventsPro' ) ) {
			$url = esc_url( get_permalink( tribe_get_organizer_id( $postId ) ) );
			$name = tribe_get_organizer($postId);
			$link = !empty($url) && !empty($name) ? '<a href="'.$url.'">'.$name.'</a>' : false;
			$link = apply_filters( 'tribe_get_organizer_link', $link, $postId, $echo, $url, $name );
			if ( $echo ) {
				echo $link;
			} else {
				return $link;
			}
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

	function tribe_get_organizer_permalink( $post_id = null ){
		$post_id = TribeEvents::postIdHelper( $post_id );
		$organizer_id = tribe_get_organizer_id( $post_id );
		$link = sprintf('<a href="%s">%s</a>',
			get_permalink( $organizer_id ),
			get_the_title( $organizer_id )
			);
		return apply_filters('tribe_get_organizer_permalink', $link, $post_id, $organizer_id );
	}
	
	function tribe_get_organizer_website_link( $post_id = null, $label = null ){
		$post_id = tribe_get_organizer_id( $post_id );
		$url = tribe_get_event_meta( $post_id, '_OrganizerWebsite', true );
		if( !empty($url) ) {
			$label = is_null($label) ? $url : $label;
			if( !empty( $url )) {
				$parseUrl = parse_url($url);
				if (empty($parseUrl['scheme'])) 
					$url = "http://$url";
			}
			$html = sprintf('<a href="%s" target="%s">%s</a>',
				$url,
				apply_filters('tribe_get_organizer_website_link_target', 'self'),
				apply_filters('tribe_get_organizer_website_link_label', $label)
				);
		} else {
			$html = '';
		}
		return apply_filters('tribe_get_organizer_website_link', $html );
	}
	
	/**
	 * Get all the organizers
	 *
	 * @author PaulHughes01
	 * @since 2.1
	 * @param bool $only_with_upcoming Only return organizers with upcoming events attached to them.
	 * @return array An array of organizer post objects.
	 */
	function tribe_get_organizers( $only_with_upcoming = false, $posts_per_page = -1 ) {
		$organizers = get_posts( array( 'post_type' => TribeEvents::ORGANIZER_POST_TYPE, 'posts_per_page' => $posts_per_page ) );
		
		return $organizers;
	}

}
