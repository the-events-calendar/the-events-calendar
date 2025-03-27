<?php
/**
 * The Front End QR Code Widget View.
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 * @since TBD
 */

namespace Tribe\Events\Views\V2\Views\Widgets;

use Tribe\Events\Views\V2\Messages;
use Tribe__Context as Context;

/**
 * Class Widget_QR_Code_View
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 */
class Widget_QR_Code_View extends Widget_View {

	/**
	 * The slug for this view.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static $view_slug = 'widget-events-qr-code';

	/**
	 * Sets up the View repository arguments from the View context or a provided Context object.
	 *
	 * @since TBD
	 *
	 * @param  ?Context $context A context to use to setup the args, or `null` to use the View Context.
	 *
	 * @return array<string,mixed> The arguments, ready to be set on the View repository instance.
	 */
	protected function setup_repository_args( ?Context $context = null ) {
		$context ??= $this->context;
		$args      = parent::setup_repository_args( $context );

		// If we're redirecting to a specific event, we need to get that event.
		if ( $context->get( 'redirection' ) === 'specific' ) {
			$args['post__in'] = [ (int) $context->get( 'specific_event_id' ) ];
		}

		return $args;
	}

	/**
	 * Overrides the base View method.
	 *
	 * @since TBD
	 *
	 * @return array<string,mixed> The Widget QR Code View template vars, modified if required.
	 */
	protected function setup_template_vars() {
		$template_vars = parent::setup_template_vars();

		// Add our widget-specific variables.
		$template_vars['widget_title']      = $this->context->get( 'widget_title' );
		$template_vars['qr_code_size']      = $this->context->get( 'qr_code_size', '125' );
		$template_vars['redirection']       = $this->context->get( 'redirection', 'current' );
		$template_vars['specific_event_id'] = $this->context->get( 'specific_event_id' );

		return $template_vars;
	}

	/**
	 * Sets up the user-facing messages the View will print on the frontend.
	 *
	 * @since TBD
	 *
	 * @param array $events An array of the View events, if any.
	 *
	 * @return void
	 */
	protected function setup_messages( array $events ) {
		// If we're looking for a specific event and it's not found.
		if ( $this->context->get( 'redirection' ) === 'specific' && empty( $events ) ) {
			$this->messages->insert(
				Messages::TYPE_NOTICE,
				Messages::for_key( 'event_not_found' )
			);
			return;
		}

		// If we're looking for the next event in a series and it's not found.
		if ( $this->context->get( 'redirection' ) === 'next' && empty( $events ) ) {
			$this->messages->insert(
				Messages::TYPE_NOTICE,
				Messages::for_key( 'no_next_event' )
			);
			return;
		}
	}

	/**
	 * Overrides the base method to return an empty array, since the widget will not use breadcrumbs.
	 *
	 * @since TBD
	 *
	 * @return array An empty array, the widget will not use breadcrumbs.
	 */
	protected function get_breadcrumbs() {
		return [];
	}
}
