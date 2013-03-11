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
		private static $hide_upcoming_ids;
		private static $current_day;
		private static $current_month;
		private static $current_year;
		private static $event_daily_counts = array();
		private static $posts_per_page_limit = 3;
		private static $tribe_bar_args = array();
		private static $cache_expiration = 3600;

		public static function init(){

			Tribe_Template_Factory::asset_package( 'ajax-calendar' );

			global $wp_query;
			if (tribe_is_event_query() && !empty($wp_query->query_vars['s']) && empty($wp_query->posts) ){
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There are no events for %s.', 'tribe-events-calendar' ), $wp_query->query_vars['s'] ) );
			}		

			// Start calendar template
			add_filter( 'tribe_events_calendar_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Calendar title
			add_filter( 'tribe_events_calendar_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_calendar_the_title', array( __CLASS__, 'the_title' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// Calendar header
			add_filter( 'tribe_events_calendar_before_header', array( __CLASS__, 'before_header' ), 1, 1 );
			
			// Navigation
			add_filter( 'tribe_events_calendar_before_header_nav', array( __CLASS__, 'before_header_nav' ), 1, 1 );
			add_filter( 'tribe_events_calendar_header_nav', array( __CLASS__, 'header_navigation' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_header_nav', array( __CLASS__, 'after_header_nav' ), 1, 1 );
			
			add_filter( 'tribe_events_calendar_after_header', array( __CLASS__, 'after_header' ), 1, 1 );

			// Calendar notices
			add_filter( 'tribe_events_calendar_notices', array( __CLASS__, 'notices' ), 1, 1 );

			// Calendar content
			add_filter( 'tribe_events_calendar_before_the_grid', array( __CLASS__, 'before_the_grid' ), 1, 1 );
			add_filter( 'tribe_events_calendar_the_grid', array( __CLASS__, 'the_grid' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_the_grid', array( __CLASS__, 'after_the_grid' ), 1, 1 );
			
			// Calendar footer
			add_filter( 'tribe_events_calendar_before_footer', array( __CLASS__, 'before_footer' ), 1, 1 );
			
			// Navigation
			add_filter( 'tribe_events_calendar_before_footer_nav', array( __CLASS__, 'before_footer_nav' ), 1, 1 );
			add_filter( 'tribe_events_calendar_footer_nav', array( __CLASS__, 'footer_navigation' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_footer_nav', array( __CLASS__, 'after_footer_nav' ), 1, 1 );
			
			add_filter( 'tribe_events_calendar_after_footer', array( __CLASS__, 'after_footer' ), 1, 1 );

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
			$html = sprintf( '<h2 class="tribe-events-page-title">'. __( 'Events For ', 'tribe-events-calendar' ) .'%s</h2>',
				date( "F Y", strtotime( tribe_get_month_view_date() ))
				);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_the_title');
		}
		public static function after_the_title(){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_the_title');
		}
		// Notices
		public static function notices( $post_id ){
			$html = tribe_events_the_notices(false);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_notices');
		}
		// Calendar Header
		public static function before_header(){
			$html = '<div id="tribe-events-header" data-title="' . wp_title( '&raquo;', FALSE ) . '" data-date="'. date( 'Y-m', strtotime( tribe_get_month_view_date() ) ) .'" data-baseurl="' . tribe_get_gridview_link( false ) . '">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_header');
		}
		// Calendar Navigation
		public static function before_header_nav(){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Calendar Month Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_header_nav');
		}
		public static function header_navigation(){
			$tribe_ecp = TribeEvents::instance();

			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous">';
			$html .= '<a data-month="'. $tribe_ecp->previousMonth( tribe_get_month_view_date() ) .'" href="' . tribe_get_previous_month_link() . '" rel="prev">&larr; '. tribe_get_previous_month_text() .' </a>';
			$html .= '</li><!-- .tribe-nav-previous -->';
			
			// Display Date Navigation
			$html .= '<li class="tribe-events-nav-date">';
			ob_start();
			tribe_month_year_dropdowns( "tribe-events-", tribe_get_month_view_date() );
			$html .= ob_get_clean();
			$html .= '</li><!-- .tribe-events-nav-date -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next">';
			$html .= '<a data-month="'. $tribe_ecp->nextMonth( tribe_get_month_view_date() ) .'" href="' . tribe_get_next_month_link() .'" rel="next"> '. tribe_get_next_month_text() .' &rarr;</a>';
			
			// Loading spinner
			$html .= '<img class="tribe-ajax-loading tribe-spinner-medium" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_header_nav');
		}
		public static function after_header_nav(){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_header_nav');
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
			ob_start();

			$tribe_ecp = TribeEvents::instance();
			$tribe_ecp->date = tribe_get_month_view_date();

			// get all upcoming ids to hide so we're not querying 31 times
			self::$hide_upcoming_ids = TribeEventsQuery::getHideFromUpcomingEvents();

			list( $year, $month ) = explode( '-', $tribe_ecp->date );
			$date = mktime( 12, 0, 0, $month, 1, $year ); // 1st day of month as unix stamp


			$startOfWeek = get_option( 'start_of_week', 0 );
			$rawOffset = date( 'w', $date ) - $startOfWeek;
			$offset = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
			$rows = 1;

			self::$event_daily_counts = self::get_daily_counts($date);

			if ( empty(self::$tribe_bar_args) ) {
				foreach ( $_REQUEST as $key => $value ) {
					if ( $value && strpos($key, 'tribe-bar-') === 0 && $key != 'tribe-bar-date' ) {
						self::$tribe_bar_args[$key] = $value;
					}
				}
			}

			// Var'ng up days, months and years
			self::$current_day = date_i18n( 'd' );
			self::$current_month = date_i18n( 'm' );
			self::$current_year = date_i18n( 'Y' );
?>
			<table class="tribe-events-calendar">
				<?php self::grid_head($startOfWeek); ?>

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

							self::single_day( $year, $month, $day, $column );
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
		// Calendar Footer
		public static function before_footer(){
			$html = '<div id="tribe-events-footer">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_footer');
		}
		// Calendar Navigation
		public static function before_footer_nav(){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Calendar Month Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_footer_nav');
		}
		public static function footer_navigation(){
			$tribe_ecp = TribeEvents::instance();

			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous">';
			$html .= '<a data-month="'. $tribe_ecp->previousMonth( tribe_get_month_view_date() ) .'" href="' . tribe_get_previous_month_link() . '" rel="prev">&larr; '. tribe_get_previous_month_text() .' </a>';
			$html .= '</li><!-- .tribe-nav-previous -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next">';
			$html .= '<a data-month="'. $tribe_ecp->nextMonth( tribe_get_month_view_date() ) .'" href="' . tribe_get_next_month_link() .'" rel="next"> '. tribe_get_next_month_text() .' &rarr;</a>';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_footer_nav');
		}
		public static function after_footer_nav(){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_footer_nav');
		}
		public static function after_footer(){
			$html = '</div><!-- #tribe-events-footer -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_footer');
		}
		// End Calendar Template
		public static function after_template(){
			$html = '';
			if( function_exists( 'tribe_get_ical_link' ) )
				$html .= '<a class="tribe-events-ical tribe-events-button-grey" title="'. __( 'iCal Import', 'tribe-events-calendar' ) .'" href="'. tribe_get_ical_link() .'">'. __( 'iCal Import', 'tribe-events-calendar' ) .'</a>';
				
			if ( tribe_get_option( 'donate-link', FALSE ) == TRUE )
				$html .= '<p class="tribe-events-promo">' . apply_filters( 'tribe_promo_banner', sprintf( __( 'Calendar powered by %sThe Events Calendar%s', 'tribe-events-calendar' ), '<a class="vcard url org fn" href="http://tri.be/wordpress-events-calendar/">', '</a>' ) ) . '</p>';
			$html .= '</div><!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_template');
		}

		private static function get_daily_counts( $date ) {
			global $wp_query;
			$count_args = $wp_query->query;
			if ( empty($count_args) ) { // this will likely be empty on Ajax calls
				$count_args['post_type'] = TribeEvents::POSTTYPE;
				$count_args['eventDisplay'] = 'month';
			}
			$count_args['start_date'] = date('Y-m-d', $date) . ' 00:00:00';
			$count_args['end_date'] = date('Y-m-t', $date) . ' 23:59:59';
			$count_args['hide_upcoming_ids'] = self::$hide_upcoming_ids;

			$cache = new TribeEventsCache();
			$cache_key = 'daily_counts_'.serialize($count_args);
			$found = $cache->get( $cache_key, 'save_post' );
			if ( $found && is_array($found) ) {
				return $found;
			}
			$result = TribeEventsQuery::getEventCounts( $count_args );
			$cache->set( $cache_key, $result, self::$cache_expiration, 'save_post' );
			return $result;
		}

		private static function grid_head( $startOfWeek ) {
			$tribe_ecp = TribeEvents::instance();
			?>
			<thead>
			<tr>
				<?php
				for( $n = $startOfWeek; $n < count( $tribe_ecp->daysOfWeek ) + $startOfWeek; $n++ ) {
					$dayOfWeek = ( $n >= 7 ) ? $n - 7 : $n;
					echo '<th id="tribe-events-' . strtolower( $tribe_ecp->daysOfWeek[$dayOfWeek] ) . '" title="' . $tribe_ecp->daysOfWeek[$dayOfWeek] . '">' . $tribe_ecp->daysOfWeekShort[$dayOfWeek] . '</th>';
				} ?>
			</tr>
			</thead>
			<?php
		}

		private static function single_day( $year, $month, $day, $column ) {
			$date =  date( 'Y-m-d', strtotime("$year-$month-$day") );

			$ppf = self::get_past_present_future_classes( $year, $month, $day );

			if ( ( $column % 4 == 0 ) || ( $column % 5 == 0 ) || ( $column % 6 == 0 ) ) {
				$ppf .= ' tribe-events-right';
			}

			// You can find tribe_the_display_day() & tribe_get_display_day_title() in
			// /public/template-tags/calendar.php
			// This controls the markup for the days and events on the frontend

			echo "<td class=\"tribe-events-thismonth". $ppf ."\">"."\n"; //. tribe_get_display_day_title( $day, $monthView, $date ) ."\n";

			echo '<div id="tribe-events-daynum-' . $day .'">';
			// If PRO enabled have links for days
			if ( class_exists( 'TribeEventsPro' ) ) {
				$day_link = tribe_get_day_link($date);
				printf( '<a href="%s">%s</a>',
					$day_link,
					$day
				);
			} else {
				echo $day;
			}
			echo '</div>';

			$daily_events = self::get_daily_events($date);

			foreach( $daily_events->posts as $post_int => $post ) {
				$classes = self::single_event_classes($post);
				if ( $post_int+1 == count($daily_events->posts) ) {
					$classes[] = 'tribe-last';
				}
				self::single_event( $post, $day, $classes );
			}

			self::view_more_link( $date, self::$tribe_bar_args );

			// echo $date;
			// tribe_the_display_day( $day, $daily_events );
			echo '</td>';
		}

		private static function single_event_classes( $post ) {

			// Get our wrapper classes (for event categories, organizer, venue, and defaults)
			$classes = array('hentry', 'vevent');
			$tribe_cat_ids = tribe_get_event_cat_ids( $post->ID );
			foreach( $tribe_cat_ids as $tribe_cat_id ) {
				$classes[] = 'tribe-events-category-'. $tribe_cat_id;
			}
			$classes = array_merge($classes, get_post_class('', $post->ID));
			if ( $venue_id = tribe_get_venue_id($post->ID) ) {
				$classes[] = 'tribe-events-venue-'.$venue_id;
			}
			if ( $organizer_id = tribe_get_organizer_id($post->ID) ) {
				$classes[] = 'tribe-events-organizer-'.$organizer_id;
			}
			return $classes;
		}

		private static function single_event( $post, $day, $classes = array() ) {
			$tribe_ecp = TribeEvents::instance();

			$eventId	= $post->ID.'-'.$day;
			$start		= tribe_get_start_date( $post, FALSE, 'U' );
			$end		= tribe_get_end_date( $post, FALSE, 'U' );
			$class_string = implode(' ', $classes);
			// $cost		= tribe_get_cost( $post->ID, TRUE);
				?>

			<div id="tribe-events-event-<?php echo $eventId; ?>" class="<?php echo $class_string; ?>">
				<h3 class="entry-title summary"><a href="<?php tribe_event_link( $post ); ?>"><?php echo $post->post_title; ?></a></h3>
				<div id="tribe-events-tooltip-<?php echo $eventId; ?>" class="tribe-events-tooltip">
					<h4 class="entry-title summary"><?php echo $post->post_title;?></h4>
					<div class="tribe-events-event-body">
						<div class="duration">
							<abbr class="tribe-events-abbr updated published dtstart" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), $start ); ?>">
								<?php if ( !empty( $start ) )	echo date_i18n( get_option( 'date_format', 'F j, Y' ), $start );
								if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', TRUE ) )
									echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), $start ); ?>
							</abbr><!-- .dtstart -->
							<abbr class="tribe-events-abbr dtend" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), $end ); ?>">
								<?php if ( !empty( $end )  && $start !== $end ) {
								if ( date_i18n( 'Y-m-d', $start ) == date_i18n( 'Y-m-d', $end ) ) {
									$time_format = get_option( 'time_format', 'g:i a' );
									if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', TRUE ) )
										echo " – " . date_i18n( $time_format, $end );
								} else {
									echo " – " . date_i18n( get_option( 'date_format', 'F j, Y' ), $end );
									if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', TRUE ) )
										echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), $end ) . '<br />';
								}
							} ?>
							</abbr><!-- .dtend -->
						</div><!-- .duration -->

						<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail($post->ID) ) { ?>
						<div class="tribe-events-event-thumb"><?php echo get_the_post_thumbnail( $post->ID, array( 75,75 ) );?></div>
						<?php } ?>

						<p class="entry-summary description">
							<?php if( has_excerpt( $post->ID ) ) {
							echo $tribe_ecp->truncate( $post->post_excerpt, 30 );
						} else {
							echo $tribe_ecp->truncate( $post->post_content, 30 );
						} ?>
						</p><!-- .entry-summary -->

					</div><!-- .tribe-events-event-body -->
					<span class="tribe-events-arrow"></span>
				</div><!-- .tribe-events-tooltip -->
			</div><!-- #tribe-events-event-# -->
			<?php
		}

		private static function view_more_link( $date, $args ) {
			if( !empty(self::$event_daily_counts[$date]) && (int) self::$event_daily_counts[$date] > self::$posts_per_page_limit ) {
				$day_link = tribe_get_day_link($date);
				if ( !empty($args) ) {
					$day_link = add_query_arg($args, $day_link);
				}
				printf( '<div class="tribe-viewmore"><a href="%s">View All %d &raquo;</a></div>',
					$day_link,
					self::$event_daily_counts[$date]
				);
			}
		}

		private static function get_past_present_future_classes( $year, $month, $day ) {
			$ppf = '';

			if ( self::$current_month == $month && self::$current_year == $year) {
				// Past, Present, Future class
				if ( self::$current_day == $day ) {
					$ppf = ' tribe-events-present';
				} elseif ( self::$current_day > $day ) {
					$ppf = ' tribe-events-past';
				} elseif ( self::$current_day < $day ) {
					$ppf = ' tribe-events-future';
				}
			} elseif ( self::$current_month > $month && self::$current_year == $year || self::$current_year > $year ) {
				$ppf = ' tribe-events-past';
			} elseif ( self::$current_month < $month && self::$current_year == $year || self::$current_year < $year ) {
				$ppf = ' tribe-events-future';
			}

			return $ppf;
		}

		/**
		 * @param string $date
		 * @return WP_Query
		 */
		private function get_daily_events( $date ) {
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();

			$args = wp_parse_args(array(
				'eventDate' => $date,
				'start_date' => tribe_event_beginning_of_day( $date ),
				'end_date' => tribe_event_end_of_day( $date ),
				// setup our own custom hide upcoming
				'post__not_in' => self::$hide_upcoming_ids,
				'hide_upcoming' => FALSE,
				'posts_per_page' => self::$posts_per_page_limit,
				'orderby' => 'event_date',
				'order' => 'ASC',
				'eventDisplay' => 'custom',
				'no_found_rows' => TRUE
			), $wp_query->query_vars);

			if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
				$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
				$args['eventCat'] = (int) $cat->term_id;
			}

			$cache = new TribeEventsCache();
			$cache_key = 'daily_events_'.serialize($args);
			$found = $cache->get($cache_key, 'save_post');
			if ( $found && is_a($found, 'WP_Query') ) {
				return $found;
			}

			$result = TribeEventsQuery::getEvents( $args, TRUE );
			$cache->set($cache_key, $result, self::$cache_expiration, 'save_post');
			return $result;
		}
	}
	Tribe_Events_Calendar_Template::init();
}
