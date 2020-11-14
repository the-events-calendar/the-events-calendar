<?php
/**
 * Widget Admin Templates
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

use Tribe__Utils__Array as Arr;

/**
 * Class Admin_Template
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Admin_Template extends \Tribe__Template {

	public $allowed_field_types = [
		'checkbox',
		'radio',
		'dropdown',
		'text',
		'multiselect',
	];

	private $widget_obj;

	/**
	 * Template constructor.
	 *
	 * @since TBD
	 */
	public function __construct() {
		$this->set_template_origin( tribe( 'tec.main' ) );
		$this->set_template_folder( 'src/admin-views' );

		/**
		 * Allows other plugins to add new/custom field types.
		 *
		 * @since TBD
		 *
		 * @param array<string> An array of field-type "slugs".
		 */
		$this->allowed_field_types = apply_filters( 'tribe_events_views_v2_widget_allowed_field_types', $this->allowed_field_types );

		// We specifically don't want to look up template files here.
		$this->set_template_folder_lookup( false );

		// Configures this templating class extract variables.
		$this->set_template_context_extract( true );
	}

	/**
	 * Sets global widget object and filters out any unexpected data before passing to the conditionals.
	 *
	 * @since TBD
	 *
	 * @param WP_Widget           $widget_obj   The widget object.
	 * @param array<string,mixed> $admin_fields The array of field(s) data.
	 * @return void
	 */
	public function structure( $widget_obj, $admin_fields ) {
		$this->widget_obj = $widget_obj;
		foreach ( $admin_fields as $field_id => $field ) {
			// Can't do anything if we don't know what we're dealing with.
			if ( empty( $field['type'] ) ) {
				continue;
			}

			$this->maybe_input( $field_id, $field );
		}
	}

	/**
	 * Conditional templating to allow for non-input entries and recursion.
	 *
	 * @since TBD
	 *
	 * @param int                  $field_id    The ID of the field.
	 * @param array <string,mixed> $field       The field data.
	 * @param array                $passthrough Passthrough data (from parent - like fieldset, to children).
	 */
	public function maybe_input( $field_id, $field, $passthrough = [] ) {
		if ( 'section' === $field['type'] || 'fieldset' === $field['type'] ) {
			$this->section( $field_id, $field, $passthrough );
		} else {
			$this->input( $field_id, $field, $passthrough );
		}
	}

	public function section( $field_id, $field, $passthrough = [] ) {
		$data = [
			'id'         => $this->widget_obj->get_field_id( $field_id ),
			'name'       => $this->widget_obj->get_field_name( $field_id ),
			'label'      => Arr::get( $field, 'label', '' ),
			'options'    => Arr::get( $field, 'options', [] ),
			'value'      => Arr::get( $this->context, $field_id, [] ),
			'classes'    => Arr::get( $field, 'classes', '' ),
			'children'   => Arr::get( $field, 'children', '' ),
			'dependency' => $this->format_dependency( $field ),
		];

		$this->template( "widgets/components/{$field['type']}", $data, $field, $passthrough );
	}

	public function input( $field_id, $field, $passthrough = [] ) {
		$data = [
			'id'          => $this->widget_obj->get_field_id( $field_id ),
			'name'        => $this->widget_obj->get_field_name( $field_id ),
			'label'       => Arr::get( $field, 'label', '' ),
			'options'     => Arr::get( $field, 'options', [] ),
			'value'       => Arr::get( $this->context, $field_id, [] ),
			'classes'     => Arr::get( $field, 'classes', '' ),
			'placeholder' => Arr::get( $field, 'placeholder', '' ),
			'dependency'  => $this->format_dependency( $field ),
		];

		if ( 'radio' === $field['type'] ) {
			$data[ 'button_value' ] = Arr::get( $field, 'button_value', '' );
			$data['name']           = Arr::get( $passthrough, 'name', '' );
			$data['value']          = Arr::get( $passthrough, 'value', null );
		}

		if ( in_array( $field['type'], $this->allowed_field_types ) ) {
			$this->template( "widgets/components/{$field['type']}", $data );
		} else {
			do_action( "tribe_events_view_v2_widget_admin_form_{$field['type']}_input", $data, $field, $this->widget_obj, $this->context );
		}
	}

	/**
	 * Massages the data before asking tribe_format_field_dependency() to create the dependency attributes.
	 *
	 * @since TBD
	 *
	 * @param [type] $field
	 * @return void
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
			return;
		} else {
			$deps['id'] = $this->widget_obj->get_field_id( $deps['id'] );
		}

		return tribe_format_field_dependency( $deps );
	}
}
