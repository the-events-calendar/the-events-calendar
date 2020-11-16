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
 * @var Widget_Abstract $widget_obj   An instance with the widget abstract.
 * @var array<array>    $admin_fields An array of admin fields to display in the widget form.
 *
 * @version TBD
 */

use \Tribe\Events\Views\V2\Widgets\Widget_Abstract;
use Tribe__Utils__Array as Arr;

if ( empty( $admin_fields ) ) {
	return;
}

$this->print_form( $widget_obj, $admin_fields );
