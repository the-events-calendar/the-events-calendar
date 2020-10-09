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
	 * @todo update in TEC-3612 & TEC-3613
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
	 * @todo update in TEC-3612 & TEC-3613
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
