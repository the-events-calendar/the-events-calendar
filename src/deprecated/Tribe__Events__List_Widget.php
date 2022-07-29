<?php
_deprecated_file( __FILE__, '6.0.0', '', 'This widget has been replaced by src/Tribe/Views/V2/Widgets/Widget_List.php' );
/**
 * Event List Widget
 *
 * @deprecated 6.0.0
 *
 * Creates a widget that displays the next upcoming x events
 */
class Tribe__Events__List_Widget extends \Tribe\Events\Views\V2\Widgets\Widget_List {
	/**
	 * The main widget output function (called by the class's widget() function).
	 *
	 * @deprecated 6.0.0
	 */
	public function widget_output( $args, $instance, $template_name = 'widgets/list-widget' ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * Output the admin form for the widget.
	 *
	 * @deprecated 6.0.0
	 */
	public function form( $instance ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * Enqueue the appropriate CSS for the list widget
	 *
	 * @deprecated 6.0.0
	 */
	public static function enqueue_widget_styles() {
		_deprecated_function( __METHOD__, '6.0.0' );
	}
}