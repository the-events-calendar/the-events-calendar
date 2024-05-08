<?php
/**
 * List View Elementor Widget.
 *
 * @since 6.4.0
 *
 * @package Tribe\Events\Integrations\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts;

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Template_Engine;
use TEC\Events\Integrations\Plugins\Elementor\Assets_Manager;
use Tribe__Events__Main as TEC;

use Elementor\Widget_Base;
use WP_Post;

/**
 * Abstract Widget class
 *
 * All template widgets should extend this class.
 */
abstract class Abstract_Widget extends Widget_Base {

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
	 * Widget slug.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	protected static string $slug;

	/**
	 * Whether the widget has styles to register/enqueue.
	 *
	 * @since 6.4.0
	 *
	 * @var bool
	 */
	protected static bool $has_styles = false;

	/**
	 * Widget categories.
	 *
	 * @since 6.4.0
	 *
	 * @var array<string>
	 */
	protected array $categories = [ 'the-events-calendar' ];

	/**
	 * Widget template engine.
	 *
	 * @since 6.4.0
	 *
	 * @var Template_Engine
	 */
	protected Template_Engine $template;

	/**
	 * Template engine class.
	 *
	 * @since 6.4.0
	 *
	 * @var string The template engine class to use.
	 */
	protected string $template_engine_class = Template_Engine::class;

	/**
	 * The hooks added by the widget.
	 *
	 * @since 6.4.0
	 *
	 * @var array<string,array>
	 */
	protected array $added_hooks = [];

	/**
	 * Get elementor widget slug.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public static function get_elementor_slug(): string {
		return static::$slug_prefix . static::get_slug();
	}

	/**
	 * Gets the name (aka slug) of the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_name(): string {
		return static::get_elementor_slug();
	}

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
	 * Get the template file path, which will be used to include the correct widget template to be rendered.
	 * By default, it will be the combination of a folder named 'widgets' and the widget slug with _ replaced by -.
	 * For example:
	 * - if the widget slug is 'event_cost'
	 * - template file path will be 'widgets/event-cost'.
	 *
	 * This method can be overridden by the child class to provide a custom template file path.
	 *
	 * @since 6.4.0
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
		 * @param string          $title The widget title.
		 * @param Abstract_Widget $this  The widget instance.
		 */
		$title = apply_filters( 'tec_events_elementor_event_widget_title', $title, $this );

		/**
		 * Filters the title of a specific tec-events-elementor widget, by slug.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $title The widget title.
		 * @param Abstract_Widget $this  The widget instance.
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
		 * @param array<string>   $classes The widget classes.
		 * @param string          $format  The format to return. Either 'attribute' (default - returns a string) or 'array'.
		 * @param Abstract_Widget $this    The widget instance.
		 *
		 * @return array<string>
		 */
		$classes = apply_filters( 'tec_events_elementor_event_widget_element_classes', (array) $classes, $format, $this );

		/**
		 * Filters the widget class list for a specific tec-events-elementor widget, by slug.
		 *
		 * @since 6.4.0
		 *
		 * @param array<string>   $classes The widget classes.
		 * @param string          $format  The format to return. Either 'attribute' (default - returns a string) or 'array'.
		 * @param Abstract_Widget $this    The widget instance.
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
		 * @param string          $class The widget class.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		$class = apply_filters( 'tec_events_elementor_event_widget_class', $class, $this );

		/**
		 * Filters the widget class for a specific tec-events-elementor widget, by slug.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The widget class.
		 * @param Abstract_Widget $this  The widget instance.
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
		 * @param string          $class The widget class.
		 * @param Abstract_Widget $this  The widget instance.
		 *
		 * @return string
		 */
		$class = apply_filters( 'tec_events_elementor_event_widget_icon_class', $class, $this );

		/**
		 * Filters the widget icon class for a specific tec-events-elementor widget, by slug.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The widget class.
		 * @param Abstract_Widget $this  The widget instance.
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
		 * @param int             $event_id The event ID.
		 * @param Abstract_Widget $this     The widget instance.
		 */
		$event_id = (int) apply_filters( 'tec_events_elementor_widget_event_id', (int) $event_id, $this );

		/**
		 * Filters the event/post ID of the event/post the widget is used in.
		 *
		 * @since 6.4.0
		 *
		 * @param int             $event_id The event ID.
		 * @param Abstract_Widget $this     The widget instance.
		 */
		$event_id = (int) apply_filters( "tec_events_elementor_widget_{$slug}_event_id", (int) $event_id, $this );

