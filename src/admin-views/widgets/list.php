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

foreach ( $admin_fields as $field_id => $field ) {

	$data = [
		'for'     => $widget_obj->get_field_id( $field_id ),
		'id'      => $widget_obj->get_field_id( $field_id ),
		'name'    => $widget_obj->get_field_name( $field_id ),
		'label'   => Arr::get( $field, 'label', '' ),
		'options' => Arr::get( $field, 'options', [] ),
		'value'   => isset( ${$field_id} ) ? ${$field_id} : null,
	];

	switch ( $field['type'] ) {
		case 'checkbox':
			$this->template( 'widgets/components/checkbox', $data );
			break;
		case 'dropdown':
			$this->template( 'widgets/components/dropdown', $data );
			break;
		case 'text':
			$this->template( 'widgets/components/text', $data );
			break;
		default:
			break;
	}
}
