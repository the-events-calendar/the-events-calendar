<?php
/**
 * Template tags for premium ECP functionality
 * @author John Gadbois
 */
if( class_exists( 'TribeEventsPro' ) && !function_exists( 'tribe_get_recurrence_text' ) ) {	
	/**
	 * tribe_get_recurrence_text 
	 *
	 * Get the textual version of event recurrence
	 * e.g Repeats daily for three days 
	 */
	function tribe_get_recurrence_text( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		$postId = tribe_post_id_helper( $postId );
	   return apply_filters( 'tribe_get_recurrence_text', Events_Recurrence_Meta::recurrenceToText( $postId ) );
	}

	
    /**
     * tribe_event_meta
     *
	 * Get the custom meta for a particular event and return via template tags
     * 
     */
	function tribe_event_meta( $postId = null ) {
	  	$postId = tribe_post_id_helper( $postId );
      	$customFields = tribe_get_option('custom-fields');
	  	$meta_html = '';
	
	  	foreach ($customFields as $field) {
	     	$meta = str_replace('|', ', ', get_post_meta($postId, $field['name'], true));
	     	if($meta) {
	     		// $meta is passed through kses
				$meta_html .= '<dt>' . esc_html($field['label']) . ':</dt><dd>' . $meta . '</dd>';
			}
		}
	
		return $meta_html;
    }
	// TODO: create seperate template tags for each custom field

}