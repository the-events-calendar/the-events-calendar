<?php
/**
 * The List Widget View.
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 * @since TBD
 */

namespace Tribe\Events\Views\V2\Views\Widgets;

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Traits\List_Behavior;
use Tribe__Context;
use Tribe__Utils__Array as Arr;

class List_View extends View {
	use List_Behavior;
	/**
	 * Slug for this view
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $slug = 'list-widget';

	/**
	 * Visibility for this view.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected static $publicly_visible = true;

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( Tribe__Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		$args = parent::setup_repository_args( $context );

		$context_arr = $context->to_array();

		$date = Arr::get( $context_arr, 'event_date', 'now' );

		$args['ends_after'] = $date;
		$args['order']      = 'ASC';

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

		return $template_vars;
	}
}
