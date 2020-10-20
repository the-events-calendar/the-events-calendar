<?php
/**
 * List Widget
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

/**
 * Class for the List Widget.
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Widget_List extends Widget_Abstract {

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $slug = 'tribe_events_list_widget';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $view_slug = 'widget-list';

	/**
	 * {@inheritDoc}
	 *
	 * @var array<string,mixed>
	 */
	protected $default_arguments = [
		// View options.
		'view'                 => null,
		'should_manage_url'    => false,

		// Event widget options.
		'id'                   => null,
		'alias-slugs'          => null,
		'title'                => '',
		'limit'                => 5,
		'no_upcoming_events'   => '',
		'featured_events_only' => false,
		'tribe_is_list_widget' => true,
		'jsonld_enable'        => true,

		// WP_Widget properties.
		'id_base'              => 'tribe-events-list-widget',
		'name'                 => null,
		'widget_options'       => [
			'classname'   => 'tribe-events-list-widget',
			'description' => null,
		],
		'control_options'      => [
			'id_base' => 'tribe-events-list-widget',
		],
	];

	/**
	 * @todo update in TEC-3612 & TEC-3613
	 *
	 * {@inheritDoc}
	 *
	 * @var array<string,mixed>
	 */
	protected $validate_arguments_map = [
		'should_manage_url'    => 'tribe_is_truthy',
		'no_upcoming_events'   => 'tribe_is_truthy',
		'featured_events_only' => 'tribe_is_truthy',
		'jsonld_enable'        => 'tribe_is_truthy',
	];

	/**
	 * @todo update in TEC-3612 & TEC-3613
	 *
	 * {@inheritDoc}
	 */
	public function setup() {
		$this->setup_view();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_arguments() {
		$arguments = $this->arguments;

		$arguments['description'] = esc_html__( 'A widget that displays upcoming events.', 'the-events-calendar' );
		// @todo update name once this widget is ready to replace the existing list widget.
		$arguments['name']                          = esc_html__( 'Events List V2', 'the-events-calendar' );
		$arguments['widget_options']['description'] = esc_html__( 'A widget that displays upcoming events.', 'the-events-calendar' );

		$arguments = wp_parse_args( $arguments, $this->get_default_arguments() );

		return $this->filter_arguments( $arguments );
	}
}
