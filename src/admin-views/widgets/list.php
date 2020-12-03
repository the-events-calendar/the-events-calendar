<?php
/**
 * Admin View: List Widget
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/widgets/list.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @var Widget_Abstract $widget_obj   An instance of the widget abstract.
 * @var array<array,mixed>    $admin_fields An array of admin fields to display in the widget form.
 *
 * @version 5.3.0
 */

foreach ( $admin_fields as $field ) {
	$this->template( "widgets/components/{$field['type']}", $field );

	/**
	 * Allows other plugins to hook in as needed to inject things that aren't necessarily an input.
	 *
	 * @since 5.3.0
	 *
	 * @param array<array,mixed> $field The "field" info.
	 * @var Widget_Abstract $widget_obj An instance of the widget abstract.
	 */
	do_action( "tribe_events_views_v2_widget_admin_form_{$field['type']}_input", $field, $widget_obj );
}
