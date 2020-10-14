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
 * @var array<array> $admin_fields An array of admin fields to display in the widget form.
 *
 * @version TBD
 */

use Tribe__Utils__Array as Arr;

if ( empty( $admin_fields ) ) {
	return;
}

foreach ( $admin_fields as $id => $field ) {

	if ( empty( $field['type'] ) ) {
		continue;
	}
	// todo lets not assume this and find a better way.
	$value = ${$id};

	$data = [
		'for'     => $widget_obj->get_field_id( $id ),
		'id'      => $widget_obj->get_field_id( $id ),
		'name'    => $widget_obj->get_field_name( $id ),
		'label'   => Arr::get( $field, 'label', '' ),
		'options' => Arr::get( $field, 'options', [] ),
		'value'   => $value,
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
