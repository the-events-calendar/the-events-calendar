<?php
/**
 * The Base Widget View.
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 * @since TBD
 */

namespace Tribe\Events\Views\V2\Views\Widgets;

use Tribe\Events\Views\V2\View;

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
	 * Overrides the base View method.
	 *
	 * @since TBD
	 *
	 * @return array<string,mixed> The Widget View template vars, modified if required.
	 */
	protected function setup_template_vars() {
		$template_vars = parent::setup_template_vars();

		$template_vars['container_classes'] = $this->get_html_classes();

		return $template_vars;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html_classes( array $classes = [] ) {
		$html_classes = parent::get_html_classes( [ 'tribe-events-widget' ] );

		/**
		 * Filters the HTML classes applied to a widget top-level container.
		 *
		 * @since TBD
		 *
		 * @param array  $html_classes Array of classes used for this widget.
		 * @param string $view_slug    The current widget slug.
		 * @param View   $instance     The current View object.
		 */
		$html_classes = apply_filters( 'tribe_events_views_v2_widget_html_classes', $html_classes, $this->get_slug(), $this );

		/**
		 * Filters the HTML classes applied to a specific widget top-level container.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @param array|null $args An array of associative arguments used to setup the repository for the View.
	 */
	public function setup_the_loop( array $args = [] ) {
		$args = wp_parse_args( $args, $this->repository_args );

		$this->repository->by_args( $args );
	}
}
