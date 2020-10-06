<?php
/**
 * List Widget
 *
 * @package Tribe\Events\Views\V2\Widgets
 * @since   TBD
 */
namespace Tribe\Events\Views\V2\Widgets;

use Tribe\Events\Pro\Views\V2\Assets as Pro_Assets;
use Tribe\Events\Pro\Views\V2\Shortcodes\Tribe_Events;
use Tribe\Events\Views\V2\Assets as Event_Assets;
use Tribe\Events\Views\V2\Manager as Views_Manager;
use Tribe\Events\Views\V2\Theme_Compatibility;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\View_Interface;
use Tribe\Widget\Widget_Abstract;
use Tribe__Context as Context;
use Tribe__Events__Main as TEC;
use Tribe__Utils__Array as Arr;

/**
 * Class for List Widget.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class List_Widget extends Widget_Abstract {

	/**
	 * {@inheritDoc}
	 */
	protected $slug = 'tribe_events_list_widget';

	/**
	 * {@inheritDoc}
	 */
	protected $default_arguments = [
		// General widget properties
		'title'         => '',

		// View options
		'view'                 => null,
		'should_manage_url'    => false,

		// Event widget options
		'id'                   => null,
		'alias-slugs'          => null,
		'limit'                => 5,
		'no_upcoming_events'   => '',
		'featured_events_only' => false,
		'tribe_is_list_widget' => true,
		'jsonld_enable'        => true,

		// WP_Widget properties
		'id_base'              => 'tribe-events-list-widget',
		'name'                 => null,
		'widget_options'       => [
			'classname'   => 'tribe-events-list-widget',
			'description' => null
		],
		'control_options'      => [
			'id_base' => 'tribe-events-list-widget'
		],
	];

	/**
	 * {@inheritDoc}
	 */
	protected $validate_arguments_map = [
		'should_manage_url'    => 'tribe_is_truthy',
		'no_upcoming_events'   => 'tribe_is_truthy',
		'featured_events_only' => 'tribe_is_truthy',
		'jsonld_enable'        => 'tribe_is_truthy',
	];

	public function get_arguments() {
		$arguments = $this->default_arguments;

		$arguments['description'] = esc_html__( 'A widget that displays upcoming events.', 'the-events-calendar' );
		$arguments['name'   ]         = esc_html__( 'Events List V2', 'the-events-calendar' );
		$arguments['widget_options']['description'] = esc_html__( 'A widget that displays upcoming events.', 'the-events-calendar' );

		return $this->filter_arguments( $arguments );
	}

	/**
	 * Alters the shortcode context with its arguments.
	 *
	 * @since  TBD
	 *
	 * @param \Tribe__Context $context Context we will use to build the view.
	 *
	 * @return \Tribe__Context Context after shortcodes changes.
	 */
	public function alter_context( Context $context, array $arguments = [] ) {

		$alter_context = $this->args_to_context( $arguments, $context );

		// The View will consume this information on initial state.
		$alter_context['id']        = $this->slug;

		$context = $context->alter( $alter_context );

		return $context;
	}

	/**
	 * Translates shortcode arguments to their Context argument counterpart.
	 *
	 * @since TBD
	 *
	 * @param array   $arguments The shortcode arguments to translate.
	 * @param Context $context The request context.
	 *
	 * @return array The translated shortcode arguments.
	 */
	protected function args_to_context( array $arguments, Context $context ) {
		$context_args = [];

		$category_input = Arr::get_first_set( $arguments, [ 'cat', 'category' ], false );

		if ( ! empty( $category_input ) ) {
			$context_args['event_category'] = Arr::list_to_array( $category_input );
		}

		if ( ! empty( $arguments['date'] ) ) {
			$context_args['event_date'] = $arguments['date'];
		}

		if ( isset( $arguments['featured'] ) ) {
			$context_args['featured'] = tribe_is_truthy( $arguments['featured'] );
		}

		if ( null === $context->get( 'eventDisplay' ) ) {
			if ( empty( $arguments['view'] ) ) {
				$default_view_class                 = tribe( Views_Manager::class )->get_default_view();
				$context_args['event_display_mode'] = tribe( Views_Manager::class )->get_view_slug_by_class( $default_view_class );
			} else {
				$context_args['event_display_mode'] = $arguments['view'];
			}
		}

		return $context_args;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html() {
		$html_test = '<p>V2 List Widget</p>';

		$context = tribe_context();

		// Modifies the Context for the shortcode params.
		$context   = $this->alter_context( $context );

		// Fetches if we have a specific view are building.
		$view_slug = $this->get_argument( 'view', $context->get( 'view' ) );

		// Setup the view instance.
		$view = View::make( $view_slug, $context );

		$html = $view->get_html();

		return $html_test;
	}
}
