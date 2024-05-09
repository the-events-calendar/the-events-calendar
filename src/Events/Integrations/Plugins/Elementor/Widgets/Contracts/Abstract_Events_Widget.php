<?php
/**
 * List View Elementor Widget.
 *
 * @since 6.4.0
 *
 * @package Tribe\Events\Integrations\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts;

use TEC\Events\Integrations\Plugins\Elementor\Assets_Manager;
use TEC\Common\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Template_Engine;
use Tribe__Events__Main as TEC;
use WP_Post;

/**
 * Abstract Widget class
 *
 * All template widgets should extend this class.
 */
abstract class Abstract_Events_Widget extends Abstract_Widget {

	/**
	 * Widget slug.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	protected static string $slug;

	/**
	 * Widget slug prefix.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	protected static string $slug_prefix = 'tec_events_elementor_widget_';

	/**
	 * Widget asset prefix.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	protected static string $asset_prefix = 'tec-events-elementor-widget-';

	/**
	 * Widget template prefix.
	 *
	 * This holds the base path to the widget templates.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	protected static string $template_prefix = 'events/integrations/elementor/widgets';

	/**
	 * Template engine class.
	 *
	 * @since 6.4.0
	 *
	 * @var string The template engine class to use.
	 */
	protected string $template_engine_class = Template_Engine::class;

	/**
	 * Get local widget slug.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public static function get_slug(): string {
		return static::$slug;
	}

	/**
	 * Get the post type associated with the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public static function get_widget_post_type(): string {
		return TEC::POSTTYPE;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_template_file(): string {
		$file = str_replace( '_', '-', self::get_slug() );

		return "widgets/{$file}";
	}

	/**
	 * Gets the title of the widget.
	 *
	 * @since 6.4.0
	 */
	public function get_title(): string {
		$title = $this->title();
		$slug  = static::get_slug();

		/**
		 * Filters the title of the widget.
		 *
		 * @since 6.4.0
		 *
		 * @param string                $title The widget title.
		 * @param Abstract_Event_Widget $this  The widget instance.
		 */
		$title = apply_filters( 'tec_events_elementor_event_widget_title', $title, $this );

		/**
		 * Filters the title of a specific tec-events-elementor widget, by slug.
		 *
		 * @since 6.4.0
		 *
		 * @param string                $title The widget title.
		 * @param Abstract_Event_Widget $this  The widget instance.
		 */
		return (string) apply_filters( "tec_events_elementor_event_{$slug}_widget_title", $title, $this );
	}

	/**
	 * Gets/creates the title of the widget.
	 * This must be overridden by the child class to include translating the title string.
	 *
	 * @since 6.4.0
	 */
	abstract protected function title(): string;

	/**
	 * Gets the icon class for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return $this->get_icon_class();
	}

	/**
	 * Gets the CSS class list for the widget.
	 * As a string (for use in attributes) or as an array.
	 *
	 * @since 6.4.0
	 *
	 * @param string $format The format to return. Either 'attribute' (default) or 'array'.
	 *
	 * @return string|array<string>
	 */
	public function get_element_classes( string $format = 'attribute' ) {
		// If the property is empty, generate and use the widget class.
		$classes = $this->get_widget_class();
		$slug    = static::get_slug();

		/**
		 * Filters the widget class list for all tec-events-elementor widgets.
		 *
		 * @since 6.4.0
		 *
		 * @param array<string>         $classes The widget classes.
		 * @param string                $format  The format to return. Either 'attribute' (default - returns a string) or 'array'.
		 * @param Abstract_Event_Widget $this    The widget instance.
		 *
		 * @return array<string>
		 */
		$classes = apply_filters( 'tec_events_elementor_event_widget_element_classes', (array) $classes, $format, $this );

		/**
		 * Filters the widget class list for a specific tec-events-elementor widget, by slug.
		 *
		 * @since 6.4.0
		 *
		 * @param array<string>         $classes The widget classes.
		 * @param string                $format  The format to return. Either 'attribute' (default - returns a string) or 'array'.
		 * @param Abstract_Event_Widget $this    The widget instance.
		 *
		 * @return array<string>
		 */
		$classes = apply_filters( "tec_events_elementor_event_{$slug}_widget_element_classes", (array) $classes, $format, $this );

		// If we want a string, this is where we convert.
		if ( 'attribute' === $format ) {
			return implode( ' ', (array) $classes );
		}

		return $classes;
	}

