<?php
/**
 * Widget Abstract
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\View_Interface;
use Tribe__Context as Context;
use Tribe__Utils__Array as Arr;

/**
 * The abstract all widgets should implement.
 *
 * @since   5.2.1
 *
 * @package Tribe\Widget
 */
abstract class Widget_Abstract extends \Tribe\Widget\Widget_Abstract {

	/**
	 * The view interface for the widget.
	 *
	 * @since 5.2.1
	 *
	 * @var View_Interface;
	 */
	protected $view;

	/**
	 * The slug of the widget view.
	 *
	 * @since 5.2.1
	 *
	 * @var string
	 */
	protected $view_slug;

	/**
	 * Widget css group slug.
	 *
	 * @since 5.5.0
	 *
	 * @var string
	 */
	protected static $widget_css_group;

	/**
	 * {@inheritDoc}
	 */
	public function setup( $args = [], $instance = [] ) {
		// Add the admin template class for the widget admin form.
		$this->set_admin_template( tribe( Admin_Template::class ) );

		// Saves the instance values to the arguments
		$this->setup_arguments( $instance );

		$this->setup_sidebar_arguments( $args );

		// Setup the View for the frontend.
		$this->setup_view( null );
	}

	/**
	 * Setup the view for the widget.
	 *
	 * @since 5.2.1
	 * @since 5.3.0 Correct asset enqueue method.
	 * @since 5.5.0 Deprecated $arguments param since it should come from the instance.
	 *
	 * @param array<string,mixed> $_deprecated The widget arguments, as set by the user in the widget string.
	 */
	public function setup_view( $_deprecated ) {
		$context = tribe_context();

		// Modifies the Context for the widget params.
		$context = $this->alter_context( $context, $this->get_arguments() );

		// Setup the view instance.
		$view = View::make( $this->get_view_slug(), $context );

		$view->get_template()->set_values( $this->get_arguments(), false );

		$this->set_view( $view );

		// Ensure widgets never get Filter Bar classes on their containers.
		add_filter( "tribe_events_views_v2_filter_bar_{$this->view_slug}_view_html_classes", '__return_false' );
	}

	/**
	 * Get local widget css group slug.
	 *
	 * @since 5.5.0
	 *
	 * @return string
	 */
	public static function get_css_group() {
		return static::$widget_css_group;
	}

	/**
	 * Returns the rendered View HTML code.
	 *
	 * @since 5.2.1
	 *
	 * @return string
	 */
	public function get_html() {
		return $this->get_view()->get_html();
	}

	/**
	 * Sets the template view.
	 *
	 * @since 5.2.1
	 *
	 * @param View_Interface $view Which view we are using this template on.
	 */
	public function set_view( View_Interface $view ) {
		$this->view = $view;
	}

	/**
	 * Returns the current template view, either set in the constructor or using the `set_view` method.
	 *
	 * @since 5.2.1
	 *
	 * @return View_Interface The current template view.
	 */
	public function get_view() {
		return $this->view;
	}

	/**
	 * Returns the widget view slug.
	 *
	 * @since 5.2.1
	 *
	 * @return string The widget view slug.
	 */
	public function get_view_slug() {
		return $this->view_slug;
	}

	/**
	 * Alters the widget context with its arguments.
	 *
	 * @since  5.2.1
	 *
	 * @param \Tribe__Context     $context   Context we will use to build the view.
	 * @param array<string,mixed> $arguments Current set of arguments.
	 *
	 * @return \Tribe__Context Context after widget changes.
	 */
	public function alter_context( Context $context, array $arguments = [] ) {
		$alter_context = $this->args_to_context( $arguments, $context );

		$context = $context->alter( $alter_context );

		return $context;
	}

	/**
	 * Translates widget arguments to their Context argument counterpart.
	 *
	 * For front-end display.
	 *
	 * @since 5.2.1
	 *
	 * @param array<string,mixed> $arguments Current set of arguments.
	 * @param Context             $context   The request context.
	 *
	 * @return array<string,mixed> The translated widget arguments.
	 */
	protected function args_to_context( array $arguments, Context $context ) {
		$context_args = [
			'widget'       => true,
			'widget_title' => Arr::get( $arguments, 'title' ),
		];

		return $context_args;
	}

