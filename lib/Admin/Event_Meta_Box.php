<?php
class Tribe__Events__Admin__Event_Meta_Box {
	public function do_meta_box( $event ) {
		$saved = false;
		$tec = TribeEvents::instance();

		if ( ! $event ) {
			global $post;

			if ( isset( $_GET['post'] ) && $_GET['post'] ) {
				$saved = true;
			}
		} else {
			$post = $event;

			if ( $post->ID ) {
				$saved = true;
			} else {
				$saved = false;
			}
		}

		$options = '';
		$style   = '';

		if ( isset( $post->ID ) ) {
			$postId = $post->ID;
		} else {
			$postId = 0;
		}

		foreach ( $tec->metaTags as $tag ) {
			if ( $postId && $saved ) { //if there is a post AND the post has been saved at least once.

				// Sort the meta to make sure it is correct for recurring events

				$meta = get_post_meta( $postId, $tag );
				sort( $meta );
				if ( isset( $meta[0] ) ) {
					$$tag = $meta[0];
				}
			} else {
				$cleaned_tag = str_replace( '_Event', '', $tag );

				//allow posted data to override default data
				if ( isset( $_POST['Event' . $cleaned_tag] ) ) {
					$$tag = stripslashes_deep( $_POST['Event' . $cleaned_tag] );
				} else {
					$$tag = call_user_func( array( $tec->defaults(), $cleaned_tag ) );
				}
			}
		}

		if ( isset( $_EventOrganizerID ) && $_EventOrganizerID ) {
			foreach ( $tec->organizerTags as $tag ) {
				$$tag = get_post_meta( $_EventOrganizerID, $tag, true );
			}
		} else {
			foreach ( $tec->organizerTags as $tag ) {
				$cleaned_tag = str_replace( '_Organizer', '', $tag );
				if ( isset( $_POST['organizer'][$cleaned_tag] ) ) {
					$$tag = stripslashes_deep( $_POST['organizer'][$cleaned_tag] );
				}
			}
		}

		if ( isset( $_EventVenueID ) && $_EventVenueID ) {

			foreach ( $tec->venueTags as $tag ) {
				$$tag = get_post_meta( $_EventVenueID, $tag, true );
			}

		} else {
			$_VenueVenue = $tec->defaults()->venue_id();
			if ( !$_VenueVenue ) {
				$_VenueVenue = NULL;
			}
		}

		$_EventAllDay    = isset( $_EventAllDay ) ? $_EventAllDay : false;
		$_EventStartDate = ( isset( $_EventStartDate ) ) ? $_EventStartDate : null;

		if ( isset( $_EventEndDate ) ) {
			if ( $_EventAllDay && TribeDateUtils::timeOnly( $_EventEndDate ) != '23:59:59' && TribeDateUtils::timeOnly( tribe_event_end_of_day() ) != '23:59:59' ) {

				// If it's an all day event and the EOD cutoff is later than midnight
				// set the end date to be the previous day so it displays correctly in the datepicker
				// so the datepickers will match. we'll set the correct end time upon saving
				// @todo: remove this once we're allowed to have all day events without a start/end time

				$_EventEndDate = date_create( $_EventEndDate );
				$_EventEndDate->modify( '-1 day' );
				$_EventEndDate = $_EventEndDate->format( TribeDateUtils::DBDATETIMEFORMAT );

			}
		} else {
			$_EventEndDate = null;
		}
		$isEventAllDay        = ( $_EventAllDay == 'yes' || ! TribeDateUtils::dateOnly( $_EventStartDate ) ) ? 'checked="checked"' : ''; // default is all day for new posts
		$startMinuteOptions   = TribeEventsViewHelpers::getMinuteOptions( $_EventStartDate, true );
		$endMinuteOptions     = TribeEventsViewHelpers::getMinuteOptions( $_EventEndDate );
		$startHourOptions     = TribeEventsViewHelpers::getHourOptions( $_EventAllDay == 'yes' ? null : $_EventStartDate, true );
		$endHourOptions       = TribeEventsViewHelpers::getHourOptions( $_EventAllDay == 'yes' ? null : $_EventEndDate );
		$startMeridianOptions = TribeEventsViewHelpers::getMeridianOptions( $_EventStartDate, true );
		$endMeridianOptions   = TribeEventsViewHelpers::getMeridianOptions( $_EventEndDate );

		if ( $_EventStartDate ) {
			$start = TribeDateUtils::dateOnly( $_EventStartDate );
		}

		$EventStartDate = ( isset( $start ) && $start ) ? $start : date( 'Y-m-d' );

		if ( ! empty( $_REQUEST['eventDate'] ) ) {
			$EventStartDate = esc_attr( $_REQUEST['eventDate'] );
		}

		if ( $_EventEndDate ) {
			$end = TribeDateUtils::dateOnly( $_EventEndDate );
		}

		$EventEndDate = ( isset( $end ) && $end ) ? $end : date( 'Y-m-d' );
		$recStart     = isset( $_REQUEST['event_start'] ) ? esc_attr( $_REQUEST['event_start'] ) : null;
		$recPost      = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : null;


		if ( ! empty( $_REQUEST['eventDate'] ) ) {
			$duration     = get_post_meta( $postId, '_EventDuration', true );
			$start_time   = isset( $_EventStartDate ) ? TribeDateUtils::timeOnly( $_EventStartDate ) : TribeDateUtils::timeOnly( tribe_get_start_date( $post->ID ) );
			$EventEndDate = TribeDateUtils::dateOnly( strtotime( $_REQUEST['eventDate'] . ' ' . $start_time ) + $duration, true );
		}

		$events_meta_box_template = $tec->pluginPath . 'admin-views/events-meta-box.php';
		$events_meta_box_template = apply_filters( 'tribe_events_meta_box_template', $events_meta_box_template );

		include( $events_meta_box_template );
	}
}