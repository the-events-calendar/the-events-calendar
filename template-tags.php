<?php
/**
 * Template tags for premium ECP functionality
 * @author John Gadbois
 */
if( class_exists( 'TribeEventsPro' ) && !function_exists( 'tribe_get_recurrence_text' ) ) {	
	/**
	 * Get the textual version of event recurrence
	 * e.g Repeats daily for three days 
	 */
	function tribe_get_recurrence_text( $postId = null )  {
		$postId = tribe_post_id_helper( $postId );
		$tribe_ecp = TribeEvents::instance();
	  	return apply_filters( 'tribe_get_recurrence_text', TribeEventsRecurrenceMeta::recurrenceToText( $postId ) );
	}
	
	/**
	 * Get an array of custom fields
	 *
	 * @param int $postId 
	 * @return array $data of custom fields
	 */
	function tribe_get_custom_fields( $postId = null ) {
		$postId = tribe_post_id_helper( $postId );
	  	$customFields = tribe_get_option('custom-fields');
	  	$data = array();
	  	foreach ($customFields as $field) {
		 	$meta = str_replace('|', ', ', get_post_meta($postId, $field['name'], true));
		 	if($meta) {
				$data[esc_html($field['label'])] = $meta; // $meta has been through wp_kses - links are allowed
			}
		}
		return $data;
	}
	
	/**
	 * Echo a definition term list of custom fields
	 *
	 * @param int $postId
	 */
	function tribe_the_custom_fields( $postId = null ) {
		$fields = tribe_get_custom_fields( $postId );
	  	$meta_html = "<dl class='column'>\n";
	  	foreach ($fields as $label => $value) {
			$meta_html .= apply_filters('tribe_the_custom_field',"<dt>$label:</dt><dd>$value</dd>\n",$label,$value);
		}
		$meta_html .= "</dl>\n";
		echo $meta_html;
	}
	
	/**
	 * Returns an ical feed for a single event. Must be used in the loop.
	 * 
	 * @return string
	 */
	function tribe_get_single_ical_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = esc_url($tribe_ecp->getLink( 'ical', 'single' ));
		return $output;
	}

   /**
    * Returns a sitewide ical link
    */
	function tribe_get_ical_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = esc_url($tribe_ecp->getLink('ical'));
		return $output;
	}

	/**
	 * Returns an add to Google Calendar link. Must be used in the loop
	 */
	function tribe_get_gcal_link( $postId = null )  {
		$postId = tribe_post_id_helper( $postId );
		$tribe_ecp = TribeEventsPro::instance();
		$output = esc_url($tribe_ecp->googleCalendarLink( $postId ));
		return $output;
	}
	

}
