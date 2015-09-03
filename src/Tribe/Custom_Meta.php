<?php

/**
 * Tribe__Events__Pro__Custom_Meta
 *
 * This class allows users to create custom fields in the settings & displays the
 * custom fields in the event editor
 */
class Tribe__Events__Pro__Custom_Meta {
	public static function init() {
		add_action( 'wp_ajax_remove_option', array( __CLASS__, 'remove_meta_field' ) );
		add_action( 'tribe_settings_after_content_tab_additional-fields', array( __CLASS__, 'event_meta_options' ) );
		add_action( 'tribe_events_details_table_bottom', array( __CLASS__, 'single_event_meta' ) );
		add_action( 'tribe_events_update_meta', array( __CLASS__, 'save_single_event_meta' ), 10, 2 );
		add_filter( 'tribe_settings_validate_tab_additional-fields', array( __CLASS__, 'force_save_meta' ) );
		add_filter( 'tribe_events_csv_import_event_additional_fields', array( __CLASS__, 'import_additional_fields' ) );
		add_filter( 'tribe_events_importer_event_column_names', array( __CLASS__, 'importer_column_mapping' ) );

	}

	/**
	 * remove_meta_field
	 *
	 * Removes a custom field from the database and from any events that may be using that field.
	 * @return void
	 */
	public static function remove_meta_field() {
		global $wpdb, $tribe_ecp;
		if ( ! isset( $tribe_ecp ) ) {
			$tribe_ecp = Tribe__Events__Main::instance();
		}
		$options = $tribe_ecp->getOptions();
		array_splice( $options['custom-fields'], $_POST['field'] - 1, 1 );
		$tribe_ecp->setOptions( $options, false );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key=%s", '_ecp_custom_' . $_POST['field'] ) );
		die();
	}

	/**
	 * event_meta_options
	 *
	 * loads the custom field options screen
	 *
	 * @return void
	 */
	public static function event_meta_options() {
		$pro = Tribe__Events__Pro__Main::instance();

		// Grab the custom fields and append an extra blank row at the end
		$customFields   = tribe_get_option( 'custom-fields' );
		$customFields[] = array();

		// Counts used to decide whether the "remove field" or "add another" should appear
		$total = count( $customFields );
		$count = 0;
		$add_another  = esc_html( __( 'Add another', 'tribe-events-calendar-pro' ) );
		$remove_field = esc_html( __( 'Remove', 'tribe-events-calendar-pro' ) );

		// Settings for regular WordPress custom fields
		$disable_metabox_custom_fields = $pro->displayMetaboxCustomFields() ? 'show' : 'hide';

		include $pro->pluginPath . 'src/admin-views/event-meta-options.php';
	}

	/**
	 * single_event_meta
	 *
	 * loads the custom field meta box on the event editor screen
	 *
	 * @return void
	 */
	public static function single_event_meta() {
		$tribe_ecp    = Tribe__Events__Main::instance();
		$customFields = tribe_get_option( 'custom-fields' );

		$events_event_meta_template = Tribe__Events__Pro__Main::instance()->pluginPath . 'src/admin-views/event-meta.php';
		$events_event_meta_template = apply_filters( 'tribe_events_event_meta_template', $events_event_meta_template );
		if ( ! empty( $events_event_meta_template ) ) {
			include( $events_event_meta_template );
		}
	}

	/**
	 * save_single_event_meta
	 *
	 * saves the custom fields for a single event
	 *
	 * @param $postId
	 * @param $data
	 *
	 * @return void
	 * @see 'tribe_events_update_meta'
	 */
	public static function save_single_event_meta( $postId, $data = array() ) {
		$customFields = (array) tribe_get_option( 'custom-fields' );
		foreach ( $customFields as $customField ) {
			if ( isset( $customField['name'] ) ) {
				$val = self::get_value_to_save( $customField['name'], $data );

				if ( is_array( $val ) ) {
					$val = esc_attr( implode( '|', str_replace( '|', '', $val ) ) );
				} else {
					$val = wp_kses(
						$val,
						array(
							'a' => array(
								'href'   => array(),
								'title'  => array(),
								'target' => array(),
							),
							'b'      => array(),
							'i'      => array(),
							'strong' => array(),
							'em'     => array(),
						)
					);
				}

				update_post_meta( $postId, wp_kses_data( $customField['name'] ), $val );
			}
		}
	}

