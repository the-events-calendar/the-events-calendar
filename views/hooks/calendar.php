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
			// Start calendar template
			add_filter( 'tribe_events_calendar_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Calendar header
			add_filter( 'tribe_events_calendar_before_header', array( __CLASS__, 'before_header' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_header', array( __CLASS__, 'after_header' ), 1, 1 );

			// Calendar title
			add_filter( 'tribe_events_calendar_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_calendar_the_title', array( __CLASS__, 'the_title' ), 1, 2 );
			add_filter( 'tribe_events_calendar_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// Calendar navigation
			add_filter( 'tribe_events_calendar_before_nav', array( __CLASS__, 'before_nav' ), 1, 1 );
			add_filter( 'tribe_events_calendar_nav', array( __CLASS__, 'navigation' ), 1, 2 );
			add_filter( 'tribe_events_calendar_after_nav', array( __CLASS__, 'after_nav' ), 1, 1 );

			// Calendar notices
			add_filter( 'tribe_events_calendar_notices', array( __CLASS__, 'notices' ), 1, 2 );

			// calendar content
			add_filter( 'tribe_events_calendar_before_the_grid', array( __CLASS__, 'before_the_grid' ), 1, 1 );
			add_filter( 'tribe_events_calendar_the_grid', array( __CLASS__, 'the_grid' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_the_grid', array( __CLASS__, 'after_the_grid' ), 1, 1 );

			// End calendar template
			add_filter( 'tribe_events_calendar_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		// Start Calendar Template
		public function before_template( $post_id ){
			$html = '<div id="tribe-events-content" class="tribe-events-calendar">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_template');
		}
		// Calendar Title
		public function before_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_title');
		}
		public function the_title( $title, $post_id ){
			// This title is here for ajax loading – do not remove if you want ajax switching between month views
			$html = '<title>' . wp_title( '&raquo;', false ) . '</title>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_title');
		}
		public function after_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_title');
		}
		// Notices
		public function notices( $notices, $post_id ){
			$html = '';
			if(!empty($notices))	
				$html .= '<div class="event-notices">' . implode('<br />', $notices) . '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_notices');
		}
		// Calendar Header
		public function before_header( $post_id ){
			$html = '<div id="tribe-events-calendar-header" class="clearfix">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_header');
		}
		public function after_header( $post_id ){
			$html = '</div><!-- #tribe-events-calendar-header -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_header');
		}
		// Calendar Navigation
		public function before_nav( $post_id ){
			$html = '<span class="tribe-events-month-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_nav');
		}
		public function navigation( $post_id ){
			$html = '<span class="tribe-events-prev-month">';
			$html .= '<a href="' . tribe_get_previous_month_link() . '"> &#x2190; ' . tribe_get_previous_month_text() . ' </a>';
			$html .= '</span>';

			ob_start();
			tribe_month_year_dropdowns( "tribe-events-" );
			$html .= ob_get_clean();
	
			$html .= '<span class="tribe-events-next-month">';
			$html .= '<a href="' . tribe_get_next_month_link() . '"> ' . tribe_get_next_month_text() . ' &#x2192; </a>';
            $html .= '<img src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" class="ajax-loading" id="ajax-loading" alt="" style="display: none" />';
			$html .= '</span>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_nav');
		}
		public function after_nav( $post_id ){
			$html = '</span><!-- .tribe-events-month-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_nav');
		}
		// Calendar GRID
		public function before_the_grid( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_the_grid');
		}
		public function the_grid( $post_id ){
			global $wp_query;
			ob_start();

			$tribe_ecp = TribeEvents::instance();

			// In an events cat
			if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
				$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
				$eventCat = (int) $cat->term_id;
				$eventPosts = tribe_get_events( array( 'eventCat' => $eventCat, 'time_order' => 'ASC', 'eventDisplay'=>'month' ) );
			} // not in a cat
			else {
				// $eventPosts = tribe_get_events( array( 'eventDisplay'=>'month' ) );
				$eventPosts = $wp_query->posts;
			}

			if(empty($eventPosts))
				return 'NO EVENTS';

			$daysInMonth = isset( $date ) ? date( 't', $date ) : date( 't' );
			$startOfWeek = get_option( 'start_of_week', 0 );
			list( $year, $month ) = split( '-', $tribe_ecp->date );
			$date = mktime( 12, 0, 0, $month, 1, $year ); // 1st day of month as unix stamp
			$rawOffset = date( 'w', $date ) - $startOfWeek;
			$offset = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
			$rows = 1;

			$monthView = tribe_sort_by_month( $eventPosts, $tribe_ecp->date );
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

				<tbody class="hfeed">
					<tr>
					<?php // Skip last month
						for( $i = 1; $i <= $offset; $i++ ) { 
							echo '<td class="tribe-events-othermonth"></td>';
						}
						// Output this month
         				$days_in_month = date( 't', intval($date) );
						for( $day = 1; $day <= $days_in_month; $day++ ) {
			    			if( ( $day + $offset - 1 ) % 7 == 0 && $day != 1 ) {
			        			echo "</tr>\n\t<tr>";
			        			$rows++;
			    			}
			
							// Var'ng up days, months and years
							$current_day = date_i18n( 'd' );
							$current_month = date_i18n( 'm' );
							$current_year = date_i18n( 'Y' );
            				$date = "$year-$month-$day";
				
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
							} else { $ppf = false; }
							
						// You can find tribe_the_display_day() & tribe_get_display_day_title() in
						// /public/template-tags/calendar.php
						// This controls the markup for the days and events on the frontend
				
			    			echo "<td class=\"tribe-events-thismonth". $ppf ."\">". tribe_get_display_day_title( $day, $monthView, $date ) ."\n";
								tribe_the_display_day( $day, $monthView );
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
		public function after_the_grid( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_the_grid');
		}
		// End Calendar Template
		public function after_template( $post_id ){
			$html = '';
			if( function_exists( 'tribe_get_ical_link' ) )
				$html .= '<a class="tribe-events-ical" title="'. __( 'iCal Import', 'tribe-events-calendar' ) .'" href="'. tribe_get_ical_link() .'">'. __( 'iCal Import', 'tribe-events-calendar' ) .'</a>';
				
			if ( tribe_get_option( 'donate-link', false ) == true )
				$html .= '<p class="tribe-events-promo">' . apply_filters( 'tribe_promo_banner', sprintf( __( 'Calendar powered by %sThe Events Calendar%s', 'tribe-events-calendar' ), '<a class="vcard url org fn" href="http://tri.be/wordpress-events-calendar/">', '</a>' ) ) . '</p>';
			$html .= '</div><!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_template');
		}
	}
	Tribe_Events_Calendar_Template::init();
}