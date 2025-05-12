<?php
/**
 * QR Code Widget
 *
 * @since   6.12.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

use Tribe__Context as Context;
use Tribe__Events__Main as TEC;


/**
 * Class for the QR Code Widget.
 *
 * @since   6.12.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Widget_QR_Code extends Widget_Abstract {
	/**
	 * If this Widget was rendered on the screen, often useful for Assets.
	 *
	 * @since 6.12.0
	 *
	 * @var string
	 */
	protected static $widget_in_use;

	/**
	 * Slug of the current widget.
	 *
	 * @since 6.12.0
	 *
	 * @var string
	 */
	protected static $widget_slug = 'events-qr-code';

	/**
	 * The slug of the widget view.
	 *
	 * @since 6.12.0
	 *
	 * @var string
	 */
	protected $view_slug = 'widget-events-qr-code';

	/**
	 * Widget css group slug.
	 *
	 * @since 6.12.0
	 *
	 * @var string
	 */
	protected static $widget_css_group = 'events-qr-code-widget';

	/**
	 * Default arguments to be merged into final arguments of the widget.
	 *
	 * @since 6.12.0
	 *
	 * @var array<string,mixed>
	 */
	protected $default_arguments = [
		// View options.
		'view'              => null,
		'should_manage_url' => false,

		// Event widget options.
		'id'                => null,
		'alias-slugs'       => null,
		'widget_title'      => '',
		'qr_code_size'      => '4',
		'redirection'       => 'current',
		'event_id'          => '',
		'series_id'         => '',
	];

	/**
	 * Gets the default widget name.
	 *
	 * @since 6.12.0
	 *
	 * @return string Returns the default widget name.
	 */
	public static function get_default_widget_name() {
		return esc_html_x( 'Events QR Code', 'The name of the QR Code Widget.', 'the-events-calendar' );
	}

	/**
	 * Gets the default widget options.
	 *
	 * @since 6.12.0
	 *
	 * @return array Default widget options.
	 */
	public static function get_default_widget_options() {
		return [
			'description' => esc_html_x( 'Shows a QR Code for an event.', 'The description of the QR Code Widget.', 'the-events-calendar' ),
		];
	}

	/**
	 * Setup the view for the widget.
	 *
	 * @since 6.12.0
	 *
	 * @param array<string,mixed> $_deprecated The widget arguments, as set by the user in the widget string.
	 */
	public function setup_view( $_deprecated ) {
		parent::setup_view( $_deprecated );

		add_filter( 'tribe_customizer_should_print_widget_customizer_styles', '__return_true' );
	}

	/**
	 * Setup the widgets default arguments.
	 *
	 * @since 6.12.0
	 *
	 * @return array<string,mixed> The array of widget default arguments.
	 */
	protected function setup_default_arguments() {
		// Call parent first to set up admin fields.
		parent::setup_default_arguments();

		// Setup default title.
		$this->default_arguments['widget_title'] = _x( 'Events QR Code', 'The default title of the QR Code Widget.', 'the-events-calendar' );

		return $this->default_arguments;
	}

	/**
	 * Add hooks for the widget.
	 *
	 * @since 6.12.0
	 */
	protected function add_hooks() {
		parent::add_hooks();

		add_filter( 'tribe_events_virtual_assets_should_enqueue_widget_styles', '__return_true' );
		add_filter( 'tribe_events_virtual_assets_should_enqueue_widget_groups', [ $this, 'add_self_to_virtual_widget_groups' ] );
	}

	/**
	 * Remove hooks for the widget.
	 *
	 * @since 6.12.0
	 */
	protected function remove_hooks() {
		parent::remove_hooks();

		remove_filter( 'tribe_events_virtual_assets_should_enqueue_widget_styles', '__return_true' );
		remove_filter( 'tribe_events_virtual_assets_should_enqueue_widget_groups', [ $this, 'add_self_to_virtual_widget_groups' ] );
	}

	/**
	 * Add this widget's css group to the VE list of widget groups to load icon styles for.
	 *
	 * @since 6.12.0
	 *
	 * @param array<string> $groups The list of widget groups.
	 *
	 * @return array<string> The modified list of widgets.
	 */
	public function add_self_to_virtual_widget_groups( $groups ) {
		$groups[] = static::get_css_group();

		return $groups;
	}

	/**
	 * Sanitizes the widget form values as they are saved.
	 *
	 * @since 6.12.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array<string,mixed> Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$updated_instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$updated_instance['widget_title'] = wp_strip_all_tags( $new_instance['widget_title'] );
		$updated_instance['qr_code_size'] = sanitize_text_field( $new_instance['qr_code_size'] );
		$updated_instance['redirection']  = sanitize_text_field( $new_instance['redirection'] );
		$updated_instance['event_id']     = absint( $new_instance['event_id'] ?? 0 );
		$updated_instance['series_id']    = absint( $new_instance['series_id'] ?? 0 );
		return $this->filter_updated_instance( $updated_instance, $new_instance );
	}

	/**
	 * Sets up the widgets default admin fields.
	 *
	 * @since 6.12.0
	 *
	 * @return array<string,mixed> The array of widget admin fields.
	 */
	public function setup_admin_fields() {
		$options = [
			[
				'value' => 'current',
				'text'  => _x( 'Redirect to the current event', 'Current event redirection option', 'the-events-calendar' ),
			],
			[
				'value' => 'upcoming',
				'text'  => _x( 'Redirect to the first upcoming event', 'Upcoming event redirection option', 'the-events-calendar' ),
			],
			[
				'value' => 'specific',
				'text'  => _x( 'Redirect to a specific event ID', 'Specific event redirection option', 'the-events-calendar' ),
			],
		];

		/**
		 * Filters the redirection options for the QR Code widget.
		 *
		 * @since 6.12.0
		 *
		 * @param array $options The array of redirection options.
		 */
		$options = apply_filters( 'tec_events_qr_widget_options', $options );

		$event_options = [];

		$args = [
			'posts_per_page' => -1,
			'post_type'      => TEC::POSTTYPE,
			'post_status'    => 'publish',
			'orderby'        => 'ID',
			'order'          => 'DESC',
		];

		$events = tribe_get_events( $args );
		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				$event_options[] = [
					'value' => $event->ID,
					'text'  => "{$event->ID} - {$event->post_title}",
				];
			}
		} else {
			$event_options[] = [
				'value' => '',
				'text'  => esc_html__( 'No Events have been created yet.', 'the-events-calendar' ),
			];
		}

		$fields = [
			'widget_title' => [
				'id'    => 'widget_title',
				'label' => _x( 'Title:', 'The label for the widget title setting.', 'the-events-calendar' ),
				'type'  => 'text',
			],
			'qr_code_size' => [
				'id'      => 'qr_code_size',
				'label'   => _x( 'QR Code Size:', 'The label for the QR code size setting.', 'the-events-calendar' ),
				'type'    => 'dropdown',
				'options' => [
					[
						'value' => '4',
						'text'  => _x( '140 x 140 px', 'Tiny QR code size option', 'the-events-calendar' ),
					],
					[
						'value' => '8',
						'text'  => _x( '280 x 280 px', 'Extra small QR code size option', 'the-events-calendar' ),
					],
					[
						'value' => '12',
						'text'  => _x( '420 x 420 px', 'Small QR code size option', 'the-events-calendar' ),
					],
					[
						'value' => '16',
						'text'  => _x( '560 x 560 px', 'Medium QR code size option', 'the-events-calendar' ),
					],
					[
						'value' => '20',
						'text'  => _x( '700 x 700 px', 'Regular QR code size option', 'the-events-calendar' ),
					],
					[
						'value' => '24',
						'text'  => _x( '840 x 840 px', 'Large QR code size option', 'the-events-calendar' ),
					],
					[
						'value' => '28',
						'text'  => _x( '980 x 980 px', 'Extra large QR code size option', 'the-events-calendar' ),
					],
				],
			],
			'redirection'  => [
				'id'      => 'redirection',
				'label'   => _x( 'Redirection Behavior:', 'The label for the redirection behavior setting.', 'the-events-calendar' ),
				'type'    => 'dropdown',
				'classes' => 'tribe-dependency',
				'options' => $options,
			],
			'event_id'     => [
				'id'             => 'event_id',
				'label'          => _x( 'Event ID:', 'The label for the specific event ID setting.', 'the-events-calendar' ),
				'type'           => 'dropdown',
				'parent_classes' => 'hidden',
				'classes'        => 'tribe-dependent',
				'options'        => $event_options,
				'dependency'     => [
					'ID' => 'redirection',
					'is' => 'specific',
				],
			],
		];

		return apply_filters( 'tec_events_qr_widget_fields', $fields );
	}

	/**
	 * Translates widget arguments to context.
	 *
	 * @since 6.12.0
	 *
	 * @param array<string, mixed> $arguments — Current set of arguments.
	 * @param \Tribe__Context      $context — The request context.
	 *
	 * @return array<string, mixed> — The translated widget arguments.
	 */
	protected function args_to_context( array $arguments, Context $context ) {
		$alterations = parent::args_to_context( $arguments, $context );

		// Widget title.
		$alterations['widget_title'] = sanitize_text_field( $arguments['widget_title'] );

		// QR Code Size.
		$alterations['qr_code_size'] = sanitize_text_field( $arguments['qr_code_size'] );

		// Redirection behavior.
		$alterations['redirection'] = sanitize_text_field( $arguments['redirection'] );

		// Specific event ID.
		$alterations['event_id'] = absint( $arguments['event_id'] );

		return $this->filter_args_to_context( $alterations, $arguments );
	}

	/**
	 * Gets the admin fields for the widget.
	 *
	 * @since 6.12.0
	 *
	 * @return array<string,mixed> The array of widget admin fields.
	 */
	public function get_admin_fields() {
		$fields    = $this->setup_admin_fields();
		$arguments = $this->get_arguments();
		$fields    = $this->filter_admin_fields( $fields );

		foreach ( $fields as $field_name => $field ) {
			$fields[ $field_name ] = $this->get_admin_data( $arguments, $field_name, $field );
		}

		return $fields;
	}
}
