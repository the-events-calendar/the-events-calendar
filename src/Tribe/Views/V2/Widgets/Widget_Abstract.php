<?php
/**
 * Widget Abstract
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\View_Interface;
use Tribe__Context as Context;

/**
 * The abstract all widgets should implement.
 *
 * @since   TBD
 *
 * @package Tribe\Widget
 */
abstract class Widget_Abstract extends \Tribe\Widget\Widget_Abstract {

	/**
	 * The view interface for the widget.
	 *
	 * @since TBD
	 *
	 * @var View_Interface;
	 */
	protected $view;

	/**
	 * The slug of the widget view.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $view_slug;

	/**
	 * @todo update in TEC-3612 & TEC-3613
	 *
	 * {@inheritDoc}
	 * {@inheritDoc}default
	 */
	public function setup() {
		// Add the admin template class for the widget admin form.
		$this->admin_template = tribe( Admin_Template::class );

		// Setup the View for the frontend.
		$this->setup_view();
	}

	/**
	 * {@inheritDoc}
	 */
	public function form( $instance ) {

		add_filter(
			"tribe_widget_{$this->get_registration_slug()}_arguments",
			function ( array $arguments ) use ( $instance ) {
				return wp_parse_args(
					$instance,
					$arguments
				);
		} );

		$arguments = $this->get_arguments();

		$this->admin_template->template( 'widgets/list', $arguments );
	}

	/**
	 * The function for saving widget updates in the admin section.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array The new widget settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// todo update method so that is can be used in all the widgets.
		/* Strip tags (if needed) and update the widget settings. */
		$instance['title']                = strip_tags( $new_instance['title'] );
		$instance['limit']                = $new_instance['limit'];
		$instance['no_upcoming_events']   = isset( $new_instance['no_upcoming_events'] ) && $new_instance['no_upcoming_events'] ? true : false;
		$instance['featured_events_only'] = isset( $new_instance['featured_events_only'] ) && $new_instance['featured_events_only'] ? true : false;
		$instance['jsonld_enable']        = ! empty( $new_instance['jsonld_enable'] ) ? 1 : 0;

		return $instance;
	}

	/**
	 * Setup the view for the widget.
	 *
	 * @since TBD
	 */
	public function setup_view() {
		$context = tribe_context();

		// Modifies the Context for the widget params.
		// @todo update per https://github.com/moderntribe/tribe-common/pull/1451#discussion_r501498990.
		$context = $this->alter_context( $context );

		// Setup the view instance.
		$view = View::make( $this->get_view_slug(), $context );

		$view->get_template()->set_values( $this->get_arguments(), false );

		$this->set_view( $view );
	}

	/**
	 * Returns the rendered View HTML code.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_html() {
		return $this->get_view()->get_html();
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_view( View_Interface $view ) {
		$this->view = $view;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_view() {
		return $this->view;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_view_slug() {
		return $this->view_slug;
	}

	/**
	 * Alters the widget context with its arguments.
	 *
	 * @todo update in TEC-3620 & TEC-3597
	 *
	 * @since  TBD
	 *
	 * @param \Tribe__Context     $context   Context we will use to build the view.
	 * @param array<string,mixed> $arguments Current set of arguments.
	 *
	 * @return \Tribe__Context Context after widget changes.
	 */
	public function alter_context( Context $context, array $arguments = [] ) {
		// @todo update per https://github.com/moderntribe/tribe-common/pull/1451#discussion_r501498990.
		$alter_context = $this->args_to_context( $arguments, $context );

		$context = $context->alter( $alter_context );

		return $context;
	}

	/**
	 * Translates widget arguments to their Context argument counterpart.
	 *
	 * @todo update in TEC-3620 & TEC-3597
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $arguments Current set of arguments.
	 * @param Context             $context   The request context.
	 *
	 * @return array<string,mixed> The translated widget arguments.
	 */
	protected function args_to_context( array $arguments, Context $context ) {
		$context_args = [ 'widget' => true ];

		return $context_args;
	}
}
