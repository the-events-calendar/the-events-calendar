<?php
if( class_exists( 'ECP_Premium' ) && !function_exists( 'tribe_get_recurrence_text' ) ) {
	/* Premium Template Tags */	
	
	/* Get the textual version of event recurrence */
	function tribe_get_recurrence_text( $postId = null )  {
		$tribe_ecp = Events_Calendar_Pro::instance();
		$postId = tribe_post_id_helper( $postId );
	   return apply_filters( 'tribe_get_recurrence_text', Events_Recurrence_Meta::recurrenceToText( $postId ) );
	}

	/* Get the custom meta for a particular event */
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
}