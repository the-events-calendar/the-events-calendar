<?php
/**
 * The Month View.
 *
 * @package Tribe\Events\Views\V2\Views
 * @since 4.9.3
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe__Utils__Array as Arr;
use Tribe__Context as Context;

class Month_View extends View {

	/**
	 * Slug for this view.
	 *
	 * @since 4.9.3
	 *
	 * @var string
	 */
	protected $slug = 'month';

	/**
	 * Visibility for this view.
	 *
	 * @since 4.9.4
	 *
	 * @var bool
	 */
	protected $publicly_visible = true;

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		$date = Arr::get( $context, 'eventDate', 'now' );

		if ( 'past' !== Arr::get( $context, 'event_display', 'current' ) ) {
			$args['ends_after'] = $date;
		} else {
			$args['ends_before'] = $date;
		}

		return $args;
	}
}
