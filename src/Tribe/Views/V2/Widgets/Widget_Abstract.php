<?php
/**
 * Widget Abstract
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

use Tribe\Events\Views\V2\Assets;
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
	 * {@inheritDoc}
	 */
	public function setup() {
		// Add the admin template class for the widget admin form.
		$this->set_admin_template( tribe( Admin_Template::class ) );
	}

	/**
	 * Setup the view for the widget.
	 *
	 * @since 5.2.1
	 * @since 5.3.0 Correct asset enqueue method.
	 *
	 * @param array<string,mixed> $arguments The widget arguments, as set by the user in the widget string.
	 */
	public function setup_view( $arguments ) {
		$context = tribe_context();

		// Modifies the Context for the widget params.
		$context = $this->alter_context( $context, $arguments );

		// Setup the view instance.
		$view = View::make( $this->get_view_slug(), $context );

		$view->get_template()->set_values( $this->setup_arguments(), false );

		$this->set_view( $view );

		$this->filter_enqueue_assets( $context, $view );

		// Ensure widgets never get Filter Bar classes on their containers.
		add_filter( "tribe_events_views_v2_filter_bar_{$this->view_slug}_view_html_classes", '__return_false' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_arguments( array $instance = [] ) {
		$arguments = parent::setup_arguments( $instance );

		$admin_fields = $arguments['admin_fields'];

		foreach ( $admin_fields as $field_name => $field ) {
			$arguments['admin_fields'][ $field_name ] = $this->get_admin_data( $arguments, $field_name, $field );
		}

		return $arguments;
	}

	/**
	 * Encapsulates and handles the logic for asset enqueues in it's own method.
	 *
	 * @since 5.3.0
	 *
	 * @param \Tribe__Context $context Context we are using to build the view.
	 * @param View_Interface  $view    Which view we are using the template on.
	 */
	public function filter_enqueue_assets( $context, $view ) {
		$should_enqueue = $this->should_enqueue_assets( $context, $view );

		/**
		 * Run an action before we start enqueuing widget assets.
		 *
		 * @since 5.3.0
		 *
		 * @param boolean         $should_enqueue Whether assets are enqueued or not.
		 * @param \Tribe__Context $context        Context we are using to build the view.
		 * @param View_Interface  $view           Which view we are using the template on.
		 */
		do_action(
			'tribe_events_views_v2_widget_before_enqueue_assets',
			$should_enqueue,
			$context,
			$view
		);

		/**
		 * Run an action for a specific widget before we start enqueuing widget assets.
		 *
		 * @since 5.3.0
		 *
		 * @param boolean         $should_enqueue Whether assets are enqueued or not.
		 * @param \Tribe__Context $context        Context we are using to build the view.
		 * @param View_Interface  $view           Which view we are using the template on.
		 */
		do_action(
			"tribe_events_views_v2_widget_{$this->view_slug}_before_enqueue_assets",
			$should_enqueue,
			$context,
			$view
		);

		if ( $should_enqueue ) {
			$this->enqueue_assets( $context, $view );
		}

		/**
		 * Run an action after we start enqueuing widget assets.
		 *
		 * @since 5.3.0
		 *
		 * @param boolean         $should_enqueue Whether assets are enqueued or not.
		 * @param \Tribe__Context $context        Context we are using to build the view.
		 * @param View_Interface  $view           Which view we are using the template on.
		 */
		do_action(
			'tribe_events_views_v2_widget_after_enqueue_assets',
			$should_enqueue,
			$context,
			$view
		);

		/**
		 * Run an action for a specific widget after we start enqueuing widget assets.
		 *
		 * @since 5.3.0
		 *
		 * @param boolean         $should_enqueue Whether assets are enqueued or not.
		 * @param \Tribe__Context $context        Context we are using to build the view.
		 * @param View_Interface  $view           Which view we are using the template on.
		 */
		do_action(
			"tribe_events_views_v2_widget_{$this->view_slug}_after_enqueue_assets",
			$should_enqueue,
			$context,
			$view
		);
	}

	/**
	 * Enqueues the assets for widgets.
	 *
	 * @since 5.3.0
	 *
	 * @param \Tribe__Context $context Context we are using to build the view.
	 * @param View_Interface  $view    Which view we are using the template on.
	 */
	public function enqueue_assets( $context, $view ) {
		// Ensure we also have all the other things from Tribe\Events\Views\V2\Assets we need.
		tribe_asset_enqueue_group( Assets::$widget_group_key );
	}

	/**
	 * Determines whether to enqueue assets for widgets.
	 *
	 * @since 5.3.0
	 *
	 * @param \Tribe__Context $context Context we are using to build the view.
	 * @param View_Interface  $view    Which view we are using the template on.
	 *
	 * @return bool Whether assets are enqueued or not.
	 */
	public function should_enqueue_assets( $context, $view ) {
		/**
		 * Allow other plugins to hook in here to alter the enqueue.
		 *
		 * @since 5.3.0
		 *
		 * @param boolean         $enqueue Should the widget assets be enqueued. Defaults to true.
		 * @param \Tribe__Context $context Context we are using to build the view.
		 * @param View_Interface  $view    Which view we are using the template on.
		 */
		$enqueue = apply_filters(
			'tribe_events_views_v2_widget_enqueue_assets',
			true,
			$context,
			$view
		);

		/**
		 * Allow other plugins to hook in here to alter the enqueue for a specific widget type.
		 *
		 * @since 5.3.0
		 *
		 * @param boolean         $enqueue Should the widget assets be enqueued.
		 * @param \Tribe__Context $context Context we are using to build the view.
		 * @param View_Interface  $view    Which view we are using the template on.
		 */
		$enqueue = apply_filters(
			"tribe_events_views_v2_widget_{$this->view_slug}_enqueue_assets",
			$enqueue,
			$context,
			$view
		);

		return $enqueue;
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
	 * Returns the widget slug.
	 *
	 * @since 5.3.0
	 *
	 * @return string The widget slug.
	 */
	public function get_slug() {
		return $this->slug;
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
	 * Handles gathering the data for admin fields.
	 *
	 * @since 5.3.0
	 *
	 * @param array<string,mixed> $arguments Current set of arguments.
	 * @param int                 $field_name    The ID of the field.
	 * @param array<string,mixed> $field       The field info.
	 *
	 * @return array<string,mixed> $data The assembled field data.
	 */
	public function get_admin_data( $arguments, $field_name, $field ) {
		$data = [
			'classes'     => Arr::get( $field, 'classes', '' ),
			'dependency'  => $this->format_dependency( $field ),
			'id'          => $this->get_field_id( $field_name ),
			'label'       => Arr::get( $field, 'label', '' ),
			'name'        => $this->get_field_name( $field_name ),
			'options'     => Arr::get( $field, 'options', [] ),
			'placeholder' => Arr::get( $field, 'placeholder', '' ),
			'value'       => Arr::get( $arguments, $field_name ),
		];

		$children = Arr::get( $field, 'children', [] );

		if ( ! empty( $children ) ) {
			foreach ( $children as $child_name => $child ) {
				$input_name =  ( 'radio' === $child['type'] ) ? $field_name : $child_name;

				$child_data = $this->get_admin_data(
					$arguments,
					$input_name,
					$child
				);

				$data['children'][ $child_name ] = $child_data;
			}
		}

		$data = array_merge( $field, $data );

		return apply_filters( 'tribe_events_views_v2_widget_field_data', $data, $field_name, $this );
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
}
