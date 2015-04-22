<?php
/**
 * Events Calendar Pro template Tags
 *
 * Display functions for use in WordPress templates.
 * @todo move view specific functions to their own file
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if( class_exists( 'TribeEventsPro' ) ) {

	if ( !function_exists( 'tribe_get_mapview_link' ) ) {
		function tribe_get_mapview_link( $term = null ) {
			global $wp_query;
			if ( isset( $wp_query->query_vars[ TribeEvents::TAXONOMY ] ) ) {
				$term = $wp_query->query_vars[TribeEvents::TAXONOMY];
			}
			$output = TribeEvents::instance()->getLink( 'map', null, $term );

			return apply_filters( 'tribe_get_map_view_permalink', $output );
		}
	}

	/**
	 * Event Recurrence
	 *
	 * Test to see if event is recurring.
	 *
	 * @param int $postId (optional)
	 *
	 * @return bool true if event is a recurring event.
	 */
	if (!function_exists( 'tribe_is_recurring_event' )) {
		function tribe_is_recurring_event( $postId = null )  {
			if ( is_object($postId) ) {
				$postId = $postId->ID;
			}
			$postId = $postId ? $postId : get_the_ID();
			if ( get_post_type($postId) != TribeEvents::POSTTYPE ) {
				return false;
			}
			$instances = tribe_get_recurrence_start_dates($postId);
			$recurring = count($instances) > 1;
			if ( ! $recurring && get_post_meta( $postId, '_EventNextPendingRecurrence', true ) ) {
				$recurring = true;
			}

			return apply_filters( 'tribe_is_recurring_event', $recurring, $postId );
		}
	}

	/**
	 * Get the start dates of all instances of the event,
	 * in ascending order
	 *
	 * @param int $post_id
	 *
	 * @return array Start times, as Y-m-d H:i:s
	 */
	function tribe_get_recurrence_start_dates( $post_id = null ) {
		$post_id = TribeEvents::postIdHelper($post_id);

		return TribeEventsRecurrenceMeta::get_start_dates( $post_id );
	}

	/**
	 * Recurrence Text
	 *
	 * Get the textual version of event recurrence
	 * e.g Repeats daily for three days
	 *
	 * @param int $postId (optional)
	 *
	 * @return string Summary of recurrence.
	 */
	if (!function_exists( 'tribe_get_recurrence_text' )) {
		function tribe_get_recurrence_text( $postId = null )  {
			$postId = TribeEvents::postIdHelper( $postId );

			return apply_filters( 'tribe_get_recurrence_text', TribeEventsRecurrenceMeta::recurrenceToTextByPost( $postId ) );
		}
	}

	/**
	 * Recurring Event List Link
	 *
	 * Display link for all occurrences of an event (based on the currently queried event).
	 *
	 * @param int $postId (optional)
	 */
	if (!function_exists( 'tribe_all_occurences_link' )) {
		function tribe_all_occurences_link( $postId = null, $echo = true )  {
			$postId = TribeEvents::postIdHelper( $postId );
			$tribe_ecp = TribeEvents::instance();
			$link = apply_filters('tribe_all_occurences_link', $tribe_ecp->getLink('all', $postId));
			if( $echo ) {
				echo $link;
			} else {
				return $link;
			}
		}
	}

	// show user front-end settings only if ECP is active
	function tribe_recurring_instances_toggle( $postId = null )  {
			$hide_recurrence = ( !empty( $_REQUEST['tribeHideRecurrence'] ) && $_REQUEST['tribeHideRecurrence'] == '1' ) || ( empty( $_REQUEST['tribeHideRecurrence'] ) && empty( $_REQUEST['action'] ) && tribe_get_option( 'hideSubsequentRecurrencesDefault', false ) ) ? '1' : false;
		if( !tribe_is_week() && !tribe_is_month() ){
			echo '<span class="tribe-events-user-recurrence-toggle">';
				echo '<label for="tribeHideRecurrence">';
					echo '<input type="checkbox" name="tribeHideRecurrence" value="1" id="tribeHideRecurrence" ' . checked( $hide_recurrence, 1, false ) . '>' . __( 'Show only the first upcoming instance of recurring events', 'tribe-events-calendar-pro' );
				echo '</label>';
			echo '</span>';
		}
	}

	/**
	 * Event Custom Fields
	 *
	 * Get an array of custom fields
	 *
	 * @param int $postId (optional)
	 *
	 * @return array $data of custom fields
	 * @todo move logic to TribeEventsCustomMeta class
	 */
	function tribe_get_custom_fields( $postId = null ) {
		$postId = TribeEvents::postIdHelper( $postId );
		$data = array();
		$customFields = tribe_get_option('custom-fields', false);
		if (is_array($customFields)) {
			foreach ($customFields as $field) {
				$meta = str_replace('|', ', ', get_post_meta($postId, $field['name'], true));
				if( $field['type'] == 'url' && !empty($meta) ) {
					$url_label = $meta;
					$parseUrl = parse_url($meta);
					if ( empty( $parseUrl['scheme'] ) ) {
						$meta = "http://$meta";
					}
					$meta = sprintf('<a href="%s" target="%s">%s</a>',
						esc_url( $meta ),
						apply_filters('tribe_get_event_website_link_target', 'self'),
						apply_filters('tribe_get_event_website_link_label', $url_label)
						);
				}
				if ( $meta ) {
					$data[esc_html($field['label'])] = $meta; // $meta has been through wp_kses - links are allowed
				}
			}
		}

		return apply_filters('tribe_get_custom_fields', $data);
	}

	/**
	 * Event Custom Fields (Display)
	 *
	 * Display a definition term list of custom fields
	 *
	 * @param int $postId (optional)
	 *
	 * @deprecated
	 * @todo remove in 3.11
	 */
	function tribe_the_custom_fields( $postId = null, $echo = true ) {
		_deprecated_function( __FUNCTION__, '3.9', "tribe_get_template_part( 'pro/modules/meta/additional-fields', null, array(
			'fields' => tribe_get_custom_fields()
		) );" );
		ob_start();
		tribe_get_template_part( 'pro/modules/meta/additional-fields', null, array(
			'fields' => tribe_get_custom_fields()
		) );
		$html = ob_get_clean();
		if ( has_filter( 'tribe_the_custom_fields' ) ) {
			_deprecated_function( "The 'tribe_the_custom_fields' filter", '3.9', " the 'tribe_get_template_part_content' filter for pro/modules/meta/additional-fields" );
			$html = apply_filters( 'tribe_the_custom_fields', $html );
		}
		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * Get Event Custom Field by Label
	 *
	 * retrieve a custom field's value by searching its label
	 * instead of its (more obscure) ID
	 *
	 * @param (string) $label, the label to search for
	 * @param (int) $eventID (optional), the event to look for, defaults to global $post
	 *
	 * @return (string) value of the field
	 * @deprecated
	 * @todo   remove in 3.11
	 */
	function tribe_get_custom_field( $label, $eventID = null ) {

		_deprecated_function( __FUNCTION__, '3.9', 'tribe_get_custom_fields' );

		$field = TribeEventsCustomMeta::get_custom_field_by_label( $label, $eventID );

		if ( has_filter( 'tribe_get_custom_field' ) ) {
			_deprecated_function( "The 'tribe_get_custom_field' filter", '3.9', " the 'tribe_get_custom_fields' filter" );
			$field = apply_filters( 'tribe_get_custom_field', $field );
	}

		return $field;
	}

	/**
	 * Echo Event Custom Field by Label
	 *
	 * same as above but echo instead of return
	 *
	 * @param (string) $label, the label to search for
	 * @param (int) $eventID (optional), the event to look for, defaults to global $post
	 *
	 * @return (string) value of the field
	 * @deprecated
	 * @todo   remove in 3.11
	 */
	function tribe_custom_field( $label, $eventID = null ) {
		_deprecated_function( __FUNCTION__, '3.9', "tribe_get_custom_fields" );
		$field = TribeEventsCustomMeta::get_custom_field_by_label( $label, $eventID );
		echo $field;
	}

	/**
	* Get Related Events
	*
	* Get a list of related events to the current post
	*
	* @param int $count
	 *
	* @return array Array of events
	 * @deprecated
	 * @todo remove in 3.11
	*/
	function tribe_get_related_events ($count=3) {
		_deprecated_function( __FUNCTION__, '3.9', "tribe_get_related_posts" );

		$posts = tribe_get_related_posts( $count );

		if ( has_filter( 'tribe_get_related_events' ) ) {
			_deprecated_function( "The 'tribe_get_related_events' filter", '3.9', " the 'tribe_get_related_posts' filter" );
			$posts = apply_filters( 'tribe_get_related_events', $posts );
	}

		return $posts;
	}

	/**
	* Display Related Events
	*
	* Display a list of related events to the current post
	*
	* @param string $title
	* @param int $count
	* @param bool $thumbnails
	* @param bool $start_date
	 *
	 * @deprecated
	 * @todo remove in 3.11
	*/
	function tribe_related_events ($title, $count=3, $thumbnails=false, $start_date=false, $get_title=true) {
		_deprecated_function( __FUNCTION__, '3.9', 'tribe_single_related_events' );
		if ( has_filter( 'tribe_related_events' ) ) {
			_deprecated_function( "The 'tribe_related_events' filter", '3.9', " the 'tribe_after_get_template_part' action for pro/related-events" );

			return apply_filters( 'tribe_related_events', tribe_single_related_events() );
		} else {
			tribe_single_related_events();
	}
	}

	/**
	 * Displays the saved organizer
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_organizer() {
		$current_organizer_id = tribe_get_option('eventsDefaultOrganizerID', 'none' );
		$current_organizer = ($current_organizer_id != 'none' && $current_organizer_id != 0 && $current_organizer_id) ? tribe_get_organizer($current_organizer_id) : __('No default set', 'tribe-events-calendar-pro');
		$current_organizer = esc_html( $current_organizer );
		echo '<p class="tribe-field-indent description">'.sprintf( __('The current default organizer is: %s', 'tribe-events-calendar-pro' ), '<strong>'.$current_organizer.'</strong>').'</p>';
	}

	/**
	 * Displays the saved venue
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_venue() {
		$current_venue_id = tribe_get_option('eventsDefaultVenueID', 'none' );
		$current_venue = ($current_venue_id != 'none' && $current_venue_id != 0 && $current_venue_id) ? tribe_get_venue($current_venue_id) : __('No default set', 'tribe-events-calendar-pro');
		$current_venue = esc_html( $current_venue );
		echo '<p class="tribe-field-indent tribe-field-description description">'.sprintf( __('The current default venue is: %s', 'tribe-events-calendar-pro' ), '<strong>'.$current_venue.'</strong>').'</p>';
	}

	/**
	 * Displays the saved address
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_address() {
		$option = tribe_get_option('eventsDefaultAddress', __('No default set', 'tribe-events-calendar-pro'));
		$option = ( !isset($option) || $option == '' || !$option ) ? __('No default set', 'tribe-events-calendar-pro') : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">'.sprintf( __('The current default address is: %s', 'tribe-events-calendar-pro' ), '<strong>'.$option.'</strong>').'</p>';
	}

	/**
	 * Displays the saved city
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_city() {
		$option = tribe_get_option('eventsDefaultCity', __('No default set', 'tribe-events-calendar-pro'));
		$option = ( !isset($option) || $option == '' || !$option ) ? __('No default set', 'tribe-events-calendar-pro') : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">'.sprintf( __('The current default city is: %s', 'tribe-events-calendar-pro' ), '<strong>'.$option.'</strong>').'</p>';
	}

	/**
	 * Displays the saved state
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_state() {
		$option = tribe_get_option('eventsDefaultState', __('No default set', 'tribe-events-calendar-pro'));
		$option = ( !isset($option) || $option == '' || !$option ) ? __('No default set', 'tribe-events-calendar-pro') : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">'.sprintf( __('The current default state is: %s', 'tribe-events-calendar-pro' ), '<strong>'.$option.'</strong>').'</p>';
	}

	/**
	 * Displays the saved province
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_province() {
		$option = tribe_get_option('eventsDefaultProvince', __('No default set', 'tribe-events-calendar-pro'));
		$option = ( !isset($option) || $option == '' || !$option ) ? __('No default set', 'tribe-events-calendar-pro') : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">'.sprintf( __('The current default province is: %s', 'tribe-events-calendar-pro' ), '<strong>'.$option.'</strong>').'</p>';
	}

	/**
	 * Displays the saved zip
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_zip() {
		$option = tribe_get_option('eventsDefaultZip', __('No default set', 'tribe-events-calendar-pro'));
		$option = ( !isset($option) || $option == '' || !$option ) ? __('No default set', 'tribe-events-calendar-pro') : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">'.sprintf( __('The current default postal code/zip code is: %s', 'tribe-events-calendar-pro' ), '<strong>'.$option.'</strong>').'</p>';
	}

	/**
	 * Displays the saved country
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_country() {
		$option = tribe_get_option('defaultCountry', __('No default set', 'tribe-events-calendar-pro'));
		$option = ( !isset($option) || $option == '' || !$option || empty($option) || !is_array($option) || !isset($option[1]) ) ? __('No default set', 'tribe-events-calendar-pro') : $option = $option[1];
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">'.sprintf( __('The current default country is: %s', 'tribe-events-calendar-pro' ), '<strong>'.$option.'</strong>').'</p>';
	}

	/**
	 * Displays the saved phone
	 * Used in the settings screen
	 *
	 * @return void
	 * @deprecated
	 * @todo move this to the settings classes and remove
	 */
	function tribe_display_saved_phone() {
		$option = tribe_get_option('eventsDefaultPhone', __('No default set', 'tribe-events-calendar-pro'));
		$option = ( !isset($option) || $option == '' || !$option ) ? __('No default set', 'tribe-events-calendar-pro') : $option;
		$option = esc_html( $option );
		echo '<p class="tribe-field-indent tribe-field-description venue-default-info description">'.sprintf( __('The current default phone is: %s', 'tribe-events-calendar-pro' ), '<strong>'.$option.'</strong>').'</p>';
	}

	/**
	 * Returns the formatted and converted distance from the db (always in kms.) to the unit selected
	 * by the user in the 'defaults' tab of our settings.
	 *
	 * @param $distance_in_kms
	 *
	 * @return mixed
	 * @todo remove tribe_formatted_distance filter in 3.11
	 */
	function tribe_get_distance_with_unit( $distance_in_kms ) {

		$tec = TribeEvents::instance();

		$unit     = $tec->getOption( 'geoloc_default_unit', 'miles' );
		$distance = round( tribe_convert_units( $distance_in_kms, 'kms', $unit ), 2 );

		if ( has_filter( 'tribe_formatted_distance' ) ) {
			_deprecated_function( "The 'tribe_formatted_distance' filter", '3.9', " the 'tribe_get_distance_with_unit' filter" );
			$distance = apply_filters( 'tribe_formatted_distance', $distance . ' ' . $unit );
	}

		return apply_filters( 'tribe_get_distance_with_unit', $distance, $distance_in_kms, $unit );
	}

	/**
	 * Returns an events distance from location search term
	 *
	 * @return string
	 * @todo move tags to template
	 *
	 */
	function tribe_event_distance() {
		global $post;
 		if ( !empty( $post->distance ) ) {
			return '<span class="tribe-events-distance">'. tribe_get_distance_with_unit( $post->distance ) .'</span>';
		}
	}

	/**
	 *
	 * Converts units. Uses tribe_convert_$unit_to_$unit_ratio filter to get the ratio.
	 *
	 * @param $value
	 * @param $unit_from
	 * @param $unit_to
	 */
	function tribe_convert_units( $value, $unit_from, $unit_to ) {

		if ( $unit_from === $unit_to ) {
			return $value;
		}

		$filter = sprintf( 'tribe_convert_%s_to_%s_ratio', $unit_from, $unit_to );
		$ratio  = apply_filters( $filter, 0 );

		// if there's not filter for this conversion, let's return the original value
		if ( empty( $ratio ) ) {
			return $value;
		}

		return ( $value * $ratio );

	}

	/**
	 * Get the first day of the week from a provided date
	 *
	 * @param null|mixed $date  given date or week # (week # assumes current year)
	 *
	 * @return string
	 * @todo move logic to TribeDateUtils
	 */
	function tribe_get_first_week_day( $date = null ) {
		global $wp_query;
		$offset = 7 - get_option( 'start_of_week', 0 );

		if ( tribe_is_ajax_view_request() ) {
			$date = is_null( $date ) ? $_REQUEST['eventDate'] : $date;
		} else {
			$date = is_null( $date ) ? $wp_query->get('start_date') : $date;
		}

		try {
			$date = new DateTime( $date );
		} catch ( exception $e ) {
			$date = new DateTime();
		}

		// Clone to avoid altering the original date
		$r = clone $date;
		$r->modify(-(($date->format('w') + $offset) % 7) . 'days');

		return apply_filters('tribe_get_first_week_day', $r->format('Y-m-d'));
	}

	/**
	 * Get the last day of the week from a provided date
	 *
	 * @param string|int $date_or_int A given date or week # (week # assumes current year)
	 * @param bool $by_date determines how to parse the date vs week provided
	 * @param int $first_day sets start of the week (offset) respectively, accepts 0-6
	 *
	 * @return DateTime
	 */
	function tribe_get_last_week_day( $date_or_int, $by_date = true ) {
		return apply_filters('tribe_get_last_week_day', date('Y-m-d', strtotime( tribe_get_first_week_day( $date_or_int, $by_date ) . ' +7 days' )));
	}

	/**
	 * Week Loop View Test
	 *
	 * @return bool
	 */
	function tribe_is_week()  {
		$is_week = (TribeEvents::instance()->displaying == 'week') ? true : false;

		return apply_filters('tribe_is_week', $is_week);
	}

	/**
	 * Week Loop View Test
	 *
	 * @return bool
	 */
	function tribe_is_photo()  {
		$is_photo = (TribeEvents::instance()->displaying == 'photo') ? true : false;

		return apply_filters('tribe_is_photo', $is_photo);
	}

	/**
	 * Map Loop View Test
	 *
	 * @return bool
	 */
	function tribe_is_map() {
		$tribe_ecp = TribeEvents::instance();
		$is_map    = ( $tribe_ecp->displaying == 'map' ) ? true : false;

		return apply_filters( 'tribe_is_map', $is_map );
	}

	/**
	 * Get last week permalink by provided date (7 days offset)
	 *
	 * @uses tribe_get_week_permalink
	 *
	 * @param string $week
	 * @param bool $is_current
	 *
	 * @return string $permalink
	 * @todo move logic to week template class
	 */
	function tribe_get_last_week_permalink( $week = null ) {
		$week = !empty( $week ) ? $week : tribe_get_first_week_day();
		if ( PHP_INT_SIZE <= 4 ) {
			if ( date('Y-m-d', strtotime($week)) < '1902-01-08' ) {
				throw new OverflowException(__('Date out of range.', 'tribe-events-calendar-pro'));
			}
		}

		$week = date('Y-m-d', strtotime( $week . ' -1 week'));

		return apply_filters('tribe_get_last_week_permalink', tribe_get_week_permalink( $week ) );
	}

	/**
	 * Get next week permalink by provided date (7 days offset)
	 *
	 * @uses tribe_get_week_permalink
	 *
	 * @param string $week
	 *
	 * @return string $permalink
	 * @todo move logic to week template class
	 */
	function tribe_get_next_week_permalink( $week = null ) {
		$week = !empty( $week ) ? $week : tribe_get_first_week_day();
		if ( PHP_INT_SIZE <= 4 ) {
			if ( date('Y-m-d', strtotime($week)) > '2037-12-24' ) {
				throw new OverflowException(__('Date out of range.', 'tribe-events-calendar-pro'));
			}
		}
		$week = date('Y-m-d', strtotime( $week . ' +1 week'));

		return apply_filters('tribe_get_next_week_permalink', tribe_get_week_permalink( $week ) );
	}

	/**
	 * Get week permalink
	 *
	 * @param string $week
	 *
	 * @return string $permalink
	 */
	function tribe_get_week_permalink( $week = null, $term = true ){
		$week = is_null($week) ? false : date('Y-m-d', strtotime( $week ) );
		if ( isset( $wp_query->query_vars[ TribeEvents::TAXONOMY ] ) ) {
			$term = $wp_query->query_vars[TribeEvents::TAXONOMY];
		}
		$output = TribeEvents::instance()->getLink( 'week', $week, $term );

		return apply_filters('tribe_get_week_permalink', $output);
	}


	/**
	 * Get photo permalink by provided date
	 * @return string $permalink
	 */
	function tribe_get_photo_permalink( $term = true ) {
		if ( isset( $wp_query->query_vars[ TribeEvents::TAXONOMY ] ) ) {
			$term = $wp_query->query_vars[TribeEvents::TAXONOMY];
		}
		$output = TribeEvents::instance()->getLink( 'photo', null, $term );

		return apply_filters( 'tribe_get_photo_view_permalink', $output );
	}

	/**
	 * Echos the single events page related events boxes.
	 * @return void.
	 */
	function tribe_single_related_events( ) {
		tribe_get_template_part( 'pro/related-events' );
	}

	/**
	 * Template tag to get related posts for the current post.
	 *
	 * @param int $count number of related posts to return.
	 * @param int|obj $post the post to get related posts to, defaults to current global $post
	 *
	 * @return array the related posts.
	 */
	function tribe_get_related_posts( $count = 3, $post = false ) {
		$post_id = TribeEvents::postIdHelper( $post );
		$tags = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );
		$categories = wp_get_object_terms( $post_id, TribeEvents::TAXONOMY, array( 'fields' => 'ids' ) );
		if ( ! $tags && ! $categories ) {
			return;
		}
		$args = array(
			'posts_per_page' => $count,
			'post__not_in' => array( $post_id ),
			'eventDisplay' => 'list',
			'tax_query' => array('relation' => 'OR'),
			'orderby' => 'rand',
		);
		if ( $tags ) {
			$args['tax_query'][] = array( 'taxonomy' => 'post_tag', 'field' => 'id', 'terms' => $tags );
		}
		if ( $categories ) {
			$args['tax_query'][] = array(
				'taxonomy' => TribeEvents::TAXONOMY,
				'field'    => 'id',
				'terms'    => $categories
			);
		}

		$args = apply_filters( 'tribe_related_posts_args', $args );

		if ( $args ) {
			$posts = TribeEventsQuery::getEvents( $args );
		} else {
			$posts = array();
		}

		return apply_filters( 'tribe_get_related_posts',  $posts ) ;
	}

	/**
	 * show the recurring event info in a tooltip
	 *
	 * return the details of the start/end date/time
	 *
	 * @param int     $post_id
	 *
	 * @return string
	 * @todo remove tribe_events_event_recurring_info_tooltip filter in 3.11
	 */
	function tribe_events_recurrence_tooltip( $post_id = null ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}
		$tooltip = '';
		if ( tribe_is_recurring_event( $post_id ) ) {
			$tooltip .= '<div class="recurringinfo">';
			$tooltip .= '<div class="event-is-recurring">';
			$tooltip .= '<span class="tribe-events-divider">|</span>';
			$tooltip .= __( 'Recurring Event', 'tribe-events-calendar-pro' );
			$tooltip .= sprintf(' <a href="%s">%s</a>',
				esc_url( tribe_all_occurences_link( $post_id, false ) ),
				__( '(See all)', 'tribe-events-calendar-pro' )
			);
			$tooltip .= '<div id="tribe-events-tooltip-'. $post_id .'" class="tribe-events-tooltip recurring-info-tooltip">';
			$tooltip .= '<div class="tribe-events-event-body">';
			$tooltip .= tribe_get_recurrence_text( $post_id );
			$tooltip .= '</div>';
			$tooltip .= '<span class="tribe-events-arrow"></span>';
			$tooltip .= '</div>';
			$tooltip .= '</div>';
			$tooltip .= '</div>';
		}

		if ( has_filter( 'tribe_events_event_recurring_info_tooltip' ) ) {
			_deprecated_function( "The 'tribe_get_related_events' filter", '3.9', " the 'tribe_get_related_posts' filter" );
		$tooltip = apply_filters( 'tribe_events_event_recurring_info_tooltip', $tooltip ); // for backwards-compat, will be removed
		}

		return apply_filters( 'tribe_events_recurrence_tooltip', $tooltip );
	}

	/*
	 * Returns or echoes a url to a file in the Events Calendar PRO plugin resources directory
	 *
	 * @param string $resource the filename of the resource
	 * @param bool $echo whether or not to echo the url
	 * @return string
	 **/
	function tribe_events_pro_resource_url($resource, $echo = false) {
		$url = apply_filters('tribe_events_pro_resource_url', trailingslashit( TribeEventsPro::instance()->pluginUrl ).'resources/'.$resource, $resource);
		if ($echo) {
			echo $url;
		}

		return $url;
	}


}
