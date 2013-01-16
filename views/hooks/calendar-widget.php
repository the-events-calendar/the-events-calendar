<?php
/**
 * @for Calendar Widget Template
 * This file contains the hook logic required to create an effective calendar widget view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

// the div tribe-events-widget-nav controls ajax navigation for the calendar widget. 
// Modify with care and do not remove any class names or elements inside that element 
// if you wish to retain ajax functionality.

if( !class_exists('Tribe_Events_Calendar_Widget_Template')){
	class Tribe_Events_Calendar_Widget_Template extends Tribe_Template_Factory {
		public static function init(){
			// Start calendar widget template
			add_filter( 'tribe_events_calendar_widget_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Calendar ajax navigation
			add_filter( 'tribe_events_calendar_widget_before_the_nav', array( __CLASS__, 'before_the_nav' ), 1, 1 );
			add_filter( 'tribe_events_calendar_widget_the_nav', array( __CLASS__, 'the_nav' ), 1, 1 );
			add_filter( 'tribe_events_calendar_widget_after_the_nav', array( __CLASS__, 'after_the_nav' ), 1, 1 );

			// Start calendar
			add_filter( 'tribe_events_calendar_widget_before_the_cal', array( __CLASS__, 'before_the_cal' ), 1, 1 );
	
			// Calendar days of the week
			add_filter( 'tribe_events_calendar_widget_before_the_days', array( __CLASS__, 'before_the_days' ), 1, 1 );
			add_filter( 'tribe_events_calendar_widget_the_days', array( __CLASS__, 'the_days' ), 1, 1 );
			add_filter( 'tribe_events_calendar_widget_after_the_days', array( __CLASS__, 'after_the_days' ), 1, 1 );

			// Calendar dates
			add_filter( 'tribe_events_calendar_widget_before_the_dates', array( __CLASS__, 'before_the_dates' ), 1, 1 );
			add_filter( 'tribe_events_calendar_widget_the_dates', array( __CLASS__, 'the_dates' ), 1, 1 );
			add_filter( 'tribe_events_calendar_widget_after_the_dates', array( __CLASS__, 'after_the_dates' ), 1, 1 );
	
			// End calendar
			add_filter( 'tribe_events_calendar_widget_after_the_cal', array( __CLASS__, 'after_the_cal' ), 1, 1 );

			// End calendar widget template
			add_filter( 'tribe_events_calendar_widget_after_template', array( __CLASS__, 'after_template' ), 1, 1 );	
		}
		// Start Calendar Widget Template
		public static function before_template(){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_before_template');
		}
		// Calendar Ajax Navigation
		public static function before_the_nav(){
			$html = '<div class="tribe-events-widget-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_before_the_nav');
		}
		public static function the_nav(){
			$tribe_ecp = TribeEvents::instance();
			$current_date = tribe_get_month_view_date();
			list( $year, $month ) = explode( '-', $current_date );
			$date = mktime( 12, 0, 0, $month, 1, $year ); // 1st day of month as unix stamp
			$html = '<a class="tribe-mini-ajax prev-month" href="#" data-month="'. $tribe_ecp->previousMonth( $current_date ) .'" title="'. tribe_get_previous_month_text() .'"><span>'. tribe_get_previous_month_text() .'</span></a>';
			$html .= '<span id="tribe-mini-ajax-month">'. $tribe_ecp->monthsShort[date( 'M',$date )] . date( ' Y',$date ) .'</span>';
			$html .= '<a class="tribe-mini-ajax next-month" href="#" data-month="'. $tribe_ecp->nextMonth( $current_date ) .'" title="'. tribe_get_next_month_text() .'"><span>'. tribe_get_next_month_text() .'</span></a>';
			$html .= '<img id="ajax-loading-mini" class="tribe-spinner-small" src="'. trailingslashit( TribeEvents::instance()->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
				
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_the_nav');
		}
		public static function after_the_nav(){
			$html = '</div><!-- .tribe-events-widget-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_after_the_nav');
		}
		// Start Calendar
		public static function before_the_cal(){
			$html = '<table class="tribe-events-calendar">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_before_the_cal');
		}
		// Calendar Days of the Week
		public static function before_the_days(){
			$html = '<thead><tr>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_before_the_days');
		}
		public static function the_days() {
			$tribe_ecp = TribeEvents::instance();
			$startOfWeek = get_option( 'start_of_week', 0 );
			$html = '';
			for( $n = $startOfWeek; $n < count( $tribe_ecp->daysOfWeekMin ) + $startOfWeek; $n++ ) {
				$dayOfWeek = ( $n >= 7 ) ? $n - 7 : $n;
				$html .= '<th id="tribe-events-' . strtolower( $tribe_ecp->daysOfWeekMin[$dayOfWeek] ) . '" title="' . $tribe_ecp->daysOfWeek[$dayOfWeek] . '">' . $tribe_ecp->daysOfWeekMin[$dayOfWeek] . '</th>';
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_the_days');
		}
		public static function after_the_days(){
			$html = '</tr></thead>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_after_the_days');
		}
		// Calendar Dates
		public static function before_the_dates(){
			$html = '<tbody class="hfeed vcalendar"><tr>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_before_the_dates');
		}
		public static function the_dates( $args = array() ){


			extract( $args, EXTR_SKIP );
			ob_start();
			// Skip last month
			for( $i = 1; $i <= $offset; $i++ ) { 
				echo '<td class="tribe-events-othermonth"></td>';
			}
			// Output this month
			for( $day = 1; $day <= date( "t", $date ); $day++ ) {
			    
			    if( ( $day + $offset - 1 ) % 7 == 0 && $day != 1 ) {
			        echo "</tr>\n\t<tr>";
			        $rows++;
			    }

				// Var'ng up days, months and years
				$current_day = date_i18n( 'd' );
				$current_month = date_i18n( 'm' );
				$current_year = date_i18n( 'Y' );
				
				$column = $day + $offset  - ( 7 * ( $rows - 1 ) ) ;
				$ppf = '';
				if ( $current_month == $month && $current_year == $year) {
					// Past, Present, Future class
					if ( $current_day == $day ) {
						$ppf = ' tribe-events-present';
					} elseif ($current_day > $day) {
						$ppf = ' tribe-events-past';
					} elseif ($current_day < $day) {
						$ppf = ' tribe-events-future';
					}
				} elseif ( $current_month > $month && $current_year == $year || $current_year > $year ) {
					$ppf = ' tribe-events-past';
				} elseif ( $current_month < $month && $current_year == $year || $current_year < $year ) {
					$ppf = ' tribe-events-future';
				} else { $ppf = false; }
				
				if ( ( $column % 5 == 0 ) || ( $column % 6 == 0 ) || ( $column % 7 == 0 ) ) {
					$ppf .= ' tribe-events-right';
				}
			   
			   	// You can find tribe_mini_display_day() in the /public/template-tags/widgets.php
			   	// This controls the markup for the days and events on the frontend
			   
			    echo "<td class=\"tribe-events-thismonth". $ppf ."\">" . tribe_mini_display_day( $day, $monthView ) ."\n";
				echo "</td>";
			
			}
			// Skip next month
			while( ( $day + $offset ) <= $rows * 7 ) {
			    echo '<td class="tribe-events-othermonth"></td>';
			    $day++;
			}
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_the_dates');
		}
		public static function after_the_dates(){
			$html = '</tr></tbody><!-- .hfeed -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_after_the_dates');
		}
		// End Calendar
		public static function after_the_cal(){
			$html = '</table><!-- .tribe-events-calendar -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_after_the_cal');
		}
		// End Calendar Widget Template		
		public static function after_template(){
			$html = '<p class="tribe-events-widget-link"><a href="'. tribe_get_events_link() .'" rel="bookmark">'. __( 'View all &raquo;', 'tribe-events-calendar' ) .'</a></p>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_after_template');
		}
	}
	Tribe_Events_Calendar_Widget_Template::init();
}