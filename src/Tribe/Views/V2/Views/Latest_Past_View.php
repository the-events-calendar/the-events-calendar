<?php
/**
 * The Past Latest View.
 *
 * @since   5.3.0
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Traits\With_Noindex;
use Tribe__Context;

class Latest_Past_View extends List_View {
	use With_Noindex;
  
	/**
	 * Slug for this view
	 *
	 * @since      5.1.0
	 * @deprecated 6.0.7
	 *
	 * @var string
	 */
	protected $slug = 'latest-past';

	/**
	 * Statically accessible slug for this view.
	 *
	 * @since 6.0.7
	 *
	 * @var string
	 */
	protected static $view_slug = 'latest-past';

	/**
	 * Visibility for this view.
	 *
	 * @since 5.1.0
	 *
	 * @var bool
	 */
	protected static $publicly_visible = false;

	/**
	 * Safe list of Templates to display when Latest Past Events is Active.
	 *
	 * @since 5.1.0
	 *
	 * @var array
	 */

	protected $safelist = [
		// Common Components.
		'components/icons/caret-down',
		'components/icons/caret-left',
		'components/icons/caret-right',
		'components/icons/day',
		'components/icons/dot',
		'components/icons/error',
		'components/icons/featured',
		'components/icons/list',
		'components/icons/map',
		'components/icons/messages-not-found',
		'components/icons/month',
		'components/icons/photo',
		'components/icons/recurring',
		'components/icons/search',
		'components/icons/virtual',
		'components/icons/week',

		// Standard View Components.
		'components/after',
		'components/before',
		'components/breadcrumbs',
		'components/breakpoints',
		'components/data',
		'components/header',
		'components/header-title',
		'components/events-bar',
		'components/events-bar/search-button',
		'components/events-bar/search',
		'components/events-bar/search/keyword',
		'components/events-bar/search/submit',
		'components/events-bar/views',
		'components/events-bar/views/list',
		'components/events-bar/views/list/item',
		'components/ical-link',
		'components/json-ld-data',
		'components/loader',
		'components/messages',
		'components/top-bar/today',
		'components/top-bar/actions',

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
		'latest-past/event/date/featured',
		'latest-past/event/date/meta',
		'latest-past/event/featured-image',
		'latest-past/top-bar',

		// Add-ons.
		'components/filter-bar',
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
		$context          = null !== $context ? $context : $this->context;
		$this->repository = tribe_events();

		$date                   = $context->get( 'event_date', 'now' );
		$args['posts_per_page'] = $this->context->get( 'latest_past_per_page', 3 );
		$args['order_by']       = 'event_date';
		$args['order']          = 'DESC';
		$args['ends_before']    = $date;

		return $args;
	}

	/**
	 * Add Filters for safe list and Adding View HTML.
	 *
	 * @since 5.1.0
	 */
	public function add_view_filters() {
		add_filter( 'tribe_template_html:events/v2/components/before', [ $this, 'filter_template_done' ] );
		add_filter( 'tribe_template_html:events/v2/components/ical-link', [ $this, 'add_view' ] );
	}

	/**
	 * Connect safe list Filter to Tribe Template Done to Prevent some of the current View's
	 * Templates from Displaying when the Latest Past Events Displays.
	 *
	 * @since 5.1.0
	 */
	public function filter_template_done( $html ) {
		add_filter( 'tribe_template_done', [ $this, 'filter_template_display_by_safelist' ], 10, 4 );

		return $html;
	}

	/**
	 * Filter the Template Files and Only Return HTML if in safe list.
	 *
	 * @since 5.1.0
	 *
	 * @param string       $done    Whether to continue displaying the template or not.
	 * @param array|string $name    Template name.
	 * @param array        $context Any context data you need to expose to this file.
	 * @param boolean      $echo    If we should also print the Template.
	 *
	 * @return string
	 */
	public function filter_template_display_by_safelist( $done, $name, $context, $echo ) {
		if ( is_array( $name ) ) {
			$name = implode( '/', $name );
		}
		$display = in_array( $name, $this->safelist, true );

		/**
		 * Filters whether a specific template should show in the context of the Latest Past Events View or not.
		 *
		 * @since 5.2.0
		 *
		 * @param bool   $display Whether a specified template should display or not.
		 * @param string $name    The template name.
		 * @param array  $context The data context for this template inclusion.
		 * @param bool   $echo    Whether the template inclusion is attempted to then echo to the page, or not.
		 */
		$display = apply_filters( 'tribe_events_latest_past_view_display_template', $display, $name, $context, $echo );

		if ( $display ) {
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