	/**
	 * Checks passed metadata array for a custom field, returns its value
	 * If the value is not found in the passed array, checks the $_POST for the value
	 *
	 * @param $name
	 * @param $data
	 *
	 * @return string
	 */
	private static function get_value_to_save( $name, $data ) {
		$value = '';
		if ( ! empty( $data ) && ! empty( $data[ $name ] ) ) {
			$value = $data[ $name ];
		} elseif ( ! empty( $_POST[ $name ] ) ) {
			$value = $_POST[ $name ];
		}
		return $value;
	}

	/**
	 * enforce saving on additional fields tab
	 * @return void
	 */
	public static function force_save_meta() {
		$options = Tribe__Events__Main::getOptions();
		$options = self::save_meta_options( $options );
		Tribe__Events__Main::instance()->setOptions( $options );
	}

	/**
	 * add custom meta fields to the event array passed thru the importer
	 */
	public static function import_additional_fields( $import_fields ) {
		$custom_fields = (array) tribe_get_option( 'custom-fields' );
		foreach ( $custom_fields as $custom_field ) {
			$import_fields[ $custom_field['name'] ] = $custom_field['label'];
		}
		return $import_fields;
	}

	/**
	 * add custom meta fields to the column mapping passed to the importer
	 */
	public static function importer_column_mapping( $column_mapping ) {
		$custom_fields = (array) tribe_get_option( 'custom-fields' );
		foreach ( $custom_fields as $custom_field ) {
			if (
				! is_array( $custom_field )
				|| empty( $custom_field['name'] )
				|| ! isset( $custom_field['label'] )
			) {
				continue;
			}

			$column_mapping[ $custom_field['name'] ] = $custom_field['label'];
		}
		return $column_mapping;
	}

	/**
	 * Save/update the additional field structure.
	 *
	 * @param $ecp_options
	 *
	 * @return array
	 */
	public static function save_meta_options( $ecp_options ) {
		// The custom-fields key may not exist if not fields have been defined
		$ecp_options['custom-fields'] = isset( $ecp_options['custom-fields'] ) ? $ecp_options['custom-fields'] : array();

		// Maintain a record of the highest assigned custom field index
		$max_index = isset( $ecp_options['custom-fields-max-index'] )
			? $ecp_options['custom-fields-max-index']
			: count( $ecp_options['custom-fields'] ) + 1;

		// Clear the existing list of custom fields
		$ecp_options['custom-fields'] = array();

		// save the view state for custom fields
		$ecp_options['disable_metabox_custom_fields'] = $_POST['disable_metabox_custom_fields'];

		foreach ( $_POST['custom-field'] as $index => $field ) {
			$name   = wp_kses( stripslashes( $_POST['custom-field'][ $index ] ), array() );
			$type   = 'text';
			$values = '';

			// For new fields, it's possible the type/value hasn't been defined (fallback to defaults if so)
			if ( isset( $_POST['custom-field-type'][ $index ] ) ) {
				$type   = wp_kses( stripslashes( $_POST['custom-field-type'][ $index ] ), array() );
				$values = wp_kses( stripslashes( $_POST['custom-field-options'][ $index ] ), array() );
			}

			// Remove empty lines
			$values = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n", $values );
			$values = rtrim( $values );
			//Remove Vertical Bar for Checkbox Field
			$values = $type == 'checkbox' ? str_replace( '|', '', $values ) : $values;

			// The indicies of pre-existing custom fields begin with an underscore - so if
			// the index does not have an underscore we need to assign a new one
			if ( 0 === strpos( $index, '_' ) ) {
				$assigned_index = substr( $index, 1 );
			} else {
				$assigned_index = ++ $max_index;
			}

			if ( $name ) {
				$ecp_options['custom-fields'][ $assigned_index ] = array(
					'name'   => '_ecp_custom_' . $assigned_index,
					'label'  => $name,
					'type'   => $type,
					'values' => $values,
				);
			}
		}

		// Update the max index and return the updated options array
		$ecp_options['custom-fields-max-index'] = $max_index;

		return $ecp_options;
	}

	/**
	 * get_custom_field_by_label
	 *
	 * retrieve a custom field's value by searching its label
	 * instead of its (more obscure) ID
	 *
	 * @param  (string) $label, the label to search for
	 * @param  (int) $eventID (optional), the event to look for, defaults to global $post
	 *
	 * @return (string) value of the field
	 */
	public static function get_custom_field_by_label( $label, $eventID = null ) {
		$eventID      = Tribe__Events__Main::postIdHelper( $eventID );
		$customFields = tribe_get_option( 'custom-fields', false );
		if ( is_array( $customFields ) ) {
			foreach ( $customFields as $field ) {
				if ( $field['label'] == $label ) {
					return get_post_meta( $eventID, $field['name'], true );
				}
			}
		}
	}

}
