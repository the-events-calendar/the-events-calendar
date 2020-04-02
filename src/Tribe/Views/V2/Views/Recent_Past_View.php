<?php
/**
 * The Past Recent View.
 *
 * @package Tribe\Events\Views\V2\Views
 * @since TBD
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Traits\List_Behavior;
use Tribe__Context;
use Tribe__Events__Main as TEC;
use Tribe__Events__Rewrite as TEC_Rewrite;
use Tribe__Utils__Array as Arr;
use Tribe\Events\Views\V2\Utils;

class Recent_Past_View extends View {
	use List_Behavior;
	/**
	 * Slug for this view
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $slug = 'recent-past';

	/**
	 * Visibility for this view.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected static $publicly_visible = true;

	/**
	 * Indicates Recent Past View supports the date as a query argument appended to its URL, not as part of a "pretty" URL.
	 *
	 * @var bool
	 */
	protected static $date_in_url = false;

		// Todo: remove this.

	public function maybe_filter_current_view_slug( $view_slug, $context, $query ) {

		/**
		 *
		 *
		 * @since TBD
		 *
		 * @param string $return_value Which value we are going to return as the conversion.
		 */
		$prevent_recent_past = apply_filters( 'tribe_events_filter_recent_post_slug', false );

		if ( $prevent_recent_past ) {
			return $view_slug;
		}

		if ( tribe_events()->where( 'ends_after', 'now' )->count() === 0 ) {

			return 'recent-past';
		}

		return $view_slug;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( Tribe__Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		$args = parent::setup_repository_args( $context );

		$date = $context->get( 'event_date', 'now' );

		$args['posts_per_page'] = $context->get( 'events_per_page', 3 );
		$args['order_by']       = 'event_date';
		$args['order']          = 'DESC';
		$args['ends_before']    = $date;

		return $args;
	}

	/**
	 * Overrides the base View method to fix the order of the events in the `past` display mode.
	 *
	 * @since TBD
	 *
	 * @return array The List View template vars, modified if required.
	 */
	protected function setup_template_vars() {
		$template_vars = parent::setup_template_vars();

		$template_vars = $this->setup_datepicker_template_vars( $template_vars );

		return $template_vars;
	}

}
