<?php
namespace Tribe\Events\Event_Status;

use Tribe__Events__Main as Events_Plugin;
use WP_Post;

/**
 * Class Template_Modifications
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status
 */
class Template_Modifications {
	/**
	 * Stores the template class used.
	 *
	 * @since TBD
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * File name and template insert to regex map.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
/*	protected $file_template_to_regex_map = [
		// List View
		'list/event/title:status-label'                                                     => '/(<h3 class="tribe-events-calendar-list__event-title tribe-common-h6 tribe-common-h4--min-medium">)/',
		// List View
		'day/event/title:status-label'                                                      => '/(<h3 class="tribe-events-calendar-day__event-title tribe-common-h6 tribe-common-h4--min-medium">)/',
		// Month View
		'month/calendar-body/day/calendar-events/calendar-event/date:online-event'          => '/(<divclass="tribe-events-calendar-month__calendar-event-datetime">)/',
		'month/calendar-body/day/calendar-events/calendar-event/tooltip/date:online-event'  => '/(<div class="tribe-events-calendar-month__calendar-event-tooltip-datetime">)/',
		'month/calendar-body/day/multiday-events/multiday-event:online-event'               => '/(<div class="tribe-events-calendar-month__multiday-event-bar-inner">)/',
		'month/mobile-events/mobile-day/mobile-event/date:online-event'                     => '/(<div class="tribe-events-calendar-month-mobile-events__mobile-event-datetime tribe-common-b2">)/',
		'month/calendar-body/day/calendar-events/calendar-event/title:status-label'         => '/(<h3 class="tribe-events-calendar-month__calendar-event-title tribe-common-h8 tribe-common-h--alt">)/',
		'month/calendar-body/day/calendar-events/calendar-event/tooltip/title:status-label' => '/(<h3 class="tribe-events-calendar-month__calendar-event-tooltip-title tribe-common-h7">)/',
		'month/calendar-body/day/multiday-events/multiday-event:status-label'               => '/(<h3 class="tribe-events-calendar-month__multiday-event-bar-title tribe-common-h8">)/',
		'month/mobile-events/mobile-day/mobile-event/title:status-label'                    => '/(<h3 class="tribe-events-calendar-month-mobile-events__mobile-event-title tribe-common-h7">)/',
		// Photo View
		'photo/event/title:status-label'                                                    => '/(<h3 class="tribe-events-pro-photo__event-title tribe-common-h6">)/',
		// Map View
		'map/event-cards/event-card/event/title:status-label'                               => '/(<h3 class="tribe-events-pro-map__event-title tribe-common-h8 tribe-common-h7--min-medium">)/',
		'map/event-cards/event-card/tooltip/title:status-label'                             => '/(<h3 class="tribe-events-pro-map__event-tooltip-title tribe-common-h7">)/',
		// Week View
		'week/grid-body/events-day/event/date:online-event'                                 => '/(<div class="tribe-events-pro-week-grid__event-datetime">)/',
		'week/grid-body/events-day/event/tooltip/date:online-event'                         => '/(<div class="tribe-events-pro-week-grid__event-tooltip-datetime">)/',
		'week/grid-body/multiday-events-day/multiday-event:online-event'                    => '/(<div class="tribe-events-pro-week-grid__multiday-event-bar-inner">)/',
		'week/grid-body/events-day/event/title:status-label'                                => '/(<h3 class="tribe-events-pro-week-grid__event-title tribe-common-h8 tribe-common-h--alt">)/',
		'week/grid-body/events-day/event/tooltip/title:status-label'                        => '/(<h3 class="tribe-events-pro-week-grid__event-tooltip-title tribe-common-h7">)/',
		'week/grid-body/multiday-events-day/multiday-event:status-label'                    => '/(<h3 class="tribe-events-pro-week-grid__multiday-event-bar-title tribe-common-h8 tribe-common-h--alt">)/',
		'week/mobile-events/day/event/title:status-label'                                   => '/(<h3 class="tribe-events-pro-week-mobile-events__event-title tribe-common-h6 tribe-common-h5--min-medium">)/',
	];*/

	/**
	 * Tempalte Modification constructor.
	 *
	 * @since TBD
	 *
	 * @param Template $template An instance of the plugin template handler. 	one.
	 */
	public function __construct( Template $template ) {
		$this->template = $template;
	}

	/**
	 * Gets the instance of template class set for the metabox.
	 *
	 * @since TBD
	 *
	 * @return Template Instance of the template we are using to render this metabox.
	 */
	public function get_template() {
		return $this->template;
	}

	/**
	 * Add the control classes for the views v2 elements
	 *
	 * @since TBD
	 *
	 * @param int|WP_Post      $event      Post ID or post object.
	 *
	 * @return string[]
	 */
	public function get_post_classes( $event ) {
		$classes = [];
		if ( ! tribe_is_event( $event ) ) {
			return $classes;
		}

		$event = tribe_get_event( $event );

		$status = get_post_meta( $event->ID, Event_Meta::$key_status, true );
		if ( $status ) {
			$classes[] = 'tribe-ext-events-control-list-event--' . sanitize_html_class( $status );
		}

		return $classes;
	}

	/**
	 * Include the control markers to the single page.
	 *
	 * @since TBD
	 *
	 * @param  string  $notices_html  Previously set HTML.
	 * @param  array   $notices       Array of notices added previously.
	 *
	 * @return string  New Before with the control markers appended.
	 */
	public function add_single_control_markers( $notices_html, $notices ) {
		if ( ! is_singular( Events_Plugin::POSTTYPE ) ) {
			return $notices_html;
		}

		$args = [
			'event' => tribe_get_event( get_the_ID() ),
		];

		return $notices_html . $this->template->template( 'single/post-statuses', $args, false );
	}

	/**
	 * Inserts HTML after regex match.
	 *
	 * @since TBD
	 *
	 * @param string   $template_name Template name to insert into the html.
	 * @param string   $html          HTML to be modified.
	 * @param string   $file          Complete path to include the PHP File.
	 * @param array    $name          Template name.
	 * @param Template $template      Current instance of the Template.
	 *
	 * @return string
	 */
	public function insert_status_label( $template_name, $html, $file, $name, $template ) {
		$key = implode( '/', $name ) . ":$template_name";

		if ( ! array_key_exists( $key, $this->file_template_to_regex_map ) ) {
			return $html;
		}

		$regex       = $this->file_template_to_regex_map[ $key ];
		$replacement = '$1' . $template->template( $template_name, [], false );

		return preg_replace( $regex, $replacement, $html );
	}

	/*	public function regex_insert_template( $template_name, $html, $file, $name, $template ) {
		$key = implode( '/', $name ) . ":$template_name";

		if ( ! array_key_exists( $key, $this->file_template_to_regex_map ) ) {
			return $html;
		}

		$regex       = $this->file_template_to_regex_map[ $key ];
		$replacement = '$1' . $template->template( $template_name, [], false );

		return preg_replace( $regex, $replacement, $html );
	}*/
}
