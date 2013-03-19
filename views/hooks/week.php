<?php
/**
 *
 *
 * @for Week Grid Template
 * This file contains the hook logic required to create an effective week grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( !class_exists( 'Tribe_Events_Week_Template' ) ) {
	class Tribe_Events_Week_Template extends Tribe_Template_Factory {

		public static function init() {
			global $wp_query;
			// enqueue needed styles
			Tribe_PRO_Template_Factory::asset_package( 'ajax-weekview' );

			// Start week template
			add_filter( 'tribe_events_week_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			add_filter( 'tribe_events_week_the_title', array( __CLASS__, 'the_title' ), 1, 1 );
			
			// Week notices
			add_filter( 'tribe_events_week_notices', array( __CLASS__, 'notices' ), 1, 1 );

			// Week header
			add_filter( 'tribe_events_week_before_header', array( __CLASS__, 'before_header' ), 1, 1 );
			
			// Navigation
			add_filter( 'tribe_events_week_before_header_nav', array( __CLASS__, 'before_header_nav' ), 1, 1 );
			add_filter( 'tribe_events_week_header_nav', array( __CLASS__, 'header_navigation' ), 1, 1 );
			add_filter( 'tribe_events_week_after_header_nav', array( __CLASS__, 'after_header_nav' ), 1, 1 );
			
			add_filter( 'tribe_events_week_after_header', array( __CLASS__, 'after_header' ), 1, 1 );

			// Start week loop
			add_filter( 'tribe_events_week_before_loop', array( __CLASS__, 'before_loop' ), 1, 1 );
			add_filter( 'tribe_events_week_inside_before_loop', array( __CLASS__, 'inside_before_loop' ), 1, 1 );

			add_filter( 'tribe_events_week_the_grid', array( __CLASS__, 'the_grid' ), 1, 1 );

			// End week loop
			add_filter( 'tribe_events_week_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 1, 1 );
			add_filter( 'tribe_events_week_after_loop', array( __CLASS__, 'after_loop' ), 1, 1 );
			
			// Week footer
			add_filter( 'tribe_events_week_before_footer', array( __CLASS__, 'before_footer' ), 1, 1 );
			
			// Navigation
			add_filter( 'tribe_events_week_before_footer_nav', array( __CLASS__, 'before_footer_nav' ), 1, 1 );
			add_filter( 'tribe_events_week_footer_nav', array( __CLASS__, 'footer_navigation' ), 1, 1 );
			add_filter( 'tribe_events_week_after_footer_nav', array( __CLASS__, 'after_footer_nav' ), 1, 1 );
			
			add_filter( 'tribe_events_week_after_footer', array( __CLASS__, 'after_footer' ), 1, 1 );

			// End week template
			add_filter( 'tribe_events_week_after_template', array( __CLASS__, 'after_template' ), 1, 2 );

			if( !empty( $wp_query->query_vars['s'] )){
				$search_term = $wp_query->query_vars['s'];
			} else if( !empty($_POST['tribe-bar-search'])) {
				$search_term = $_POST['tribe-bar-search'];
			}

			if( !empty( $search_term ) && !have_posts() ) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There were no results found for <strong>"%s"</strong> this week. Try searching another week.', 'tribe-events-calendar' ), $search_term ) );
			}				
		}
		// Start Week Template
		public static function before_template( $post_id ) {
			ob_start();
			// This title is here for ajax loading – do not remove if you want ajax switching between month views
?>
			<div id="tribe-events-content" class="tribe-events-week-grid">
			<?php
			$html = ob_get_clean();
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_before_template' );
		}
		// Week Title
		public static function the_title() {
			global $wp_query;

			// because we can't trust tribe_get_events_title will be set when run via AJAX
			$title = sprintf( __( 'week of %s', 'tribe-events-calendar-pro' ),
				date( "l, F jS Y", strtotime( tribe_get_first_week_day( $wp_query->get( 'start_date' ) ) ) )
			);

			$html = sprintf( '<h2 class="tribe-events-page-title">'. __( 'Events for ', 'tribe-events-calendar-pro' ) .'%s</h2>',
				$title
			);

			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_the_title' );
		}	
		// Notices
		public static function notices( $post_id ){
			$html = tribe_events_the_notices(false);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_notices');
		}			
		// Week Header
		public static function before_header( $post_id ){
			global $wp_query;
			$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );
		
			$html = '<div id="tribe-events-header" data-title="' . wp_title( '&raquo;', false ) . '" data-date="'. $current_week .'">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_header');
		}
		// Week Navigation
		public static function before_header_nav( $post_id ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Week Navigation', 'tribe-events-calendar-pro' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_header_nav');
		}
		public static function header_navigation( $post_id ){
			$tribe_ecp = TribeEvents::instance();
			global $wp_query;
			$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );

			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous"><a data-week="'. date( 'Y-m-d', strtotime( $current_week . ' -7 days' ) ) .'" href="'. tribe_get_last_week_permalink( $current_week ) .'" rel="prev">&larr; '. __( 'Previous Week', 'tribe-events-calendar-pro' ) .'</a></li><!-- .tribe-nav-previous -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next"><a data-week="'. date( 'Y-m-d', strtotime( $current_week . ' +7 days' ) ) .'" href="'. tribe_get_next_week_permalink( $current_week ) .'" rel="next">'. __( 'Next Week', 'tribe-events-calendar-pro' ) .' &rarr;</a>';
			
			// Loading spinner
			$html .= '<img class="tribe-ajax-loading tribe-spinner-medium" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_header_nav');
		}
		public static function after_header_nav( $post_id ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_header_nav');
		}
		public static function after_header( $post_id ){
			$html = '</div><!-- #tribe-events-header -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_header');
		}				
		// Start Week Loop
		public static function before_loop( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_before_loop' );
		}
		public static function inside_before_loop( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_inside_before_loop' );
		}

		// Week Grid
		public static function the_grid() {

			global $wp_query;
			$tribe_ecp = TribeEvents::instance();
			$start_of_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );

			// convert the start of the week into a timestamp
			$start_of_weektime = strtotime( $start_of_week );

			$week_length = 7; // days of the week
			$today = date( 'Y-m-d', strtotime( 'today' ) );
			$events = (object) array( 'all_day' => array(), 'daily' => array(), 'hours' => array( 'start'=>null, 'end'=>null ) );
			$all_day_events = array();

			// get it started off with at least 1 row
			$all_day_events[] = array_fill(1, $week_length, null);

			// loop through all found events
			foreach ( $wp_query->posts as $event_id => $event ) {
				
				// convert the start date of the event into a timestamp
				$event_start_time = strtotime($event->EventStartDate);

				// if the event start time is greater than the start time of the week then we use the event date otherwise use the beginning of the week date
				$start_date_compare = $start_of_weektime < $event_start_time ? $event->EventStartDate : $start_of_week;

				// convert the starting event or week date into day of the week
				$event_start_day_of_week = date('w', strtotime($start_date_compare) );

				// determine the number of days between the starting date and the end of the event
				$event->days_between = tribe_get_days_between( $start_date_compare, $event->EventEndDate );

				// make sure that our days between don't extend past the end of the week
				$event->days_between = $event->days_between >= $week_length - $event_start_day_of_week ? ( $week_length - $event_start_day_of_week ) : (int) $event->days_between;

				// if this is an all day event
				if (  tribe_get_event_meta( $event->ID, '_EventAllDay' ) ) {

					// let's build our hashtable for add day events
					foreach( $all_day_events as $hash_id => $days) {

						// set bool for if we should inset the event id on the current hash row
						$insert_current_row = false;

						// loop through the columns of this hash row
						for( $n = $event_start_day_of_week; $n <= $event_start_day_of_week + $event->days_between; $n++){

							// check for hash collision and setup bool for going to the next row if we can't fit it on this row
							if( ! empty($all_day_events[$hash_id][$n]) ) {
								$insert_current_row = true;
								break;
							} else {
								$insert_current_row = false;
							}
						}
						// if we should actually insert a new row vs going to the next row 
						if( $insert_current_row && count($all_day_events) == $hash_id + 1 ){

							// create a new row and fill with week day columns
							$all_day_events[] = array_fill(1, $week_length, null);

							// change the row id to the last row
							$hash_id = count($all_day_events) -1;

						} else if( $insert_current_row ) {

							// nullify the hash id
							$hash_id = null;
						}

						// if we still have a hash id then fill the row with the event id
						if( ! is_null($hash_id) ) {

							// loop through each week day we want the event to be inserted
							for( $n = $event_start_day_of_week; $n <= $event_start_day_of_week + $event->days_between; $n++){

								// add the event id into the week day column
								$all_day_events[$hash_id][$n] = $event->ID;
							}

							// break the hashtable since we have successfully added the event into a row
							break;
						}
					}
					
					$events->all_day[ $event->ID ] = $event;
				} else {
					$start_hour = date( 'G', strtotime( $event->EventStartDate ) );
					$end_hour = date( 'G', strtotime( $event->EventEndDate ) );
					if ( is_null( $events->hours['start'] ) || $start_hour < $events->hours['start'] ) {
						$events->hours['start'] = $start_hour;
					}
					if ( is_null( $events->hours['end'] ) || $end_hour > $events->hours['end'] ) {
						$events->hours['end'] = $end_hour;
					}
					$events->daily[] = $event;
				}
			}

			ob_start();
?>
<div class="tribe-events-grid clearfix">

	<?php // Header "Row" ?>
	<div class="tribe-grid-header clearfix">

		<div class="column first">
			<span class="tribe-events-visuallyhidden"><?php _e( 'Hours', 'tribe-events-calendar-pro' ); ?></span>
		</div>

		<div class="tribe-grid-content-wrap">

			<?php
			for ( $n = 0; $n < $week_length; $n++ ) {
				$day = date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) );
				$header_class = ( $day == $today ) ? 'tribe-week-today' : '';
				printf( '<div title="%s" class="column %s"><a href="%s" rel="bookmark">%s</a></div><!-- header column -->',
					$day,
					$header_class,
					trailingslashit( get_site_url() ) . trailingslashit( $tribe_ecp->rewriteSlug ) . trailingslashit( date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) ) ),
					date( 'D jS', strtotime( $start_of_week . " +$n days" ) )
				);
			} ?>

		</div><!-- .tribe-grid-content-wrap -->

	</div><!-- .tribe-grid-header -->

	<?php // All Day "Row" ?>	
		
		<div class="tribe-grid-allday clearfix">

			<div class="column first"><?php _e( '<span>All Day</span>', 'tribe-events-calendar-pro' ); ?></div>

			<div class="tribe-grid-content-wrap">

				<?php
				$placeholder_html = '<div class="tribe-event-placeholder hentry vevent" data-event-id="%s">&nbsp;</div>';
				$all_day_span_ids = array();
				for ( $n = 1; $n <= $week_length; $n++ ) {
					$day = date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) );
					$header_class = ( $day == $today ) ? ' tribe-week-today' : '';
					$right_align = ( $n != 0 && ( ( $n % 4 == 0 ) || ( $n % 5 == 0 ) || ( $n % 6 == 0 ) ) ) ? ' tribe-events-right' : '';
					printf( '<div title="%s" class="column%s%s">', 
						date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) ), 
						$header_class, 
						$right_align );

					foreach( $all_day_events as $all_day_cols ) {
						$event_id = $all_day_cols[ $n ];
						if( is_null( $event_id ) ){
							printf( $placeholder_html, 0 );
						} else {
							$event = $events->all_day[ $event_id ];

							// check if the event has already been shown - if so then dump in a span placeholder
							if( in_array( $event->ID, $all_day_span_ids)){
								printf( $placeholder_html,
									$event->ID
									);
							} else {
								$all_day_span_ids[] = $event->ID;
								$day_span_length = $event->days_between + 1; // we need to adjust on behalf of weekly span scripts
								$span_class = $day_span_length > 0 ? 'tribe-dayspan' . $day_span_length : '';
								// Get our wrapper classes (for event categories, organizer, venue, and defaults)
								$classes = array( 'hentry', 'vevent', $span_class, 'type-tribe_events', 'post-' . $event->ID, 'tribe-clearfix' );
								$tribe_cat_ids = tribe_get_event_cat_ids( $event->ID );
								foreach( $tribe_cat_ids as $tribe_cat_id ) {
									$classes[] = 'tribe-events-category-'. $tribe_cat_id;
								}
								if ( $venue_id = tribe_get_venue_id( $event->ID ) ) {
									$classes[] = 'tribe-events-venue-'.$venue_id;
								}
								if ( $organizer_id = tribe_get_organizer_id( $event->ID ) ) {
									$classes[] = 'tribe-events-organizer-'.$organizer_id;
								}
								$class_string = implode(' ', $classes);
								printf( '<div id="tribe-events-event-'. $event->ID .'" class="%s" data-hour="all-day"><div><h3 class="entry-title summary"><a href="%s" class="url" rel="bookmark">%s</a></h3>',
									$class_string,
									get_permalink( $event->ID ),
									$event->post_title
								); ?>

								<div id="tribe-events-tooltip-<?php echo $event->ID; ?>" class="tribe-events-tooltip">
									<h4 class="entry-title summary"><?php echo $event->post_title; ?></h4>
									<div class="tribe-events-event-body">
										<div class="duration">
											<abbr class="tribe-events-abbr updated published dtstart" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime( $event->EventStartDate ) ); ?>">
												<?php if ( !empty( $event->EventStartDate ) )	
													echo date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $event->EventStartDate ) );
													if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
														echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), strtotime( $event->EventStartDate ) ); ?>
											</abbr><!-- .dtstart -->
											<abbr class="tribe-events-abbr dtend" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime( $event->EventEndDate ) ); ?>">
												<?php if ( !empty( $event->EventEndDate ) && $event->EventStartDate !== $event->EventEndDate ) {
													if ( date_i18n( 'Y-m-d', strtotime($event->EventStartDate) ) == date_i18n( 'Y-m-d', strtotime($event->EventEndDate) ) ) {
														$time_format = get_option( 'time_format', 'g:i a' );
														if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
															echo " – " . date_i18n( $time_format, strtotime( $event->EventEndDate ) );
														} else {
															echo " – " . date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $event->EventEndDate ) );
															if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
																echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), strtotime( $event->EventEndDate ) ) . '<br />';
														}
													} ?>
											</abbr><!-- .dtend -->
										</div><!-- .duration -->

										<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $event->ID ) ) { ?>
											<div class="tribe-events-event-thumb "><?php echo get_the_post_thumbnail( $event->ID, array( 75, 75 ) );?></div>
										<?php } ?>

										<p class="entry-summary description">
										<?php if( has_excerpt( $event->ID ) ) {
											echo TribeEvents::truncate( $event->post_excerpt, 30 );
										} else {
											echo TribeEvents::truncate( $event->post_content, 30 );
										} ?>
										</p><!-- .entry-summary -->

									</div><!-- .tribe-events-event-body -->
									<span class="tribe-events-arrow"></span>
								</div><!-- .tribe-events-tooltip -->
								<?php
								echo '</div></div>';
							}

						}
					}
					
					echo '</div><!-- allday column -->';
				} ?>

			</div><!-- .tribe-grid-content-wrap -->

		</div><!-- .tribe-grid-allday -->
<div class="tribe-week-grid-wrapper">
		<?php // Grid "Rows" ?>
		<div class="tribe-week-grid-outer-wrap tribe-clearfix">
			<div class="tribe-week-grid-inner-wrap">
				<?php	
				for ( $hour = 0; $hour <= 23; $hour++ ) {
					echo '<div class="tribe-week-grid-block" data-hour="' . $hour . '"><div></div></div>';
				}

	?>
			</div><!-- .tribe-week-grid-inner-wrap -->
		</div><!-- .tribe-week-grid-outer-wrap -->

		<?php // Content / Events "Rows" ?>
		<!-- Days of the week & hours & events -->
		<div class="tribe-grid-body clearfix">

			<?php // Hours ?>
			<div class="column tribe-week-grid-hours">
				<?php
				// for ( $hour = $events->hours['start']; $hour <= $events->hours['end']; $hour++ ) {
				for ( $hour = 0; $hour <= 23; $hour++ ) {
					 // if( strpos(get_option('time_format'), 'g') !== false ) {
							printf( '<div class="time-row-%1$s">%1$s</div>', date( 'gA', mktime( $hour ) ) );
						// } else {
							// printf( '<div>%s</div>', date( 'H', mktime( $hour ) ) );
						// }	
				}

	?>
			</div><!-- tribe-week-grid-hours -->
			<?php // Content ?>
			<div class="tribe-grid-content-wrap">

				<?php // Our day columns?
				$daily_span_ids = array();
				for ( $n = 0; $n < $week_length; $n++ ) {
					$day = date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) );
					$header_class = ( $day == $today ) ? ' tribe-week-today' : '';
					$right_align = ( $n != 0 && ( ( $n % 4 == 0 ) || ( $n % 5 == 0 ) || ( $n % 6 == 0 ) ) ) ? ' tribe-events-right' : '';
					printf( '<div title="%s" class="column hfeed vcalendar%s%s">',
						date( 'Y-m-d', strtotime( $start_of_week . " +$n days" ) ),
						$header_class,
						$right_align
					);
					$prior_event_date = (object) array('EventStartDate'=>null,'EventEndDate'=>null);
					foreach ( $events->daily as $event ) {
						if ( date( 'Y-m-d', strtotime( $event->EventStartDate ) ) <= $day && date( 'Y-m-d', strtotime( $event->EventEndDate ) ) >= $day ) {
							if( $event->days_between > 0 ) {
								$daily_mins = 1440;
								$data_hour = 0;
								$data_min = 0;
								if( in_array( $event->ID, $daily_span_ids) && date( 'Y-m-d', strtotime( $event->EventEndDate ) ) == $day ){
									// if the event is longer than a day we want to account for that with an offset for the ending time
									$duration = abs( (strtotime($day) - strtotime( $event->EventEndDate ) ) / 60 );
								} else if( in_array( $event->ID, $daily_span_ids) && date( 'Y-m-d', strtotime( $event->EventEndDate ) ) > $day ){
									// if there is a day in between start/end we just want to fill the spacer with the total mins in the day.
									$duration = $daily_mins;
								} else {
									$daily_span_ids[] = $event->ID;
									// if the event is longer than a day we want to account for that with an offset
									$duration = $daily_mins - abs( (strtotime($day) - strtotime( $event->EventStartDate ) ) / 60 );
									$data_hour = date( 'G', strtotime( $event->EventStartDate ) );
									$data_min = date( 'i', strtotime( $event->EventStartDate ) );
								}
							} else {
								// for a default event continue as everything is normal
								$duration = ( $event->EventDuration / 60 );
								$data_hour = date( 'G', strtotime( $event->EventStartDate ) );
								$data_min = date( 'i', strtotime( $event->EventStartDate ) );
							}
							// Get our wrapper classes (for event categories, organizer, venue, and defaults)
							$classes = array( 'hentry', 'vevent', 'type-tribe_events', 'post-' . $event->ID, 'tribe-clearfix' );
							$tribe_cat_ids = tribe_get_event_cat_ids( $event->ID );
							foreach( $tribe_cat_ids as $tribe_cat_id ) {
								$classes[] = 'tribe-events-category-'. $tribe_cat_id;
							}
							if ( $venue_id = tribe_get_venue_id( $event->ID ) ) {
								$classes[] = 'tribe-events-venue-'.$venue_id;
							}
							if ( $organizer_id = tribe_get_organizer_id( $event->ID ) ) {
								$classes[] = 'tribe-events-organizer-'.$organizer_id;
							}
							if( strtotime( $prior_event_date->EventStartDate ) < strtotime( $event->EventStartDate ) ) {
								$classes[] = 'tribe-event-overlap';
							}
							$class_string = implode(' ', $classes);
							// echo '<div id="tribe-events-event-'. $event->ID .'" duration="'. round( $duration ) .'" data-hour="' . $data_hour . '" data-min="' . $data_min . '">';
							printf( '<div id="tribe-events-event-%s" duration="%s" data-hour="%s" data-min="%s" class="%s"><div class="hentry vevent"><h3 class="entry-title summary"><a href="%s" class="url" rel="bookmark">%s</a></h3></div>',
								$event->ID,
								round( $duration ),
								$data_hour,
								$data_min,
								$class_string,
								get_permalink( $event->ID ),
								$event->post_title
							); ?>

							<div id="tribe-events-tooltip-<?php echo $event->ID; ?>" class="tribe-events-tooltip">
								<h4 class="entry-title summary"><?php echo $event->post_title; ?></h4>
								<div class="tribe-events-event-body">
									<div class="duration">
										<abbr class="tribe-events-abbr updated published dtstart" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime( $event->EventStartDate ) ); ?>">
											<?php if ( !empty( $event->EventStartDate ) )	
												echo date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $event->EventStartDate ) );
												if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
													echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), strtotime( $event->EventStartDate ) ); ?>
										</abbr><!-- .dtstart -->
										<abbr class="tribe-events-abbr dtend" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime($event->EventEndDate) ); ?>">
											<?php if ( !empty( $event->EventEndDate ) && $event->EventStartDate !== $event->EventEndDate ) {
												if ( date_i18n( 'Y-m-d', strtotime($event->EventStartDate) ) == date_i18n( 'Y-m-d', strtotime($event->EventEndDate) ) ) {
													$time_format = get_option( 'time_format', 'g:i a' );
													if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
														echo " – " . date_i18n( $time_format, strtotime( $event->EventEndDate ) );
													} else {
														echo " – " . date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $event->EventEndDate ) );
														if ( !tribe_get_event_meta( $event->ID, '_EventAllDay', true ) )
															echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), strtotime( $event->EventEndDate ) ) . '<br />';
													}
												} ?>
										</abbr><!-- .dtend -->
									</div><!-- .duration -->

									<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $event->ID ) ) { ?>
										<div class="tribe-events-event-thumb"><?php echo get_the_post_thumbnail( $event->ID, array( 75, 75 ) );?></div>
									<?php } ?>

									<p class="entry-summary description">
									<?php if( has_excerpt( $event->ID ) ) {
										echo TribeEvents::truncate( $event->post_excerpt, 30 );
									} else {
										echo TribeEvents::truncate( $event->post_content, 30 );
									} ?>
									</p><!-- .entry-summary -->
									
									<span class="tribe-events-arrow"></span>
								</div><!-- .tribe-events-event-body -->
							</div><!-- .tribe-events-tooltip -->
							<?php

							echo '</div><!-- #tribe-events-event-'. $event->ID .' -->';

							$prior_event_date->EventStartDate = $event->EventStartDate;
							$prior_event_date->EventStartDate = $event->EventStartDate;
						}
					}
					echo '</div>';
				} ?>

			</div><!-- .tribe-grid-content-wrap -->

		</div><!-- .tribe-grid-body -->
	
	</div><!-- .tribe-week-grid-wrapper -->

</div><!-- .tribe-events-grid -->

    <?php
			$html = ob_get_clean();
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_the_grid' );
		}

		// End Week Loop
		public static function inside_after_loop( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_inside_after_loop' );
		}
		public static function after_loop( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_after_loop' );
		}
		// Week Footer
		public static function before_footer( $post_id ){
			global $wp_query;
			$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );
		
			$html = '<div id="tribe-events-footer">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_footer');
		}
		// Week Navigation
		public static function before_footer_nav( $post_id ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Week Navigation', 'tribe-events-calendar-pro' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_before_footer_nav');
		}
		public static function footer_navigation( $post_id ){
			$tribe_ecp = TribeEvents::instance();
			global $wp_query;
			$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );

			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous"><a data-week="'. date( 'Y-m-d', strtotime( $current_week . ' -7 days' ) ) .'" href="'. tribe_get_last_week_permalink( $current_week ) .'" rel="prev">&larr; '. __( 'Prev Week', 'tribe-events-calendar-pro' ) .'</a></li><!-- .tribe-nav-previous -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next"><a data-week="'. date( 'Y-m-d', strtotime( $current_week . ' +7 days' ) ) .'" href="'. tribe_get_next_week_permalink( $current_week ) .'" rel="next">'. __( 'Next Week', 'tribe-events-calendar-pro' ) .' &rarr;</a>';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_footer_nav');
		}
		public static function after_footer_nav( $post_id ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_footer_nav');
		}
		public static function after_footer( $post_id ){
			$html = '</div><!-- #tribe-events-footer -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_week_after_footer');
		}
		// End Week Template
		public static function after_template( $post_id ) {
			$html = '';

			// iCal import button
			if ( function_exists( 'tribe_get_ical_link' ) ) {
				$html .= sprintf( '<a class="tribe-events-ical tribe-events-button-grey" title="%s" href="%s">%s</a>',
					esc_attr( 'iCal Import', 'tribe-events-calendar' ),
					tribe_get_ical_link(),
					__( 'iCal Import', 'tribe-events-calendar' )
				);
			}
			$html .= '</div><!-- #tribe-events-content -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_week_after_template' );
		}
	}
	Tribe_Events_Week_Template::init();
}
