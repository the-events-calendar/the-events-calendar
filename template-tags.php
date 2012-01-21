<?php
/**
 * Events Calendar Pro template Tags
 *
 * Display functions for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEventsPro' ) ) {	

	/**
	 * Event Recurrence
	 * 
	 * Test to see if event is recurring.
	 * 
	 * @param int $postId (optional)
	 * @return bool true if event is a recurring event.
	 * @since 2.0
	 */
	if (!function_exists( 'tribe_is_recurring_event' )) {
		function tribe_is_recurring_event( $postId = null )  {
			$tribe_ecp = TribeEvents::instance();
			$postId = TribeEvents::postIdHelper( $postId );
			return sizeof(get_post_meta($postId, '_EventStartDate')) > 1;
		}
	}

	/**
	 * Recurrence Text
	 * 
	 * Get the textual version of event recurrence
	 * e.g Repeats daily for three days 
	 *
	 * @param int $postId (optional)
	 * @return string Summary of recurrence.
	 * @since 2.0
	 */
	if (!function_exists( 'tribe_get_recurrence_text' )) {
		function tribe_get_recurrence_text( $postId = null )  {
			$postId = TribeEvents::postIdHelper( $postId );
			$tribe_ecp = TribeEvents::instance();
		  	return apply_filters( 'tribe_get_recurrence_text', TribeEventsRecurrenceMeta::recurrenceToText( $postId ) );
		}
	}

	/**
	 * Recurring Event List Link
	 *
	 * Display link for all occurrences of an event (based on the currently queried event).
	 *
	 * @param int $postId (optional)
	 * @since 2.0
	 */
	if (!function_exists( 'tribe_all_occurences_link' )) {
		function tribe_all_occurences_link( $postId = null )  {
			$postId = TribeEvents::postIdHelper( $postId );
			$post = get_post($postId);
			$tribe_ecp = TribeEvents::instance();
			echo $tribe_ecp->getLink('all');		
		}
	}
	
	/**
	 * Event Custom Fields
	 * 
	 * Get an array of custom fields
	 *
	 * @param int $postId (optional)
	 * @return array $data of custom fields
	 * @since 2.0
	 */
	function tribe_get_custom_fields( $postId = null ) {
		$postId = TribeEvents::postIdHelper( $postId );
		$data = array();
		$customFields = tribe_get_option('custom-fields', false);
		if (is_array($customFields)) {
			foreach ($customFields as $field) {
				$meta = str_replace('|', ', ', get_post_meta($postId, $field['name'], true));
				if ( $meta ) {
					$data[esc_html($field['label'])] = $meta; // $meta has been through wp_kses - links are allowed
				}
			}
		}
		return $data;
	}
	
	/**
	 * Event Custom Fields (Display)
	 * 
	 * Display a definition term list of custom fields
	 *
	 * @param int $postId (optional)
	 * @since 2.0
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
	 * Get Event Custom Field by Label
	 *
	 * retrieve a custom field's value by searching its label
	 * instead of its (more obscure) ID
	 *
	 * @since 2.0.3
	 * @param (string) $label, the label to search for
	 * @param (int) $eventID (optional), the event to look for, defaults to global $post
	 * @return (string) value of the field
	 */
	function tribe_get_custom_field( $label, $eventID = null ) {
		return apply_filters('tribe_get_custom_field', TribeEventsCustomMeta::get_custom_field_by_label( $label, $eventID ) );
	}

	/**
	 * Echo Event Custom Field by Label
	 *
	 * same as above but echo instead of return
	 *
	 * @since 2.0.3
	 * @param (string) $label, the label to search for
	 * @param (int) $eventID (optional), the event to look for, defaults to global $post
	 * @return (string) value of the field
	 */
	function tribe_custom_field( $label, $eventID = null ) {
		echo tribe_get_custom_field( $label, $eventID = null );
	}

	/**
	 * iCal Link (Single)
	 * 
	 * Returns an ical feed for a single event. Must be used in the loop.
	 * 
	 * @return string URL for ical for single event.
	 * @since 2.0
	 */
	function tribe_get_single_ical_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink( 'ical', 'single' );
		return $output;
	}

	/**
	 * iCal Link
	 * 
	 * Returns a sitewide ical link
	 *
	 * @return string URL for ical dump.
	 * @since 2.0
	 */
	function tribe_get_ical_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('ical');
		return $output;
	}

	/**
	 * Google Calendar Link
	 * 
	 * Returns an add to Google Calendar link. Must be used in the loop
	 *
	 * @param int $postId (optional)
	 * @return string URL for google calendar.
	 * @since 2.0
	 */
	function tribe_get_gcal_link( $postId = null )  {
		$postId = TribeEvents::postIdHelper( $postId );
		$tribe_ecp = TribeEventsPro::instance();
		$output = esc_url($tribe_ecp->googleCalendarLink( $postId ));
		return $output;
	}

	/** 
	 * Day View Link
	 * 
	 * Get a link to day view
	 *
	 * @param string $date
	 * @param string $day
	 * @return string HTML linked date
	 * @since 2.0
	 */
	function tribe_get_linked_day($date, $day) {
		$return = '';
		$return .= "<a href='" . tribe_get_day_link($date) . "'>";
		$return .= $day;
		$return .= "</a>";
		return $return;
	}

}
