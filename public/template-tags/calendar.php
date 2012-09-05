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
		load_template( TribeEventsTemplates::getTemplateHierarchy('table') );
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
		load_template( TribeEventsTemplates::getTemplateHierarchy( 'table-mini' ) );
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
		return ( $tribe_ecp->displaying == 'month' ) ? true : false;
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


		foreach ( $results as $event ) {
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
				
				if ( $started ) {
					$monthView[$i][] = $event;
				}
				if( $i == $endDay && $endMonth == $queryMonth ) {
					continue 2;
				}
			}
		}

		return $monthView;
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
		return $output;
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
		$monthOptions = TribeEventsViewHelpers::getMonthOptions( $date );
		$yearOptions = TribeEventsViewHelpers::getYearOptions( $date );
		include(TribeEvents::instance()->pluginPath.'admin-views/datepicker.php');
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
			return $output;
		}
		return false;
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
		
		return $date;
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
		return $output;
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
		return $tribe_ecp->getDateStringShortened( $tribe_ecp->previousMonth( tribe_get_month_view_date() ) );
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
		return $output;
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
		return date( 'F', strtotime( tribe_get_month_view_date() ) );
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
		return $tribe_ecp->getDateStringShortened( $tribe_ecp->nextMonth( tribe_get_month_view_date() ) );
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
			return $tribe_ecp->getDateString( $tribe_ecp->date );
		}
		return " ";
	}

}
?>