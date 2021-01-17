<?php
/**
 * List Widget
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

use Tribe\Events\Views\V2\Assets;
use Tribe__Context as Context;

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
	protected $view_slug = 'widget-events-list';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $asset_slug = 'tribe-events-list-widget-v2';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $view_admin_slug = 'widgets/list';

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
		'tribe_is_list_widget' => true,

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
	public function setup_view( $arguments ) {
		parent::setup_view( $arguments );

		add_filter( 'tribe_customizer_should_print_widget_customizer_styles', '__return_true' );
		add_filter( 'tribe_customizer_inline_stylesheets', [ $this, 'add_full_stylesheet_to_customizer' ], 12, 2 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function enqueue_assets( $context, $view ) {
		parent::enqueue_assets( $context, $view );

		// Ensure we also have all the other things from Tribe\Events\Views\V2\Assets we need.
		tribe_asset_enqueue( 'tribe-events-widgets-v2-events-list-skeleton' );

		if ( tribe( Assets::class )->should_enqueue_full_styles() ) {
			tribe_asset_enqueue( 'tribe-events-widgets-v2-events-list-full' );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_default_arguments() {
		$default_arguments = parent::setup_default_arguments();

		$default_arguments['description'] = esc_html_x( 'A widget that displays upcoming events.', 'The description of the List Widget.', 'the-events-calendar' );
		// @todo update name once this widget is ready to replace the existing list widget.
		$default_arguments['name']                          = esc_html_x( 'Events List', 'The name of the List Widget.', 'the-events-calendar' );
		$default_arguments['widget_options']['description'] = esc_html_x( 'A widget that displays upcoming events.', 'The description of the List Widget.', 'the-events-calendar' );
		// Setup default title.
		$default_arguments['title'] = _x( 'Upcoming Events', 'The default title of the List Widget.', 'the-events-calendar' );

		return $default_arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function update( $new_instance, $old_instance ) {
		$updated_instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$updated_instance['title']                = wp_strip_all_tags( $new_instance['title'] );
		$updated_instance['limit']                = $new_instance['limit'];
		$updated_instance['no_upcoming_events']   = ! empty( $new_instance['no_upcoming_events'] );
		$updated_instance['featured_events_only'] = ! empty( $new_instance['featured_events_only'] );
		$updated_instance['jsonld_enable']        = ! empty( $new_instance['jsonld_enable'] );
		$updated_instance['tribe_is_list_widget'] = ! empty( $new_instance['tribe_is_list_widget'] );

		return $this->filter_updated_instance( $updated_instance, $new_instance );
	}

	/**
	 * {@inheritDoc}
	 */
	public function setup_admin_fields() {
		return [
			'title'                => [
				'label' => _x( 'Title:', 'The label for the field of the title of the List Widget.', 'the-events-calendar' ),
				'type'  => 'text',
			],
			'limit'                => [
				'label'   => _x( 'Show:', 'The label for the amount of events to show in the List Widget.', 'the-events-calendar' ),
				'type'    => 'dropdown',
				'options' => $this->get_limit_options(),
			],
			'no_upcoming_events'   => [
				'label' => _x( 'Hide this widget if there are no upcoming events.', 'The label for the option to hide the List Widget if no upcoming events.', 'the-events-calendar' ),
				'type'  => 'checkbox',
			],
			'featured_events_only' => [
				'label' => _x( 'Limit to featured events only', 'The label for the option to only show featured events in the List Widget', 'the-events-calendar' ),
				'type'  => 'checkbox',
			],
			'jsonld_enable'        => [
				'label' => _x( 'Generate JSON-LD data', 'The label for the option to enable JSON-LD in the List Widget.', 'the-events-calendar' ),
				'type'  => 'checkbox',
			],
		];
	}

	/**
	 * Get the options to use in a the limit dropdown.
	 *
	 * @since 5.3.0
	 *
	 * @return array<string,mixed> An array of options with the text and value included.
	 */
	public function get_limit_options() {
		/**
		 * Filter the max limit of events to display in the List Widget.
		 *
		 * @since 5.3.0
		 *
		 * @param int The max limit of events to display in the List Widget, default 10.
		 */
		$events_limit = apply_filters( 'tribe_events_widget_list_events_max_limit', 10 );

		$options = [];

		foreach ( range( 1, $events_limit ) as $i ) {
			$options[] = [
				'text'  => $i,
				'value' => $i,
			];
		}

		return $options;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function args_to_context( array $arguments, Context $context ) {
		$alterations = parent::args_to_context( $arguments, $context );

		// Only Featured Events.
		$alterations['featured'] = tribe_is_truthy( $arguments['featured_events_only'] );

		// Enable JSON-LD?
		$alterations['jsonld_enable'] = (int) tribe_is_truthy( $arguments['jsonld_enable'] );

		// Hide widget if no events.
		$alterations['no_upcoming_events'] = tribe_is_truthy( $arguments['no_upcoming_events'] );

		// Add posts per page.
		$alterations['events_per_page'] = (int) isset( $arguments['limit'] ) && $arguments['limit'] > 0 ?
			(int) $arguments['limit'] :
			5;

		/**
		 * Applies a filter to the args to context.
		 *
		 * @since 5.3.0
		 *
		 * @param array<string,mixed> $alterations The alterations to make to the context.
		 * @param array<string,mixed> $arguments   Current set of arguments.
		 */
		return apply_filters( 'tribe_events_views_v2_list_widget_args_to_context', $alterations, $arguments );
	}

	/**
	 * Empties the json_ld_data if jsonld_enable is false,
	 * removing the need for additional checks in the template.
	 *
	 * @since 5.3.0
	 *
	 * @param array<string,mixed> $template_vars The current template variables.
	 *
	 * @return array<string,mixed> The modified template variables.
	 */
	public function disable_json_data( $template_vars ) {
		if (
			isset( $template_vars['jsonld_enable'] )
			&& ! tribe_is_truthy( $template_vars['jsonld_enable'] )
		) {
			$template_vars['json_ld_data'] = '';
		}

		return $template_vars;
	}

	/**
	 * Add full events list widget stylesheets to customizer styles array to check.
	 *
	 * @since 5.3.0
	 *
	 * @param array<string> $sheets       Array of sheets to search for.
	 * @param string        $css_template String containing the inline css to add.
	 *
	 * @return array Modified array of sheets to search for.
	 */
	public function add_full_stylesheet_to_customizer( $sheets, $css_template ) {
		return array_merge( $sheets, [ 'tribe-events-widgets-v2-events-list-full' ] );
	}
}
