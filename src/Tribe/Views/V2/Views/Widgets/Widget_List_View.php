<?php
/**
 * The List Widget View.
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 * @since 5.2.1
 */

namespace Tribe\Events\Views\V2\Views\Widgets;

use Tribe\Events\Views\V2\Messages;
use Tribe\Events\Views\V2\View;
use Tribe__Context as Context;

/**
 * Class List_Widget_View
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 */
class Widget_List_View extends View {

	/**
	 * The slug for this view.
	 *
	 * @since 5.2.1
	 *
	 * @var string
	 */
	protected $slug = 'widget-events-list';

	/**
	 * The slug for the template path.
	 *
	 * @since 5.2.1
	 *
	 * @var string
	 */
	protected $template_path = 'widgets';

	/**
	 * Visibility for this view.
	 *
	 * @since 5.2.1
	 *
	 * @var bool
	 */
	protected static $publicly_visible = false;

	/**
	 * Whether the View should display the events bar or not.
	 *
	 * @since 5.2.1
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
	 * @return array<string,mixed> The arguments, ready to be set on the View repository instance.
	 */
	protected function setup_repository_args( Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		$args = parent::setup_repository_args( $context );
		$args['ends_after'] = 'now';

		return $args;
	}

	/**
	 * Overrides the base View method.
	 *
	 * @since TBD
	 *
	 * @return array<string,mixed> The Widget List View template vars, modified if required.
	 */
	protected function setup_template_vars() {
		$template_vars = parent::setup_template_vars();

		// Here update, add and remove from the default template vars.
		$template_vars['view_more_link']     = tribe_get_events_link();
		$template_vars['widget_title']       = $this->context->get( 'widget_title' );
		$template_vars['no_upcoming_events'] = $this->context->get( 'no_upcoming_events' );
		$template_vars['show_latest_past']   = false;
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
	 * Modify the setup the loop method to only set the repository arguments.
	 *
	 * @since TBD
	 */
	public function setup_the_loop( array $args = [] ) {
		$args = wp_parse_args( $args, $this->repository_args );

		$this->repository->by_args( $args );
	}
}