	/**
	 * Provides a "trimmed" slug for usage in classes and such (removes the "event_" prefix)
	 * and converts all underscores to dashes.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public static function trim_slug(): string {
		return str_replace( [ 'event_', '_' ], [ '', '-' ], static::get_slug() );
	}

	/**
	 * Provides the main CSS class for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_widget_class(): string {
		$slug  = static::get_slug();
		$class = 'tec-events-elementor-event-widget__' . $this::trim_slug();

		/**
		 * Filters the widget class for all tec-events-elementor widgets.
		 *
		 * @since 6.4.0
		 *
		 * @param string                $class The widget class.
		 * @param Abstract_Event_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		$class = apply_filters( 'tec_events_elementor_event_widget_class', $class, $this );

		/**
		 * Filters the widget class for a specific tec-events-elementor widget, by slug.
		 *
		 * @since 6.4.0
		 *
		 * @param string                $class The widget class.
		 * @param Abstract_Event_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return apply_filters( "tec_events_elementor_event_{$slug}_widget_class", $class, $this );
	}

	/**
	 * Provides the CSS class for the widget icon.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_icon_class(): string {
		$slug  = static::get_slug();
		$class = 'tec-events-elementor-event-widget__icon-' . $this::trim_slug();

		/**
		 * Filters the widget icon class for all tec-events-elementor widgets.
		 *
		 * @since 6.4.0
		 *
		 * @param string                $class The widget class.
		 * @param Abstract_Event_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		$class = apply_filters( 'tec_events_elementor_event_widget_icon_class', $class, $this );

		/**
		 * Filters the widget icon class for a specific tec-events-elementor widget, by slug.
		 *
		 * @since 6.4.0
		 *
		 * @param string                $class The widget class.
		 * @param Abstract_Event_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		return (string) apply_filters( "tec_events_elementor_event_{$slug}_widget_icon_class", $class, $this );
	}

	/**
	 * Gets the categories of the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return array<string>
	 */
	public function get_categories(): array {
		return $this->categories;
	}

	/**
	 * An internal, filterable function to get the ID of the event/post the widget is used in.
	 *
	 * @since 6.4.0
	 *
	 * @return ?int The ID of the current item (parent post) the widget is in. Null if not found.
	 */
	protected function event_id(): ?int {
		$event_id = (int) get_the_ID();
		$slug     = self::get_slug();

		// Initially check if the global post is an event.
		if (
			is_admin() &&
			get_post_type( $event_id ) !== TEC::POSTTYPE &&
			'elementor' === tribe_get_request_var( 'action' )
		) {
			$event_id = (int) tribe_get_request_var( 'post', false );
		}

		/**
		 * Filters the event/post ID of the event/post the widget is used in.
		 *
		 * @since 6.4.0
		 *
		 * @param int                   $event_id The event ID.
		 * @param Abstract_Event_Widget $this     The widget instance.
		 */
		$event_id = (int) apply_filters( 'tec_events_elementor_widget_event_id', (int) $event_id, $this );

		/**
		 * Filters the event/post ID of the event/post the widget is used in.
		 *
		 * @since 6.4.0
		 *
		 * @param int                   $event_id The event ID.
		 * @param Abstract_Event_Widget $this     The widget instance.
		 */
		$event_id = (int) apply_filters( "tec_events_elementor_widget_{$slug}_event_id", (int) $event_id, $this );

		if ( get_post_type( $event_id ) !== TEC::POSTTYPE ) {
			error_log( 'post-type-check failed. event_id: ' . get_post_type( $event_id ) );
			return null;
		}

		return $event_id > 0 ? $event_id : null;
	}

	/**
	 * Get the event ID.
	 *
	 * @since 6.4.0
	 *
	 * @return ?int
	 */
	public function get_event_id() {
		return $this->event_id();
	}

	/**
	 * Get the event ID.
	 *
	 * @since 6.4.0
	 *
	 * @return ?int
	 */
	public function get_post_id() {
		return $this->get_event_id();
	}

	/**
	 * Get the event object.
	 *
	 * @since 6.4.0
	 */
	public function get_event(): ?WP_Post {
		return tribe_get_event( $this->get_event_id() );
	}

	/**
	 * Get the post object for the widget.
	 *
	 * @since TBD
	 *
	 * @return WP_Post|null
	 */
	public function get_post(): ?WP_Post {
		return $this->get_event();
	}

	/**
	 * Determines if the widget has a valid event ID associated with it.
	 *
	 * @since 6.4.0
	 *
	 * @return bool
	 */
	protected function has_event_id(): bool {
		return $this->get_event_id() !== null;
	}

	/**
	 * Determines if the widget has a valid event associated with it.
	 *
	 * @since 6.4.0
	 *
	 * @return bool
	 */
	protected function has_event(): bool {
		return $this->get_event() !== null;
	}

	/**
	 * Get the asset source for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	protected function get_asset_source() {
		return tribe( 'tec.main' );
	}

	/**
	 * Register the styles for the widget.
	 *
	 * @since 6.4.0
	 */
	public function register_assets(): void {
		static::$group_key = Assets_Manager::$group_key;

		parent::register_assets();
	}

	/**
	 * Get the message to show when the widget is empty.
	 *
	 * @since 6.4.0
	 *
	 * @return string The message shown when an event widget is empty.
	 */
	public function get_empty_message(): string {
		return esc_html_x(
			"This widget is empty and won't display on the front end unless you add some content in the WordPress editor.",
			'The default message shown when an event widget is empty.',
			'the-events-calendar'
		);
	}

	/**
	 * Wether to show the empty widget template in the editor.
	 *
	 * @since 6.4.0
	 */
	public function show_empty(): bool {
		if ( ! $this->get_template()->is_edit_mode() ) {
			return false;
		}

		return $this->empty_conditions();
	}

	/**
	 * Conditions for showing the empty widget template in the editor.
	 * Meant to be overridden in the widget class.
	 * This must return true for the empty widget template to show.
	 *
	 * @since 6.4.0
	 */
	protected function empty_conditions(): bool {
		return false;
	}
}
