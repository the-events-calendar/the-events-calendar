<?php
/**
 * The Front End List Widget View.
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 * @since 5.2.1
 */

namespace Tribe\Events\Views\V2\Views\Widgets;

use Tribe\Events\Views\V2\Messages;
use Tribe__Context as Context;

/**
 * Class Widget_List_View
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 */
class Widget_List_View extends Widget_View {

	/**
	 * The slug for this view.
	 *
	 * @since 5.2.1
	 * @deprecated 6.0.7
	 *
	 * @var string
	 */
	protected $slug = 'widget-events-list';

	/**
	 * The slug for this view.
	 *
	 * @since 6.0.7
	 *
	 * @var string
	 */
	protected static $view_slug = 'widget-events-list';

	/**
	 * Sets up the View repository arguments from the View context or a provided Context object.
	 *
	 * @since 5.3.0
	 *
	 * @param  Context|null $context A context to use to setup the args, or `null` to use the View Context.
	 *
	 * @return array<string,mixed> The arguments, ready to be set on the View repository instance.
	 */
	protected function setup_repository_args( Context $context = null ) {
		$context            = null !== $context ? $context : $this->context;
		$args               = parent::setup_repository_args( $context );
		$args['ends_after'] = 'now';

		return $args;
	}

	/**
	 * Overrides the base View method.
	 *
	 * @since 5.3.0
	 *
	 * @return array<string,mixed> The Widget List View template vars, modified if required.
	 */
	protected function setup_template_vars() {
		$template_vars = parent::setup_template_vars();

		// Here update, add and remove from the default template vars.
		$template_vars['widget_title']               = $this->context->get( 'widget_title' );
		$template_vars['hide_if_no_upcoming_events'] = $this->context->get( 'no_upcoming_events' );
		$template_vars['show_latest_past']           = false;

		// Display is modified with filters in Pro.
		$template_vars['display'] = [];

		return $template_vars;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_messages( array $events ) {
		if ( ! empty( $events ) ) {
			return;
		}

		$keyword = $this->context->get( 'keyword', false );
		$this->messages->insert(
			Messages::TYPE_NOTICE,
			Messages::for_key( 'no_upcoming_events', trim( $keyword ) )
		);
	}

	/**
	 * Overrides the base method to return an empty array, since the widget will not use breadcrumbs.
	 *
	 * @since 5.3.0
	 *
	 * @return array<array<string,string>> An empty array, the widget will not use breadcrumbs.
	 */
	protected function get_breadcrumbs() {
		return [];
	}
}
