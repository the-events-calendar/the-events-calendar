<?php
/**
 * The List Widget View.
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 * @since TBD
 */

namespace Tribe\Events\Views\V2\Views\Widgets;

use Tribe\Events\Views\V2\View;
use Tribe__Context as Context;
use Tribe__Utils__Array as Arr;

/**
 * Class List_Widget_View
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 */
class Widget_List_View extends View {

	/**
	 * Slug for this view
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $slug = 'widget-list';

	/**
	 * Visibility for this view.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected static $publicly_visible = false;

	/**
	 * Whether the View should display the events bar or not.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected $display_events_bar = false;

	/**
	 * Sets up the View repository arguments from the View context or a provided Context object.
	 *
	 * @since TBD
	 *
	 * @param  Context|null $context A context to use to setup the args, or `null` to use the View Context.
	 *
	 * @return array The arguments, ready to be set on the View repository instance.
	 */
	protected function setup_repository_args( Context $context = null ) {
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
