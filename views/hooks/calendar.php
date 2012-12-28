<?php
/**
 * @for Calendar Template
 * This file contains the hook logic required to create an effective calendar month view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */
 
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Calendar_Template')){
	class Tribe_Events_Calendar_Template extends Tribe_Template_Factory {
		public static function init(){

			Tribe_Template_Factory::asset_package( 'ajax-calendar' );

			// Start calendar template
			add_filter( 'tribe_events_calendar_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Calendar title
			add_filter( 'tribe_events_calendar_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_calendar_the_title', array( __CLASS__, 'the_title' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// Calendar header
			add_filter( 'tribe_events_calendar_before_header', array( __CLASS__, 'before_header' ), 1, 1 );
			
			// Calendar navigation
			add_filter( 'tribe_events_calendar_before_nav', array( __CLASS__, 'before_nav' ), 1, 1 );
			add_filter( 'tribe_events_calendar_nav', array( __CLASS__, 'navigation' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_nav', array( __CLASS__, 'after_nav' ), 1, 1 );
			
			add_filter( 'tribe_events_calendar_after_header', array( __CLASS__, 'after_header' ), 1, 1 );

			// Calendar notices
			add_filter( 'tribe_events_calendar_notices', array( __CLASS__, 'notices' ), 1, 1 );

			// Calendar content
			add_filter( 'tribe_events_calendar_before_the_grid', array( __CLASS__, 'before_the_grid' ), 1, 1 );
			add_filter( 'tribe_events_calendar_the_grid', array( __CLASS__, 'the_grid' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_the_grid', array( __CLASS__, 'after_the_grid' ), 1, 1 );

			// End calendar template
			add_filter( 'tribe_events_calendar_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		// Start Calendar Template
		public static function before_template(){
			$html = '<div id="tribe-events-content" class="tribe-events-calendar">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_template');
		}
		// Calendar Title
		public static function before_the_title(){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_the_title');
		}
		public static function the_title(){			
			$html = sprintf( '<h2 class="tribe-events-page-title">%s</h2>',
				date( "F Y", strtotime( tribe_get_month_view_date() ))
				);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_the_title');
		}
		public static function after_the_title(){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_the_title');
		}
		// Notices
		public static function notices(){
			$html = '';
			if(!empty($notices))	
				$html .= '<div class="event-notices">' . implode('<br />', $notices) . '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_notices');
		}
		// Calendar Header
		public static function before_header(){
			$html = '<div id="tribe-events-header" data-title="' . wp_title( '&raquo;', false ) . '" data-date="'. date( 'Y-m', strtotime( tribe_get_month_view_date() ) ) .'">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_header');
		}
		// Calendar Navigation
		public static function before_nav(){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Calendar Month Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_nav');
		}
		public static function navigation(){
			$tribe_ecp = TribeEvents::instance();

			$html = '<li class="tribe-events-nav-prev">';
			$html .= '<a data-month="'. $tribe_ecp->previousMonth( tribe_get_month_view_date() ) .'" href="' . tribe_get_previous_month_link() . '" rel="prev">&#x2190; '. tribe_get_previous_month_text() .' </a>';
			$html .= '</li><!-- .tribe-events-prev-next -->';
			
			$html .= '<li class="tribe-events-nav-date">';
			ob_start();
			tribe_month_year_dropdowns( "tribe-events-" );
			$html .= ob_get_clean();
			$html .= '</li><!-- .tribe-events-nav-date -->';
	
			$html .= '<li class="tribe-events-nav-next">';
			$html .= '<a data-month="'. $tribe_ecp->nextMonth( tribe_get_month_view_date() ) .'" href="' . tribe_get_next_month_link() . '" rel="next"> '. tribe_get_next_month_text() .' &#x2192;</a>';
			$html .= '<img src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" class="ajax-loading" id="ajax-loading" alt="Loading events" />';
			$html .= '</li><!-- .tribe-events-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_nav');
		}
		public static function after_nav(){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_nav');
		}
		public static function after_header(){
			$html = '</div><!-- #tribe-events-header -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_header');
		}
		// Calendar GRID
		public static function before_the_grid(){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_the_grid');
		}
		public static function the_grid(){
			global $wp_query;
			ob_start();

			$tribe_ecp = TribeEvents::instance();
			if ( isset( $_REQUEST["eventDate"] ) && $_REQUEST["eventDate"] ) {
				$tribe_ecp->date = $_REQUEST["eventDate"] . '-01';
			} else if ( !empty( $wp_query->query_vars['eventDate'] ) ) {
				$tribe_ecp->date = $wp_query->query_vars['eventDate'];
			}

			// get all upcoming ids to hide so we're not querying 31 times
			$hide_upcoming_ids = TribeEventsQuery::getHideFromUpcomingEvents();

			list( $year, $month ) = explode( '-', $tribe_ecp->date );
			$date = mktime( 12, 0, 0, $month, 1, $year ); // 1st day of month as unix stamp


			$posts_per_page_limit = 3;
			$daysInMonth = isset( $date ) ? date( 't', $date ) : date( 't' );
			$startOfWeek = get_option( 'start_of_week', 0 );
			$rawOffset = date( 'w', $date ) - $startOfWeek;
			$offset = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
			$rows = 1;
			$count_args = array(
				'hide_upcoming_ids' => $hide_upcoming_ids,
				'start_date' => date('Y-m-d', $date) . ' 00:00:00',
				'end_date' => date('Y-m-t', $date) . ' 23:59:59'
				);
			$event_daily_counts = TribeEventsQuery::getEventCounts( $count_args );
			// print_r($event_daily_counts);
			// $monthView = tribe_sort_by_month( $eventPosts, $tribe_ecp->date );
?>
			<table class="tribe-events-calendar">
				<thead>
					<tr>
					<?php
						for( $n = $startOfWeek; $n < count( $tribe_ecp->daysOfWeek ) + $startOfWeek; $n++ ) {
							$dayOfWeek = ( $n >= 7 ) ? $n - 7 : $n;
							echo '<th id="tribe-events-' . strtolower( $tribe_ecp->daysOfWeek[$dayOfWeek] ) . '" title="' . $tribe_ecp->daysOfWeek[$dayOfWeek] . '">' . $tribe_ecp->daysOfWeekShort[$dayOfWeek] . '</th>';
					} ?>
					</tr>
				</thead>

				<tbody class="hfeed vcalendar">
					<tr>
					<?php // Skip last month
						for( $i = 1; $i <= $offset; $i++ ) { 
							echo '<td class="tribe-events-othermonth"></td>';
						}
						// Output this month
         				$days_in_month = date( 't', intval($date) );
						for( $day = 1; $day <= $days_in_month; $day++ ) {

							$column = $day + $offset - 1 - ( 7 * ( $rows - 1 ) ) ;

							if( ( $day + $offset - 1 ) % 7 == 0 && $day != 1 ) {
			        			echo "</tr>\n\t<tr>";
			        			$rows++;
			    			}

							// Var'ng up days, months and years
							$current_day = date_i18n( 'd' );
							$current_month = date_i18n( 'm' );
							$current_year = date_i18n( 'Y' );
            				$date = date( 'Y-m-d', strtotime("$year-$month-$day"));

							$ppf = '';

							if ( $current_month == $month && $current_year == $year) {
								// Past, Present, Future class
								if ( $current_day == $day ) {
									$ppf = ' tribe-events-present';
								} elseif ( $current_day > $day ) {
									$ppf = ' tribe-events-past';
								} elseif ( $current_day < $day ) {
									$ppf = ' tribe-events-future';
								}
							} elseif ( $current_month > $month && $current_year == $year || $current_year > $year ) {
								$ppf = ' tribe-events-past';
							} elseif ( $current_month < $month && $current_year == $year || $current_year < $year ) {
								$ppf = ' tribe-events-future';
							}

							if ( ( $column % 4 == 0 ) || ( $column % 5 == 0 ) || ( $column % 6 == 0 ) ) {
								$ppf .= ' tribe-events-right';
							}
							
							// You can find tribe_the_display_day() & tribe_get_display_day_title() in
							// /public/template-tags/calendar.php
							// This controls the markup for the days and events on the frontend
				
			    			echo "<td class=\"tribe-events-thismonth". $ppf ."\">"."\n"; //. tribe_get_display_day_title( $day, $monthView, $date ) ."\n";
			    			printf( '<div id="%s"><a href="%s">%s</a></div>',
			    				'tribe-events-daynum-' . $day,
			    				'/' . TribeEvents::getOption( 'eventsSlug', 'events' ) . '/' . date( 'Y-m-d', strtotime( $date )),
			    				$day
			    				);


			    			$args = wp_parse_args(array(
			    				'eventDate' => $date,
			    				'start_date' => tribe_event_beginning_of_day( $date ),
			    				'end_date' => tribe_event_end_of_day( $date ),
			    				// setup our own custom hide upcoming
			    				'post__not_in' => $hide_upcoming_ids, 
			    				'hide_upcoming' => false,
			    				'posts_per_page' => $posts_per_page_limit,
			    				'orderby' => 'event_date',
								'order' => 'ASC',
			    				'eventDisplay' => 'custom',
			    				'no_found_rows' => true
			    				), $wp_query->query);

			    			if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
								$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
								$args['eventCat'] = (int) $cat->term_id;
							}

			    			$daily_events = TribeEventsQuery::getEvents( $args, true );
			    			// print_r( $daily_events);
							foreach( $daily_events->posts as $post ) {

								// setup_postdata( $post );
								$eventId	= $post->ID.'-'.$day;
								$start		= tribe_get_start_date( $post, false, 'U' );
								$end		= tribe_get_end_date( $post, false, 'U' );
								$cost		= tribe_get_cost( $post->ID );			
								?>
								
								<?php			
								// Get our wrapper classes (for event categories, organizer, venue, and defaults)
								$tribe_string_classes = '';
								$tribe_cat_ids = tribe_get_event_cat_ids( $post->ID ); 
								foreach( $tribe_cat_ids as $tribe_cat_id ) { 
									$tribe_string_classes .= 'tribe-events-category-'. $tribe_cat_id .' '; 
								}
								$tribe_string_wp_classes = '';
								$allClasses = get_post_class(); 
								foreach ($allClasses as $class) { 
									$tribe_string_wp_classes .= $class . ' '; 
								}
								$tribe_classes_default = 'hentry vevent '. $tribe_string_wp_classes;
								$tribe_classes_venue = tribe_get_venue_id() ? 'tribe-events-venue-'. tribe_get_venue_id() : '';
								$tribe_classes_organizer = tribe_get_organizer_id() ? 'tribe-events-organizer-'. tribe_get_organizer_id() : '';
								$tribe_classes_categories = $tribe_string_classes;
								$class_string = $tribe_classes_default .' '. $tribe_classes_venue .' '. $tribe_classes_organizer .' '. $tribe_classes_categories;

								// added last class for css
								if( $i+1 == count( $daily_events ) ){
									$class_string .= ' tribe-last';
								}

								?>
								
								<div id="tribe-events-event-<?php echo $eventId; ?>" class="<?php echo $class_string; ?>">
									<h3 class="entry-title summary"><a href="<?php tribe_event_link( $post ); ?>"><?php echo $post->post_title; ?></a></h3>
									<div id="tribe-events-tooltip-<?php echo $eventId; ?>" class="tribe-events-tooltip">
										<h4 class="entry-title summary"><?php echo $post->post_title;?></h4>
										<div class="tribe-events-event-body">
											<div class="duration">
												<abbr class="tribe-events-abbr updated published dtstart" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), $start ); ?>">
												<?php if ( !empty( $start ) )	echo date_i18n( get_option( 'date_format', 'F j, Y' ), $start );
												if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
													echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), $start ); ?>
												</abbr><!-- .dtstart -->
												<abbr class="tribe-events-abbr dtend" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), $end ); ?>">
												<?php if ( !empty( $end )  && $start !== $end ) {
													if ( date_i18n( 'Y-m-d', $start ) == date_i18n( 'Y-m-d', $end ) ) {
														$time_format = get_option( 'time_format', 'g:i a' );
														if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
															echo " – " . date_i18n( $time_format, $end );
													} else {
														echo " – " . date_i18n( get_option( 'date_format', 'F j, Y' ), $end );
														if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
														 	echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), $end ) . '<br />';
													}
												} ?>
												</abbr><!-- .dtend -->
											</div><!-- .duration -->
											
											<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail() ) { ?>
												<div class="tribe-events-event-thumb"><?php the_post_thumbnail( array( 75,75 ) );?></div>
											<?php } ?>
											
											<p class="entry-summary description"><?php echo has_excerpt() ? TribeEvents::truncate( $post->post_excerpt ) : TribeEvents::truncate( get_the_content(), 30 ); ?></p>

										</div><!-- .tribe-events-event-body -->
										<span class="tribe-events-arrow"></span>
									</div><!-- .tribe-events-tooltip -->
								</div><!-- #tribe-events-event-# -->
								<?php


							}

							// $remaining_not_shown = !empty($daily_events->found_posts) && $daily_events->found_posts > 0 ? 
							// 	$daily_events->found_posts - $posts_per_page_limit : 
							// 	0;
							if( !empty($event_daily_counts[$date]) && (int) $event_daily_counts[$date] > $posts_per_page_limit ) {
								printf( '<div class="viewmore vevent"><a href="%s">View %d More Events &raquo;</a></div>',
									tribe_get_day_link( $date ),
									$event_daily_counts[$date]
									);
							}
								

			    			// echo $date;
							// tribe_the_display_day( $day, $daily_events );
							echo '</td>';
						}
						// Skip next month
						while( ( $day + $offset ) <= $rows * 7 ) {
			    			echo '<td class="tribe-events-othermonth"></td>';
			    			$day++;
						}
					?>
					</tr>
				</tbody><!-- .hfeed -->
			</table><!-- .tribe-events-calendar -->
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_the_grid');
		}
		public static function after_the_grid(){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_the_grid');
		}
		// End Calendar Template
		public static function after_template(){
			$html = '';
			if( function_exists( 'tribe_get_ical_link' ) )
				$html .= '<a class="tribe-events-ical tribe-events-button-grey" title="'. __( 'iCal Import', 'tribe-events-calendar' ) .'" href="'. tribe_get_ical_link() .'">'. __( 'iCal Import', 'tribe-events-calendar' ) .'</a>';
				
			if ( tribe_get_option( 'donate-link', false ) == true )
				$html .= '<p class="tribe-events-promo">' . apply_filters( 'tribe_promo_banner', sprintf( __( 'Calendar powered by %sThe Events Calendar%s', 'tribe-events-calendar' ), '<a class="vcard url org fn" href="http://tri.be/wordpress-events-calendar/">', '</a>' ) ) . '</p>';
			$html .= '</div><!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_template');
		}
	}
	Tribe_Events_Calendar_Template::init();
}