		if ( get_post_type( $event_id ) !== TEC::POSTTYPE ) {
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
	public function get_event_id(): ?int {
		return $this->event_id();
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
	 * Get the event associated with this widget.
	 *
	 * @since 6.4.0
	 *
	 * @return ?WP_Post
	 */
	public function get_event(): ?WP_Post {
		return tribe_get_event( $this->get_event_id() );
	}

	/**
	 * Get the template engine class.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	protected function get_template_engine_class(): string {
		// Ensures that the class returned is a subclass of Template_Engine.
		if ( ! is_subclass_of( $this->template_engine_class, Template_Engine::class ) ) {
			return Template_Engine::class;
		}

		return $this->template_engine_class;
	}

	/**
	 * Get template object.
	 *
	 * @since 6.4.0
	 *
	 * @return Template_Engine
	 */
	public function get_template(): Template_Engine {
		if ( empty( $this->template ) ) {
			/**
			 * @var Template_Engine $template_engine_class
			 */
			$template_engine_class = $this->get_template_engine_class();
			$this->template        = $template_engine_class::with_widget( $this );

			// Event ID is optional.
			$this->template->set_event( $this->get_event_id() );
		}

		return $this->template;
	}

	/**
	 * Set up a self-removing filter for a widget template, it should hook itself on the before and after include hooks
	 * of the template engine.
	 *
	 * @since 6.4.0
	 *
	 * @param string    $on            The hook to add on.
	 * @param ?callable $callback      The callback to add to the filter.
	 * @param int       $priority      The priority of the filter.
	 * @param int       $accepted_args The number of arguments the filter accepts.
	 */
	protected function set_template_filter( string $on, ?callable $callback = null, int $priority = 10, int $accepted_args = 1 ): void {
		$template_file = $this->get_template_file();
		$hook_name     = "events/integrations/elementor/{$template_file}";

		$add    = "tribe_template_before_include:{$hook_name}";
		$remove = "tribe_template_after_include:{$hook_name}";

		// ensure the callback is callable.
		if ( ! is_callable( $callback ) ) {
			return;
		}

		$add_callback = static function () use ( $on, $callback, $priority, $accepted_args ) {
			add_filter( $on, $callback, $priority, $accepted_args );
		};

		$remove_callback = static function () use ( $on, $callback, $priority ) {
			remove_filter( $on, $callback, $priority );
		};

		// Include the hook.
		add_action( $add, $add_callback );
		$this->added_hooks[] = [
			'hook'     => $add,
			'callback' => $add_callback,
		];

		// Remove the hook.
		add_action( $remove, $remove_callback );
		$this->added_hooks[] = [
			'hook'     => $remove,
			'callback' => $remove_callback,
		];
	}

	/**
	 * Unset the template filters.
	 *
	 * @since 6.4.0
	 */
	protected function unset_template_filters(): void {
		foreach ( $this->added_hooks as $hook ) {
			remove_action( $hook['hook'], $hook['callback'] );
		}
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return array The template args.
	 */
	abstract protected function template_args(): array;

	/**
	 * Determine if the widget should show mock data.
	 *
	 * @since 6.4.0
	 *
	 * @return bool
	 */
	public function should_show_mock_data(): bool {
		return false;
	}

	/**
	 * Get the template arguments.
	 *
	 * This calls the template_args method on the widget and then filters the data.
	 *
	 * @since 6.4.0
	 *
	 * @return array
	 */
	public function get_template_args(): array {
		$args = $this->template_args(); // Defined in each widget instance.
		$slug = self::get_slug();


		/**
		 * Filters the template data for all Elementor widget templates.
		 *
		 * @param array<string,mixed> $args   The template data.
		 * @param bool                $preview Whether the template is in preview mode.
		 * @param object              $widget The widget object.
		 *
		 * @return array
		 */
		$args = (array) apply_filters( 'tec_events_elementor_widget_template_data', $args, false, $this );

		/**
		 * Filters the template data for a specific (by $slug) Elementor widget templates.
		 *
		 * @param array<string,mixed> $args   The template data.
		 * @param bool                $preview Whether the template is in preview mode.
		 * @param object              $widget The widget object.
		 *
		 * @return array
		 */
		$args = (array) apply_filters( "tec_events_elementor_widget_{$slug}_template_data", $args, false, $this );

		// Add the widget to the data array.
		$args['widget'] = $this;

		return $args;
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
	public function register_style(): void {
		if ( ! static::$has_styles ) {
			return;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		$slug   = $this::trim_slug();
		$source = $this->get_asset_source();

		// Register the styles for the widget.
		tribe_asset(
			tribe( $source ),
			static::$asset_prefix . $slug . '-styles',
			'integrations/plugins/elementor/widgets/' . $slug . '.css',
			[],
			null,
			[ 'groups' => [ Assets_Manager::$group_key ] ]
		);
	}

	/**
	 * Enqueue the styles for the widget.
	 *
	 * @since 6.4.0
	 */
	public function enqueue_style(): void {
		if ( ! static::$has_styles ) {
			return;
		}

		$slug = $this::trim_slug();

		tribe_asset_enqueue( static::$asset_prefix . $slug . '-styles' );

		/**
		 * Fires after the styles for the Elementor widget have been enqueued.
		 *
		 * @since 6.4.0
		 *
		 * @param Abstract_Widget $this The widget instance.
		 */
		do_action( 'tec_events_elementor_widget_enqueue_style', $this );

		/**
		 * Fires after the styles for a specific Elementor widget have been enqueued.
		 *
		 * @since 6.4.0
		 *
		 * @param Abstract_Widget $this The widget instance.
		 */
		do_action( "tec_events_elementor_widget_{$slug}_enqueue_style", $this );
	}

	/**
	 * Get the output of the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public function get_output(): string {
		$template = $this->show_empty() ? 'widgets/empty' : 'widgets/base';

		$output = $this->get_template()->template( $template, $this->get_template_args(), false );

		$this->unset_template_filters();

		return $output;
	}

	/**
	 * Render the Elementor widget, this method needs to be protected as it is originally defined as such in elementor.
	 *
	 * @since 6.4.0
	 */
	protected function render(): void {
		echo $this->get_output(); // phpcs:ignore StellarWP.XSS.EscapeOutput.OutputNotEscaped,WordPress.Security.EscapeOutput.OutputNotEscaped
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
