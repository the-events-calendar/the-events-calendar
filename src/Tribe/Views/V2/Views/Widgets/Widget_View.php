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
}
