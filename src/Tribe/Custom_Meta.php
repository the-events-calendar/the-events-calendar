<?php

/**
 * "Additional Fields" implementation.
 *
 * Allows users to create custom fields via the Events > Settings > Additional Fields
 * tab that will then become common to all events and can be set via the event editor.
 *
 * ECP's custom/additional fields are stored in the post meta table and may be recorded
 * in two different ways. One is a historical form that also provides fast retrieval,
 * where multiple values are contained in a pipe-separated format within a single record,
 * ie:
 *
 *     meta_key       meta_value
 *     -------------  -----------
 *     _ecp_custom_x  apples|greengages|oranges
 *
 * As of 4.0, multiple values like the above example of several tasty fruits available
 * at a theoretical market event will additionally be stored in separate records, closer
 * to how WordPress does things organically:
 *
 *     meta_key        meta_value
 *     -------------   -----------
 *     __ecp_custom_x  apples
 *     __ecp_custom_x  greengages
 *     __ecp_custom_x  oranges
 *
 * Note the key for the second arrangement differs by a single leading underscore. This
 * facilitates easier and more flexible searching of additional fields when desired with
 * only a slight storage overhead. By default, this will only happen for field types that
 * support multiple values (such as the checkbox type).
 */
class Tribe__Events__Pro__Custom_Meta {
	/**
	 * List of field types supporting the assignment of multiple values.
	 *
	 * @var array
	 */
	protected static $multichoice_types = array(
		'checkbox'
	);


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
	 * Given an array representing a custom field structure, or a string representing a field
	 * type, returns true if the type is considered "multichoice".
	 *
	 * @param array|string $structure_or_type
	 *
	 * @return bool
	 */
	public static function is_multichoice( $structure_or_type ) {
		$field_type = ( is_array( $structure_or_type ) && isset( $structure_or_type['type'] ) )
			? $structure_or_type['type']
			: $structure_or_type;

		$is_multichoice = in_array( $field_type, self::get_multichoice_fields_list() );

		/**
		 * Controls whether the specified type should be considered "multichoice", which can impact
		 * whether or not individual post meta records are generated when storing the field.
		 *
		 * @var bool   $is_multichoice
		 * @var string $field_type
		 */
		return apply_filters( 'tribe_events_pro_field_is_multichoice', $is_multichoice, $field_type );
	}

	/**
	 * Returns a list of additional field types deemed "multichoice" in nature.
	 *
	 * @return array
	 */
	public static function get_multichoice_fields_list() {
		static $field_list;

		// If we have already built our list of multichoice field types, return it directly!
		if ( isset( $field_list ) ) {
			return $field_list;
		}

		/**
		 * The list of additional field types to be considered "multichoice" (ie, where admins can
		 * assign multiple possible values to the same post).
		 *
		 * @var array $multichoice_types
		 */
		$field_list = (array) apply_filters( 'tribe_events_pro_multichoice_field_types', self::$multichoice_types );
		return $field_list;
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
		$options = Tribe__Settings_Manager::get_options();
		array_splice( $options['custom-fields'], $_POST['field'] - 1, 1 );
		Tribe__Settings_Manager::set_options( $options, false );
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
	 * Saves the custom fields for a single event.
	 *
	 * In the case of fields where mutiple values have been assigned (or even if only
	 * a single value was assigned - but the field type itself _supports_ multiple
	 * values, such as a checkbox field) an additional set of records will be created
	 * storing each value in a separate row of the postmeta table.
	 *
	 * @param $post_id
	 * @param $data
	 *
	 * @return void
	 * @see 'tribe_events_update_meta'
	 */
	public static function save_single_event_meta( $post_id, $data = array() ) {
		$custom_fields = (array) tribe_get_option( 'custom-fields' );

		foreach ( $custom_fields as $custom_field ) {
			// If the field name (ie, "_ecp_custom_x") has not been set then we cannot store it
			if ( ! isset( $custom_field['name'] ) ) {
				continue;
			}
			
			$ordinary_field_name = wp_kses_data( $custom_field['name'] );
			$searchable_field_name = '_' . $ordinary_field_name;

			// Grab the new value and reset the searchable records container
			$value = self::get_value_to_save( $custom_field['name'], $data );
			$searchable_records = array();

			// If multiple values have been assigned (ie, if this is a checkbox field or similar) then
			// build a single pipe-separated field and a list of individual records
			if ( is_array( $value ) ) {
				$ordinary_record    = esc_attr( implode( '|', str_replace( '|', '', $value ) ) );
				$searchable_records = $value;
			} 
			// If we have only a single value we may still need to record an extra entry if the type
			// of field is multichoice in nature
			else {
				
				$searchable_records[] = $ordinary_record = wp_kses(
					$value,
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

			// Store the combined field
			update_post_meta( $post_id, $ordinary_field_name, $ordinary_record );

			// If this is not a multichoice field *and* there is only a single value we can move to the
			// next record, otherwise we should continue and store each value individually
			if ( ! self::is_multichoice( $custom_field ) && count( $searchable_records ) === 1 ) {
				continue;
			}

			// Kill all existing searchable custom fields first of all
			delete_post_meta( $post_id, $searchable_field_name );

			// Rebuild with the new values
			foreach ( $searchable_records as $single_value ) {
				add_post_meta( $post_id, $searchable_field_name, $single_value );
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
		$options = Tribe__Settings_Manager::get_options();
		$options = self::save_meta_options( $options );
		Tribe__Settings_Manager::set_options( $options );
	}

	/**
	 * add custom meta fields to the event array passed thru the importer
	 */
	public static function import_additional_fields( $import_fields ) {
		$custom_fields = (array) tribe_get_option( 'custom-fields' );
		foreach ( $custom_fields as $custom_field ) {
			if ( empty( $custom_field['name'] ) || empty( $custom_field['label'] ) ) {
				continue;
			}
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