	/**
	 * Massages the data before asking tribe_format_field_dependency() to create the dependency attributes.
	 *
	 * @since 5.3.0
	 *
	 * @param array <string,mixed> $field The field info.
	 *
	 * @return string The dependency attributes.
	 */
	public function format_dependency( $field ) {
		$deps = Arr::get( $field, 'dependency', false );
		// Sanity check.
		if ( empty( $deps ) ) {
			return '';
		}

		if ( isset( $deps['ID'] ) ) {
			$deps['id'] = $deps['ID'];
		}

		// No ID to hook to? Bail.
		if ( empty( $deps['id'] ) ) {
			return '';
		}

		$deps['id'] = $this->get_field_id( $deps['id'] );

		return tribe_format_field_dependency( $deps );
	}

	/**********************
	 * Deprecated Methods *
	 **********************/

	/**
	 * Filters the template vars for widget-specific items.
	 *
	 * @since 5.3.0
	 * @deprecated 5.5.0 Removed due to using template vars properly, see Tribe\Events\Views\V2\Views\Widgets\Widget_View::setup_template_vars().
	 *
	 * @param array<string,mixed> $template_vars The current template variables.
	 * @param View                $view          Which view we are dealing with.
	 *
	 * @return array<string,mixed> The modified template variables.
	 */
	public function filter_widget_template_vars( $template_vars, $view ) {
		if ( $view->get_slug() !== $this->view_slug ) {
			return $template_vars;
		}

		return $this->disable_json_data( $template_vars );
	}

	/**
	 * Empties the json_ld_data if jsonld_enable is false,
	 * removing the need for additional checks in the template.
	 *
	 * @since 5.3.0
	 * @deprecated 5.5.0 Removed due to using template vars properly, see Tribe\Events\Views\V2\Views\Widgets\Widget_View::setup_template_vars().
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
	 * Encapsulates and handles the logic for asset enqueues in it's own method.
	 *
	 * @since 5.3.0
	 *
	 * @deprecated 5.5.0 Removed to make use of just should enqueue setup in asset manager.
	 *
	 * @param mixed $_deprecated  (deprecated) Previously held context we are using to build the view.
	 * @param mixed $__deprecated (deprecated) Previously held which view we are using the template on.
	 */
	public function filter_enqueue_assets( $_deprecated, $__deprecated ) {
		/**
		 * We removed 4 actions from here:
		 * - 'tribe_events_views_v2_widget_before_enqueue_assets'
		 * - "tribe_events_views_v2_widget_{$this->view_slug}_before_enqueue_assets"
		 *
		 * - 'tribe_events_views_v2_widget_after_enqueue_assets'
		 * - "tribe_events_views_v2_widget_{$this->view_slug}_after_enqueue_assets"
		 *
		 * If you were making use of those refer to to the filters related on the asset registration.
		 */
	}

	/**
	 * Enqueues the assets for widgets.
	 *
	 * @since 5.3.0
	 *
	 * @deprecated 5.5.0 Removed to make use of just should enqueue setup in asset manager.
	 *
	 * @param mixed $_deprecated  (deprecated) Previously held context we are using to build the view.
	 * @param mixed $__deprecated (deprecated) Previously held which view we are using the template on.
	 */
	public function enqueue_assets( $_deprecated, $__deprecated ) {

	}

	/**
	 * Determines whether to enqueue assets for widgets.
	 *
	 * @since 5.3.0
	 *
	 * @deprecated 5.5.0 Removed to make use of just should enqueue setup in asset manager.
	 *
	 * @param mixed $_deprecated  (deprecated) Previously held context we are using to build the view.
	 * @param mixed $__deprecated (deprecated) Previously held which view we are using the template on.
	 *
	 * @return bool Whether assets are enqueued or not.
	 */
	public function should_enqueue_assets( $_deprecated, $__deprecated ) {
		/**
		 * We removed two filters from here:
		 * - 'tribe_events_views_v2_widget_enqueue_assets'
		 * - "tribe_events_views_v2_widget_{$this->view_slug}_enqueue_assets"
		 *
		 * If you were making use of those refer to to the filters related on the asset registration.
		 */

		return static::is_widget_in_use();
	}

	/**
	 * Returns the widget slug.
	 *
	 * @since 5.3.0
	 * @deprecated 5.5.0 replaced by the static::get_widget_slug().
	 *
	 * @return string The widget slug.
	 */
	public function get_slug() {
		return static::get_widget_slug();
	}
}
