<?php
/**
 * The Base Front End Widget View.
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 * @since 5.3.0
 */

namespace Tribe\Events\Views\V2\Views\Widgets;

use Tribe\Widget\Widget_Abstract;
use Tribe__Context as Context;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Theme_Compatibility;
use Tribe\Events\Views\V2\Assets as TEC_Assets;

/**
 * Class Widget_View
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 */
class Widget_View extends View {

	/**
	 * The slug for the template path.
	 *
	 * @since 5.2.1
	 *
	 * @var string
	 */
	protected $template_path = 'widgets';

	/**
	 * Visibility for this view.
	 *
	 * @since 5.2.1
	 *
	 * @var bool
	 */
	protected static $publicly_visible = false;

	/**
	 * Whether the View should display the events bar or not.
	 *
	 * @since 5.2.1
	 *
	 * @var bool
	 */
	protected $display_events_bar = false;

	/**
	 * Returns the widget "view more" text.
	 *
	 * @since 5.4.0
	 *
	 * @return string The widget "view more" text.
	 */
	public function get_view_more_text() {
		return esc_html__( 'View Calendar', 'the-events-calendar');
	}

	/**
	 * Returns the widget "view more" title.
	 * Adds context as needed for screen readers.
	 * @see \Tribe\Events\Pro\Views\V2\Views\Widgets\Venue_View for an example.
	 *
	 * @since 5.4.0
	 *
	 * @return string The widget "view more" title.
	 */
	public function get_view_more_title() {
		return esc_html(
			sprintf(
				/* Translators: 1: plural lowercase event term */
				__( 'View more %1$s.', 'the-events-calendar' ),
				tribe_get_event_label_plural_lowercase()
			)
		);
	}

	/**
	 * Returns the widget "view more" url.
	 *
	 * @since 5.4.0
	 *
	 * @return string The widget "view more" url.
	 */
	public function get_view_more_link() {
		return tribe_events_get_url();
	}

	/**
	 * Overrides the base View method.
	 *
	 * @since 5.3.0
	 *
	 * @return array<string,mixed> The Widget View template vars, modified if required.
	 */
	protected function setup_template_vars() {
		$template_vars = parent::setup_template_vars();

		$is_jsonld_enabled              = tribe_is_truthy( $this->context->get( 'jsonld_enable' ) );
		$template_vars['jsonld_enable'] = $is_jsonld_enabled;

		// If JSON-LD not enabled, just empty data.
		if ( ! $is_jsonld_enabled ) {
			$template_vars['json_ld_data'] = '';
		}

		$template_vars['compatibility_classes'] = $this->get_compatibility_classes();
		$template_vars['container_classes']     = $this->get_html_classes();
		$template_vars['view_more_text']        = $this->get_view_more_text();
		$template_vars['view_more_title']       = $this->get_view_more_title();
		$template_vars['view_more_link']        = $this->get_view_more_link();

		return $template_vars;
	}

	/**
	 * Sets up the View repository arguments from the View context or a provided Context object.
	 *
	 * @since 4.9.3
	 *
	 * @param  Context|null $context A context to use to setup the args, or `null` to use the View Context.
	 *
	 * @return array<string,mixed> The arguments, ready to be set on the View repository instance.
	 */
	protected function setup_repository_args( Context $context = null ) {
		$context     = null !== $context ? $context : $this->context;
		$args        = parent::setup_repository_args( $context );

		/**
		 * A widget-specific filter for repository args, based on widget slug.
		 * Allows other plugins to add/remove args for the repository pre-query.
		 *
		 * @since 5.2.0
		 * @since 5.4.0 Include the $widget param.
		 *
		 * @param array<string,mixed>  $args    The arguments, ready to be set on the View repository instance.
		 * @param Context              $context The context to use to setup the args.
		 * @param Widget_View          $widget  Instance of the Widget View we are filtering for.
		 */
		$args = apply_filters( "tribe_events_views_v2_widget_repository_args", $args, $context, $this );

		/**
		 * A widget-specific filter for repository args, based on widget slug.
		 * Allows other plugins to add/remove args for the repository pre-query.
		 *
		 * @since 5.2.0
		 * @since 5.4.0 Include the $widget param.
		 *
		 * @param array<string,mixed>  $args    The arguments, ready to be set on the View repository instance.
		 * @param Context              $context The context to use to setup the args.
		 * @param Widget_View          $widget  Instance of the Widget View we are filtering for.
		 */
		$args = apply_filters( "tribe_events_views_v2_{$this->get_slug()}_widget_repository_args", $args, $context, $this );

		return $args;
	}

	/**
	 * Adds compatibility classes to the widget view container.
	 * Not the view itself - the wrapping div around that
	 *
	 * @since 5.4.0
	 *
	 * @return array<string> An Array of class names to add to the container. Will contain
	 *                       _at least_ 'tribe-compatibility-container' as an indicator.
	 */
	public function get_compatibility_classes() {
		$classes = Theme_Compatibility::get_container_classes();

		/**
		 * Filters the HTML classes applied to a widget top-level container.
		 *
		 * @since 5.3.0
		 *
		 * @param array  $html_classes Array of classes used for this widget.
		 * @param string $view_slug    The current widget slug.
		 * @param View   $instance     The current View object.
		 */
		$classes = apply_filters( 'tribe_events_views_v2_widget_compatibility_classes', $classes, $this->get_slug(), $this );

		/**
		 * Filters the HTML classes applied to a specific widget top-level container.
		 *
		 * @since 5.3.0
		 *
		 * @param array $classes Array of classes used for this widget.
		 * @param View  $instance     The current View object.
		 */
		$classes = apply_filters( "tribe_events_views_v2_{$this->get_slug()}_widget_compatibility_classes", $classes, $this );

		return $classes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html_classes( array $classes = [] ) {
		$html_classes = parent::get_html_classes( [ 'tribe-events-widget' ] );

		/**
		 * Filters the HTML classes applied to a widget top-level container.
		 *
		 * @since 5.3.0
		 *
		 * @param array  $html_classes Array of classes used for this widget.
		 * @param string $view_slug    The current widget slug.
		 * @param View   $instance     The current View object.
		 */
		$html_classes = apply_filters( 'tribe_events_views_v2_widget_html_classes', $html_classes, $this->get_slug(), $this );

		/**
		 * Filters the HTML classes applied to a specific widget top-level container.
		 *
		 * @since 5.3.0
		 *
		 * @param array $html_classes Array of classes used for this widget.
		 * @param View  $instance     The current View object.
		 */
		$html_classes = apply_filters( "tribe_events_views_v2_{$this->get_slug()}_widget_html_classes", $html_classes, $this );

		return $html_classes;
	}

	/**
	 * Modify the setup the loop method to only set the repository arguments.
	 *
	 * @since 5.3.0
	 *
	 * @param array|null $args An array of associative arguments used to setup the repository for the View.
	 */
	public function setup_the_loop( array $args = [] ) {
		$args = wp_parse_args( $args, $this->repository_args );

		$this->repository->by_args( $args );

		tribe_asset_enqueue_group( TEC_Assets::$widget_group_key );
	}
}
