<?php
/**
 * Calendar Grid Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) ) {

	/**
	 * Calendar Grid (Display)
	 *
	 * Display the full size grid calendar table
	 *
	 * @uses load_template()
	 * @since 2.0
	 */
	function tribe_calendar_grid()  {
		set_query_var( 'eventDisplay', 'bydate' );
		echo apply_filters('tribe_calendar_grid', load_template( TribeEventsTemplates::getTemplateHierarchy('calendar-grid', 'modules') ));
	}

	/**
	 * Calendar Mini Grid (Display)
	 *
	 * Displays the mini grid calendar table (usually in a widget)
	 *
	 * @uses load_template()
	 * @since 2.0
	 */
	function tribe_calendar_mini_grid() {
		set_query_var( 'eventDisplay', 'bydate' );
		load_template( TribeEventsTemplates::getTemplateHierarchy( 'calendar-widget', 'widgets' ), false );
	}

	/**
	 * Grid View Test
	 *
	 * Returns true if the query is set for grid display (as opposed to Upcoming / Past)
	 *
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_month()  {
		$tribe_ecp = TribeEvents::instance();
		$output = ( $tribe_ecp->displaying == 'month' ) ? true : false;
		return apply_filters('tribe_is_month', $output);
	}
	
	/**
	 * Sort Events by Day
	 *
	 * Maps events to days of the month.
	 *
	 * @param array $results Array of events from tribe_get_events()
	 * @param string $date
	 * @return array Days of the month with events as values
	 * @since 2.0
	 */
	function tribe_sort_by_month( $results, $date )  {
		global $post;

		$cutoff_time = tribe_get_option('multiDayCutoff', '12:00');
		
		if( preg_match( '/(\d{4})-(\d{2})/', $date, $matches ) ) {
			$queryYear	= $matches[1];
			$queryMonth = $matches[2];
		} else {
			return false; // second argument not a date we recognize
		}
		$monthView = array();
		for( $i = 1; $i <= 31; $i++ ) {
			$monthView[$i] = array();
		}


		foreach ( (array)$results as $event ) {
			$post = $event;

			$started = false;

			list( $startYear, $startMonth, $startDay ) = explode( '-', $event->EventStartDate );
			list( $endYear, $endMonth, $endDay ) = explode( '-', $event->EventEndDate );

			list( $startDay, $garbage ) = explode( ' ', $startDay );
	
			list( $endDay, $garbage ) = explode( ' ', $endDay );
			for( $i = 1; $i <= 31 ; $i++ ) {
				$curDate = strtotime( $queryYear.'-'.$queryMonth.'-'.$i );

				if ( ( $i == $startDay && $startMonth == $queryMonth ) ||  strtotime( $startYear.'-'.$startMonth ) < strtotime( $queryYear.'-'.$queryMonth ) ) {
					$started = true;
				}
				
				// if last day of multiday event 			
				if( !tribe_get_all_day() && tribe_is_multiday($event->ID) && date('Y-m-d', $curDate) == date('Y-m-d', strtotime($event->EventEndDate)) ) {
					$endTime = strtotime(date('Y-m-d', $curDate) . date('h:i A', strtotime($event->EventEndDate)));
					$cutoffTime = strtotime(date('Y-m-d', $curDate) . $cutoff_time .  "AM");
					
					// if end time is before cutoff, then don't show
					if ($endTime <= $cutoffTime) {
						$started = false;
					}
				}

				// see spoofQuery in lib/tribe-templates.class.php
				if ( $event->guid === '_tribe_empty_event' ) {
					$started = false;
				}

				if ( $started ) {
					$monthView[$i][] = $event;
				}
				if( $i == $endDay && $endMonth == $queryMonth ) {
					continue 2;
				}
			}
		}

		return apply_filters('tribe_sort_by_month', $monthView);
	}
	
	/**
	 * Drop Menu Post Link
	 *
	 * Returns the URL where the jump menu sends the month/year request.
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_dropdown_link_prefix()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('dropdown');
		return apply_filters('tribe_get_dropdown_link_prefix', $output);
	}

	/**
	 * Month / Year Dropdown Selector (Display)
	 *
	 * Display the year & month dropdowns. JavaScript in the resources/events-admin.js file will autosubmit on the change event. 
	 *
	 * @param string $prefix A prefix to add to the ID of the calendar elements.  This allows you to reuse the calendar on the same page.
	 * @since 2.0
	 */
	function tribe_month_year_dropdowns( $prefix = '' )  {
		global $wp_query;

		if ( isset ( $wp_query->query_vars['eventDate'] ) ) { 
			$date = $wp_query->query_vars['eventDate'];
		} else {
			$date = date_i18n( TribeDateUtils::DBDATEFORMAT );
		}
		$monthOptions = apply_filters('tribe_month_year_dropdowns_monthOptions', TribeEventsViewHelpers::getMonthOptions( $date ));
		$yearOptions = apply_filters('tribe_month_year_dropdowns_yearOptions', TribeEventsViewHelpers::getYearOptions( $date ));
		apply_filters('tribe_month_year_dropdowns', include(TribeEvents::instance()->pluginPath.'admin-views/datepicker.php'));
	}

	/**
	 * Link to This Month
	 *
	 * Returns a link to the currently displayed month (if in "jump to month" mode)
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_this_month_link()  {
		$tribe_ecp = TribeEvents::instance();
		if ( $tribe_ecp->displaying == 'month' ) {
			$output = $tribe_ecp->getLink( 'month', $tribe_ecp->date );
		} else {
			$output = false;
		}
		return apply_filters('tribe_get_this_month_link', $output);
	}

	/**
	 * Gridview Date
	 *
	 * Get current calendar gridview date
	 *
	 * @return string Date currently queried
	 * @since 2.0
	 */
	function tribe_get_month_view_date()  {
		global $wp_query;

		if ( isset ( $wp_query->query_vars['eventDate'] ) ) { 
			$date = $wp_query->query_vars['eventDate'] . "-01";
		} else {
			$date = date_i18n( TribeDateUtils::DBDATEFORMAT );
		}
		
		return apply_filters('tribe_get_month_view_date', $date);
	}

	/**
	 * Link to Previous Month
	 * 
	 * Returns a link to the previous month's events page. Used in the grid view.
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_previous_month_link()  {
		global $wp_query;
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink( 'month', $tribe_ecp->previousMonth( tribe_get_month_view_date() ));
		return apply_filters('tribe_get_previous_month_link', $output);
	}
	
	/**
	 * Previous Month Text
	 *
	 * Returns a textual description of the previous month
	 *
	 * @return string Name of the previous month.
	 * @since 2.0
	 */
	function tribe_get_previous_month_text()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getDateStringShortened( $tribe_ecp->previousMonth( tribe_get_month_view_date() ) );
		return apply_filters('tribe_get_previous_month_text', $output);
	}

	/**
	 * Link to Next Month
	 * 
	 * Returns a link to the next month's events page. Used in the grid view.
	 *
	 * @return string URL 
	 * @since 2.0
	 */
	function tribe_get_next_month_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink( 'month', $tribe_ecp->nextMonth(tribe_get_month_view_date() ));
		return apply_filters('tribe_get_next_month_link', $output);
	}

	/**
	 * Current Month Text
	 *
	 * Returns a textual description of the current month
	 *
	 * @return string Name of the current month.
	 * @since 2.0
	 */
	function tribe_get_current_month_text( ) {
		$output = date( 'F', strtotime( tribe_get_month_view_date() ) );
		return apply_filters('tribe_get_current_month_text', $output);
	}

	/**
	 * Next Month Text
	 *
	 * Returns a textual description of the next month
	 *
	 * @return string Name of the next month.
	 * @since 2.0
	 */
	function tribe_get_next_month_text()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getDateStringShortened( $tribe_ecp->nextMonth( tribe_get_month_view_date() ) );
		return apply_filters('tribe_get_next_month_text', $output);
	}

	/**
	 * Current Month Date
	 *
	 * Returns a formatted date string of the currently displayed month (in "jump to month" mode)
	 *
	 * @return string Name of the displayed month.
	 * @since 2.0
	 */
	function tribe_get_displayed_month()  {
		$tribe_ecp = TribeEvents::instance();
		if ( $tribe_ecp->displaying == 'month' ) {
			$output = $tribe_ecp->getDateString( $tribe_ecp->date );
		} else {
			$output = " ";
		}
		return apply_filters('tribe_get_displayed_month', $output);
	}

	function tribe_get_display_day_title( $day, $monthView, $date ){
		$return = '<div id="tribe-events-daynum-'. $day .'">';
		if( function_exists( 'tribe_get_linked_day' ) && count( $monthView[$day] ) > 0 ) {
			$return .= tribe_get_linked_day( $date, $day ); // premium
		} else {
	    	$return .= $day;
		}
		$return .= '</div><!-- #tribe-events-daynum-# -->';
		return $return;
	}

	function tribe_the_display_day( $day, $monthView ){
		echo tribe_get_display_day( $day, $monthView );
	}

	function tribe_get_display_day( $day, $monthView ){
		global $post;
		$output = '';
		$posts_per_page = tribe_get_option( 'postsPerPage', 10 );
		ob_start();
		for ( $i = 0; $i < count( $monthView[$day] ); $i++ ) {
			$post = $monthView[$day][$i];
			setup_postdata( $post );
			$eventId	= $post->ID.'-'.$day;
			$start		= tribe_get_start_date( $post->ID, false, 'U' );
			$end		= tribe_get_end_date( $post->ID, false, 'U' );
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
			$tribe_classes_venue = tribe_get_venue_id($post->ID) ? 'tribe-events-venue-'. tribe_get_venue_id($post->ID) : '';
			$tribe_classes_organizer = tribe_get_organizer_id($post->ID) ? 'tribe-events-organizer-'. tribe_get_organizer_id($post->ID) : '';
			$tribe_classes_categories = $tribe_string_classes;
			$class_string = $tribe_classes_default .' '. $tribe_classes_venue .' '. $tribe_classes_organizer .' '. $tribe_classes_categories;

			// added last class for css
			if( $i+1 == count( $monthView[$day] ) ){
				$class_string .= ' tribe-last';
			}

			?>
			
			<div id="tribe-events-event-<?php echo $eventId; ?>" class="<?php echo $class_string; ?>">
				<h3 class="entry-title summary"><a href="<?php tribe_event_link(); ?>"><?php echo $post->post_title; ?></a></h3>
				<div id="tribe-events-tooltip-<?php echo $eventId; ?>" class="tribe-events-tooltip">
					<h4 class="entry-title summary"><?php the_title() ;?></h4>
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
							<div class="tribe-events-event-thumb"><?php echo get_the_post_thumbnail( $post->ID, array( 75,75 ) );?></div>
						<?php } ?>
						
						<p class="entry-summary description"><?php echo has_excerpt($post->ID) ? TribeEvents::truncate( $post->post_excerpt ) : TribeEvents::truncate( $post->post_content, 30 ); ?></p>

					</div><!-- .tribe-events-event-body -->
					<span class="tribe-events-arrow"></span>
				</div><!-- .tribe-events-tooltip -->
			</div><!-- #tribe-events-event-# -->
			<?php
		}
		$html = ob_get_clean();
		return $html;
	}
}
?>