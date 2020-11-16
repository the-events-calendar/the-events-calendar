<?php
/**
 * Widget Admin Template - handles the presentation on the widgets in the admin.
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

	/**
	 * Allowed field types.
	 *
	 * @var array<string>
	 */
	public $allowed_field_types = [
		'checkbox',
		'radio',
		'dropdown',
		'text',
		'multiselect',
	];

	/**
	 * Placeholder for the widget object.
	 *
	 * @var WP_Widget
	 */
	private $widget_obj;

	/**
	 * Template constructor.
	 *
	 * @since TBD
	 */
	public function __construct() {
		$this->set_template_origin( tribe( 'tec.main' ) );
		$this->set_template_folder( 'src/admin-views' );

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
	 */
	public function print_form( $widget_obj, $admin_fields ) {
		$this->widget_obj = $widget_obj;
		foreach ( $admin_fields as $field_id => $field ) {
			// Can't do anything if we don't know what we're dealing with.
			if ( empty( $field['type'] ) ) {
				continue;
			}

			$this->section_handler( $field_id, $field );
		}
	}

	/**
	 * Conditional templating to allow for non-input entries and recursion.
	 *
	 * @since TBD
	 *
	 * @param int                 $field_id    The ID of the field.
	 * @param array<string,mixed> $field       The field info.
	 * @param array<string,mixed> $passthrough Passthrough data (from parent - like fieldset, to children).
	 */
	public function section_handler( $field_id, $field, $passthrough = [] ) {
		if ( 'section' === $field['type'] || 'fieldset' === $field['type'] ) {
			$this->print_section( $field_id, $field, $passthrough );
		} else {
			$this->print_input( $field_id, $field, $passthrough );
		}
	}

	/**
	 * Section templating to handle control/input sections and fieldsets.
	 *
	 * @since TBD
	 *
	 * @param int                 $field_id    The ID of the field.
	 * @param array<string,mixed> $field       The field info.
	 * @param array<string,mixed> $passthrough Passthrough data (from a parent - like fieldset, to its children).
	 */
	public function print_section( $field_id, $field, $passthrough = [] ) {
		$data = $this->widget_obj->get_admin_data( $field_id, $field, $passthrough, $this->context );

		$this->template( "widgets/components/{$field['type']}", $data, $field, $passthrough );
	}

	/**
	 * Input templating.
	 *
	 * @since TBD
	 *
	 * @param int                 $field_id    The ID of the field.
	 * @param array<string,mixed> $field       The field info.
	 * @param array<string,mixed> $passthrough Passthrough data (from a parent - like fieldset, to its children).
	 */
	public function print_input( $field_id, $field, $passthrough = [] ) {
		$data = $this->widget_obj->get_admin_data( $field_id, $field, $passthrough, $this->context );

		if ( in_array( $field['type'], $this->allowed_field_types ) ) {
			$this->template( "widgets/components/{$field['type']}", $data );
		} else {
			/**
			 * Allows injection of custom "inputs" by other plugins.
			 *
			 * @since TBD
			 *
			 * @param array<string,mixed> $data       The field data passed to templates.
			 * @param array<string,mixed> $field      The field info.
			 * @param WP_Widget           $widget_obj The widget object.
			 */
			do_action( "tribe_events_views_v2_widget_admin_form_{$field['type']}_input", $data, $field, $this->widget_obj, $this->context );
		}
	}
}
