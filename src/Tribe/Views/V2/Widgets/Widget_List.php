<?php
/**
 * List Widget
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

/**
 * Class for the List Widget.
 *
 * @since   TBD
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
		'no_upcoming_events'   => false,
		'featured_events_only' => false,
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
	 * {@inheritDoc}
	 */
	public function get_arguments() {
		$arguments = $this->arguments;

		$arguments['description'] = esc_html__( 'A widget that displays upcoming events.', 'the-events-calendar' );
		// @todo update name once this widget is ready to replace the existing list widget.
		$arguments['name']                          = esc_html__( 'Events List V2', 'the-events-calendar' );
		$arguments['widget_options']['description'] = esc_html__( 'A widget that displays upcoming events.', 'the-events-calendar' );

		// Setup default title.
		$arguments['title'] = __( 'Upcoming Events', 'the-events-calendar' );

		// Setup admin fields.
		$arguments['admin_fields'] = $this->get_admin_fields();

		// Add the Widget to the arguments to pass to the admin template.
		$arguments['widget_obj'] = $this;

		$arguments = wp_parse_args( $arguments, $this->get_default_arguments() );

		return $this->filter_arguments( $arguments );
	}

	/**
	 * {@inheritDoc}
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title']                = wp_strip_all_tags( $new_instance['title'] );
		$instance['limit']                = $new_instance['limit'];
		$instance['no_upcoming_events']   = isset( $new_instance['no_upcoming_events'] ) && $new_instance['no_upcoming_events'] ? true : false;
		$instance['featured_events_only'] = isset( $new_instance['featured_events_only'] ) && $new_instance['featured_events_only'] ? true : false;
		$instance['jsonld_enable']        = ! empty( $new_instance['jsonld_enable'] ) ? 1 : 0;

		return $instance;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_admin_fields() {

		return [
			'title'                => [
				'label' => __( 'Title:', 'the-events-calendar' ),
				'type'  => 'text',
			],
			'limit'                => [
				'label'   => __( 'Show:', 'the-events-calendar' ),
				'type'    => 'dropdown',
				'options' => $this->get_limit_options(),
			],
			'no_upcoming_events'   => [
				'label' => __( 'Show widget only if there are upcoming events', 'the-events-calendar' ),
				'type'  => 'checkbox',
			],
			'featured_events_only' => [
				'label' => _x( 'Limit to featured events only', 'events list widget setting', 'the-events-calendar' ),
				'type'  => 'checkbox',
			],
			'jsonld_enable'        => [
				'label' => __( 'Generate JSON-LD data', 'the-events-calendar' ),
				'type'  => 'checkbox',
			],

		];
	}

	/**
	 * Get the options to use in a the limit dropdown.
	 *
	 * @since TBD
	 *
	 * @return array<string,mixed> An array of options with the text and value included.
	 */
	public function get_limit_options() {
		/**
		 * Filter the max limit of events to display in the List Widget.
		 *
		 * @since TBD
		 *
		 * @param int The max limit of events to display in the List Widget, default 10.
		 */
		$events_limit = apply_filters( 'tribe_events_widget_list_events_max_limit', 10 );

		$options = [];

		for ( $i = 1; $i <= $events_limit; $i ++ ) {
			$options[] = [
				'text'  => $i,
				'value' => $i,
			];
		}

		return $options;
	}
}
