<?php
/**
 * Date Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) ) {

	/**
	 * Start Date
	 *
	 * Returns the event start date and time
	 *
	 * @param int $postId (optional) This only works for non recurring events
	 * @param bool $displayTime If true shows date and time, if false only shows date
	 * @param string $dateFormat Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @return string Date
	 * @todo support $postId for recurring events.
	 * @since 2.0
	 */
	function tribe_get_start_date( $post = null, $displayTime = true, $dateFormat = '' )  {
		if ( is_null( $post ) )
			$post = get_the_ID();
		if ( is_numeric( $post ) )
			$post = get_post( $post );

		if( tribe_get_all_day( $post ) )
			 $displayTime = false;

		if( empty($post->EventStartDate) && is_object( $post ) )
			$post->EventStartDate = tribe_get_event_meta( $post, '_EventStartDate', true );

		if( isset($post->EventStartDate) ){
			$date = strtotime( $post->EventStartDate );
		}else{
			return; // '&mdash;';
		}

		return tribe_event_format_date($date, $displayTime, $dateFormat );
	}

	/**
	 * End Date
	 *
	 * Returns the event end date
	 *
	 * @param int $postId (optional) this only works for non recurring events
	 * @param bool $displayTime If true shows date and time, if false only shows date
	 * @param string $dateFormat Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @return string Date
	 * @todo support $postId for recurring events.
	 * @since 2.0
	 */
	function tribe_get_end_date( $post = null, $displayTime = true, $dateFormat = '' )  {
		if ( is_null( $post ) )
			$post = get_the_ID();
		if ( is_numeric( $post ) )
			$post = get_post( $post );
	
		if( tribe_get_all_day( $post ) )
			 $displayTime = false;

		if( empty($post->EventEndDate) )
			$post->EventEndDate = tribe_get_event_meta( $post, '_EventEndDate', true );

		if( isset($post->EventEndDate) ){
			$date = strtotime( $post->EventEndDate );
		}else{
			return; // '&mdash;';
		}

		return tribe_event_format_date($date, $displayTime, $dateFormat );
	}

	/**
	 * Formatted Date
	 *
	 * Returns formatted date
	 *
	 * @param string $date 
	 * @param bool $displayTime If true shows date and time, if false only shows date
	 * @param string $dateFormat Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @return string
	 * @since 2.0
	 */
	function tribe_event_format_date($date, $displayTime = true,  $dateFormat = '')  {
		$tribe_ecp = TribeEvents::instance();
		
		if( $dateFormat ) $format = $dateFormat;
		else $format = get_option( 'date_format', TribeDateUtils::DATEONLYFORMAT );

		if ( $displayTime )
			$format = $tribe_ecp->getTimeFormat( $format );

		$shortMonthNames = ( strstr( $format, 'M' ) ) ? true : false;
		$date = date_i18n ( $format, $date );
		return str_replace( array_keys($tribe_ecp->monthNames( $shortMonthNames )), $tribe_ecp->monthNames( $shortMonthNames ), $date);
	}

	function tribe_event_beginning_of_day( $date = null, $format = 'Y-m-d H:i:s' ){
		if( is_null($date) || empty($date) ) {
			return apply_filters( 'tribe_event_beginning_of_day', Date($format) );
		} else {
			return apply_filters( 'tribe_event_beginning_of_day', Date($format, strtotime($date)) );
		}
	}
	function tribe_event_end_of_day( $date = null, $format = 'Y-m-d H:i:s' ){
		if( is_null($date) || empty($date) ) {
			return apply_filters( 'tribe_event_end_of_day', Date($format, strtotime('tomorrow') - 1 ) );
		} else {
			return apply_filters( 'tribe_event_end_of_day', Date($format, strtotime($date . ' +1 day') - 1 ) );
		}
	}

}
?>