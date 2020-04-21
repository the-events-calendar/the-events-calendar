<?php
/**
 * The Past Latest View.
 *
 * @since   5.1.0
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Traits\List_Behavior;
use Tribe__Context;

class Latest_Past_View extends View {

	use List_Behavior;
	/**
	 * Slug for this view
	 *
	 * @since 5.1.0
	 *
	 * @var string
	 */
	protected $slug = 'latest-past';

	/**
	 * Visibility for this view.
	 *
	 * @since 5.1.0
	 *
	 * @var bool
	 */
	protected static $publicly_visible = true;

	/**
	 * Whitelist of Templates to display when Latest Past Events is Active.
	 *
	 * @since 5.1.0
	 *
	 * @var array
	 */
	protected $whitelist = [
		// Standard View Components.
		'components/loader',
		'components/json-ld-data',
		'components/data',
		'components/before',
		'components/messages',
		'components/breadcrumbs',
		'components/events-bar',
		'components/breadcrumbs',
		'components/top-bar/today',
		'components/top-bar/actions',
		'components/events-bar/search/keyword',
		'components/events-bar/search/submit',
		'components/events-bar/search-button',
		'components/events-bar/tabs',
		'components/events-bar/search',
		'components/events-bar/filters',
		'components/events-bar/views',
		'components/events-bar/views/list',
		'components/events-bar/views/list/item',
		'components/ical-link',
		'components/breakpoints',

		// Day View
		'day',
		'day/top-bar',
		'day/top-bar/nav',
		'day/top-bar/nav/prev',
		'day/top-bar/nav/next-disabled',
		'day/top-bar/datepicker',

		// List View.
		'list/top-bar',
		'list/top-bar/nav',
		'list/top-bar/nav/prev',
		'list/top-bar/nav/next-disabled',
		'list/top-bar/datepicker',
		'list',

		// Month View
		'month',
		'month/top-bar',
		'month/top-bar/nav',
		'month/top-bar/nav/prev',
		'month/top-bar/nav/next-disabled',
		'month/top-bar/datepicker',

		// Map View
		'map',
		'map/top-bar',
		'map/top-bar/nav',
		'map/top-bar/nav/prev',
		'map/top-bar/nav/next-disabled',
		'map/top-bar/datepicker',

		// Photo View
		'photo',
		'photo/top-bar',
		'photo/top-bar/nav',
		'photo/top-bar/nav/prev',
		'photo/top-bar/nav/next-disabled',
		'photo/top-bar/datepicker',

		// Week View
		'week',
		'week/top-bar',
		'week/top-bar/nav',
		'week/top-bar/nav/prev',
		'week/top-bar/nav/next-disabled',
		'week/top-bar/datepicker',

		// Latest Past Events Views.
		'latest-past',
		'latest-past/heading',
		'latest-past/event',
		'latest-past/event/date',
		'latest-past/event/title',
		'latest-past/event/venue',
		'latest-past/event/description',
		'latest-past/event/cost',
		'latest-past/event/date-tag',
		'latest-past/event/date/meta',
	];

	/**
	 * Indicates Latest Past View supports the date as a query argument appended to its URL, not as part of a "pretty" URL.
	 *
	 * @var bool
	 */
	protected static $date_in_url = false;

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( Tribe__Context $context = null ) {
		$context = null !== $context ? $context : $this->context;
		$this->repository = tribe_events();

		$date                   = $context->get( 'event_date', 'now' );
		$args['posts_per_page'] = $this->context->get( 'latest_past_per_page', 3 );
		$args['order_by']       = 'event_date';
		$args['order']          = 'DESC';
		$args['ends_before']    = $date;

		return $args;
	}

	/**
	 * Add Filters for Whitelist and Adding View HTML.
	 *
	 * @since 5.1.0
	 */
	public function add_view_filters() {

		add_filter( 'tribe_template_html:events/v2/components/messages', [ $this, 'filter_template_done' ] );
		add_filter( 'tribe_template_html:events/v2/components/ical-link', [ $this, 'add_view' ] );
	}

	/**
	 * Connect Whitelist Filter to Tribe Template Done to Prevent some of the current View's
	 * Templates from Displaying when the Latest Past Events Displays.
	 *
	 * @since 5.1.0
	 */
	public function filter_template_done( $html ) {

		add_filter( 'tribe_template_done', [ $this, 'filter_template_display_by_whitelist' ], 10, 4 );

		return $html;
	}

	/**
	 * Filter the Template Files and Only Return HTML if in Whitelist.
	 *
	 * @since 5.1.0
	 *
	 * @param string  $done    Whether to continue displaying the template or not.
	 * @param array   $name    Template name.
	 * @param array   $context Any context data you need to expose to this file.
	 * @param boolean $echo    If we should also print the Template.
	 *
	 * @return string
	 */
	public function filter_template_display_by_whitelist( $done, $name, $context, $echo ) {

		if ( in_array( $name, $this->whitelist, true ) ) {
			return $done;
		}

		return '';
	}

	/**
	 * Add the HTML for Latest Past Events to the HTML of the View Being Rendered.
	 *
	 * @since 5.1.0
	 *
	 * @param $html string The HTML of the view being rendered.
	 *
	 * @return string The HTML of the View being Rendered and Latest Past Events HTML
	 */
	public function add_view( $html ) {

		return $this->get_html();
	}
}
