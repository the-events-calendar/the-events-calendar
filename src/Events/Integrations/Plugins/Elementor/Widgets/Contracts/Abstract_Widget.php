<?php
/**
 * List View Elementor Widget.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Integrations\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts;

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Template_Engine;
use TEC\Events\Integrations\Plugins\Elementor\Assets_Manager;
use Tribe__Events__Main as TEC;

use Elementor\Widget_Base;

/**
 * Abstract Widget class
 *
 * All template widgets should extend this class.
 */
abstract class Abstract_Widget extends Widget_Base {

	/**
	 * Widget slug prefix.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug_prefix = 'tec_events_elementor_widget_';


	/**
	 * Widget asset prefix.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $asset_prefix = 'tec-events-elementor-widget-';

	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug;

	/**
	 * Whether the widget has styles to register/enqueue.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected static bool $has_styles = false;

	/**
	 * Widget categories.
	 *
	 * @since TBD
	 *
	 * @var array<string>
	 */
	protected array $categories = [ 'the-events-calendar' ];

	/**
	 * Widget template engine.
	 *
	 * @since TBD
	 *
	 * @var Template_Engine
	 */
	protected Template_Engine $template;

	/**
	 * Template engine class.
	 *
	 * @since TBD
	 *
	 * @var string The template engine class to use.
	 */
	protected string $template_engine_class = Template_Engine::class;

	/**
	 * Get elementor widget slug.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public static function get_elementor_slug(): string {
		return static::$slug_prefix . static::get_slug();
	}

	/**
	 * Gets the name (aka slug) of the widget.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_name(): string {
		return static::get_elementor_slug();
	}

	/**
	 * Get local widget slug.
	 *
	 * @since TBD
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
	 * @since TBD
	 */
	public function get_title(): string {
		$title = $this->title();
		$slug  = static::get_slug();

		/**
		 * Filters the title of the widget.
		 *
		 * @since TBD
		 *
		 * @param string          $title The widget title.
		 * @param Abstract_Widget $this  The widget instance.
		 */
		$title = apply_filters( 'tec_events_elementor_event_widget_title', $title, $this );

		/**
		 * Filters the title of a specific tec-events-elementor widget, by slug.
		 *
		 * @since TBD
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
	 * @since TBD
	 */
	abstract protected function title(): string;

	/**
	 * Gets the icon class for the widget.
	 *
	 * @since TBD
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
	 * @since TBD
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
		 * @since TBD
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
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public static function trim_slug(): string {
		return str_replace( [ 'event_', '_' ], [ '', '-' ], static::get_slug() );
	}

	/**
	 * Provides the main CSS class for the widget.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_widget_class(): string {
		$slug  = static::get_slug();
		$class = 'tec-events-elementor-event-widget__' . $this::trim_slug();

		/**
		 * Filters the widget class for all tec-events-elementor widgets.
		 *
		 * @since TBD
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
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_icon_class(): string {
		$slug  = static::get_slug();
		$class = 'tec-events-elementor-event-widget__icon-' . $this::trim_slug();

		/**
		 * Filters the widget icon class for all tec-events-elementor widgets.
		 *
		 * @since TBD
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
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return array<string>
	 */
	public function get_categories(): array {
		return $this->categories;
	}

	/**
	 * An internal, filterable function to get the ID of the event/post the widget is used in.
	 *
	 * @since TBD
	 *
	 * @return int|false The ID of the current item (parent post) the widget is in. False if not found.
	 */
	protected function get_event_id(): ?int {
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
		 * @since TBD
		 *
		 * @param int             $event_id The event ID.
		 * @param Abstract_Widget $this     The widget instance.
		 */
		$event_id = (int) apply_filters( 'tec_events_elementor_widget_event_id', (int) $event_id, $this );

		/**
		 * Filters the event/post ID of the event/post the widget is used in.
		 *
		 * @since TBD
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
	 * Determines if the widget has a valid event ID associated with it.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	protected function has_event_id(): bool {
		return $this->get_event_id() !== null;
	}

	/**
	 * Get the template engine class.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
	 *
	 * @param string    $on            The hook to add on.
	 * @param ?callable $callback      The callback to add to the filter.
	 * @param int       $priority      The priority of the filter.
	 * @param int       $accepted_args The number of arguments the filter accepts.
	 */
	protected function set_template_filter( string $on, ?callable $callback = null, int $priority = 10, int $accepted_args = 1 ): void {
		// ensure the callback is callable.
		if ( ! is_callable( $callback ) ) {
			return;
		}

		$remove_callback = static function () use ( $on, $callback, $priority ) {
			remove_filter( $on, $callback, $priority );
		};

		$slug = $this::trim_slug();

		// Add filter now as we're about to get the template.
		add_filter( $on, $callback, $priority, $accepted_args );

		// Remove the later.
		add_action( "tec_events_elementor_widget_{$slug}_after_render", $remove_callback );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since TBD
	 *
	 * @return array The template args.
	 */
	abstract protected function template_args(): array;

	/**
	 * Determine if the widget should show mock data.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_asset_source() {
		$source = 'tec.main';

		/**
		 * Filters the asset source for the widget.
		 * Allows other plugins to change the source for their widget assets.
		 *
		 * @since TBD
		 *
		 * @param string          $source The asset source.
		 * @param Abstract_Widget $this   The widget instance.
		 *
		 * @return string
		 */
		return (string) apply_filters( 'tec_events_elementor_widget_asset_source', $source, $this );
	}

	/**
	 * Register the styles for the widget.
	 *
	 * @since TBD
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
	 * @since TBD
	 */
	public function enqueue_style(): void {
		if ( ! static::$has_styles ) {
			return;
		}

		$slug = $this::trim_slug();

		tribe_asset_enqueue( static::$asset_prefix . $slug . '-styles' );
	}

	/**
	 * Render the Elementor widget, this method needs to be protected as it is originally defined as such in elementor.
	 *
	 * @since TBD
	 */
	protected function render(): void {
		$slug = $this::trim_slug();
		$args = $this->get_template_args();

		do_action( 'tec_events_elementor_widget_before_render', $this );

		do_action( "tec_events_elementor_widget_{$slug}_before_render", $this );

		$this->get_template()->template( 'widgets/base', $args, true );

		do_action( 'tec_events_elementor_widget_after_render', $this );

		do_action( "tec_events_elementor_widget_{$slug}_after_render", $this );
	}
}
