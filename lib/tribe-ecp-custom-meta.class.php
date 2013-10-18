<?php

/**
 * TribeEventsCustomMeta
 *
 * This class allows users to create custom fields in the settings & displays the
 * custom fields in the event editor
 * @author John Gadbois
 */
class TribeEventsCustomMeta {
	public static function init() {
		add_action( 'wp_ajax_remove_option', array(__CLASS__, 'remove_meta_field') );
		add_action( 'tribe_settings_after_content_tab_additional-fields', array( __CLASS__, 'event_meta_options' ) );
		add_action( 'tribe_events_details_table_bottom', array(__CLASS__, 'single_event_meta') );
		add_action( 'tribe_events_update_meta', array(__CLASS__, 'save_single_event_meta') );
		add_filter( 'tribe_settings_validate_tab_additional-fields', array( __CLASS__, 'force_save_meta' ) );	
	}

	/**
	 * remove_meta_field
	 * 
	 * Removes a custom field from the database and from any events that may be using that field.
	 * @return void
	 * @author  
	 */
    public static function remove_meta_field() {
			global $wpdb, $tribe_ecp;
			if ( ! isset( $tribe_ecp ) ) {
				$tribe_ecp = TribeEvents::instance();
			}
			$options = $tribe_ecp->getOptions();
	  	array_splice($options['custom-fields'], $_POST['field'] - 1, 1);
			$tribe_ecp->setOptions($options, false);
	  	$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_key=%s", '_ecp_custom_' . $_POST['field']));
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
		$tribe_ecp = TribeEvents::instance();
		$customFields = tribe_get_option('custom-fields');
		$disable_metabox_custom_fields = tribe_get_option('disable_metabox_custom_fields');
		// if we don't know the value lets setup a default
		if( empty($disable_metabox_custom_fields) ){
			$disable_metabox_custom_fields =  TribeEventsPro::displayMetaboxCustomFields() ? "hide" : "show";	
		}
		
		$count = 1;
		include( TribeEventsPro::instance()->pluginPath . 'admin-views/event-meta-options.php' );
	}

	/**
	 * single_event_meta
	 * 
	 * loads the custom field meta box on the event editor screen
	 * 
	 * @return void
	 */
    public static function single_event_meta() {
		$tribe_ecp = TribeEvents::instance();
		$customFields = tribe_get_option('custom-fields');

		$events_event_meta_template = TribeEventsPro::instance()->pluginPath . 'admin-views/event-meta.php';
		$events_event_meta_template = apply_filters('tribe_events_event_meta_template', $events_event_meta_template);
		if( !empty($events_event_meta_template) )
			include( $events_event_meta_template );
    }

	/**
	 * save_single_event_meta
	 *
	 * saves the custom fields for a single event
	 *
	 * @param $postId
	 * @return void
	 */
    public static function save_single_event_meta($postId) {
		$customFields = (array)tribe_get_option('custom-fields');

		foreach( $customFields as $customField) {
			if( isset( $customField['name'] ) ) {
				if ( !isset( $_POST[$customField['name']] ) )
					$_POST[$customField['name']] = '';
				$val = $_POST[$customField['name']];
				$val = is_array($val) ? esc_attr(implode("|", $val)) : wp_kses( $val, array( 'a' => array( 'href' => array(), 'title' => array(), 'target' => array() ), 'b' => array(), 'i' => array(), 'strong' => array(), 'em' => array() ) );
				update_post_meta($postId,  wp_kses_data($customField['name']), $val);
			}
		}
    }

	/**
	 * enforce saving on additional fields tab
	 * @author jkudish
	 * @since 2.0.5
	 * @return void
	 */
	public static function force_save_meta() {
		$options = TribeEvents::getOptions();
		$options = self::save_meta_options($options);
		TribeEvents::instance()->setOptions($options);
	}

	/**
	 * save_meta_options
	 *
	 * saves the custom field configuration (what custom fields exist for events)
	 *
	 * @param $ecp_options
	 *
	 * @return array
	 */
	public static function save_meta_options($ecp_options) {
		$count = 1;
		$ecp_options['custom-fields'] = array();

		// save the view state for custom fields
		$ecp_options['disable_metabox_custom_fields'] = $_POST['disable_metabox_custom_fields'];
		
		for ( $i = 0; $i < count( $_POST['custom-field'] ); $i++ ) {
			$name = strip_tags( $_POST['custom-field'][$i] );
			$type = strip_tags( $_POST['custom-field-type'][$i] );
			$values = strip_tags( $_POST['custom-field-options'][$i] );

			// Remove empty lines
			$values = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n", $values );
			$values = rtrim( $values );

		/*while( isset($_POST['custom-field-' . $count]) ) {
			$name = strip_tags($_POST['custom-field-' . $count]);
			$type = strip_tags($_POST['custom-field-type-' . $count]);
			$values = strip_tags($_POST['custom-field-options-' . $count]);
		*/
			if( $name ) {
				$ecp_options['custom-fields'][] = array(
          'name' => '_ecp_custom_' . $count,
					'label' => $name,
					'type' => $type,
					'values' => $values
				);
			}

			$count++;
		}

		return $ecp_options;
	}

	/**
	 * get_custom_field_by_label
	 *
	 * retrieve a custom field's value by searching its label
	 * instead of its (more obscure) ID
	 * @author Joachim Kudish
	 * @since 2.0.3
	 * @param (string) $label, the label to search for
	 * @param (int) $eventID (optional), the event to look for, defaults to global $post
	 * @return (string) value of the field
	 */
	public static function get_custom_field_by_label($label, $eventID = null) {
		$eventID = TribeEvents::postIdHelper( $eventID );
		$customFields = tribe_get_option('custom-fields', false);
		if (is_array($customFields))
			foreach ($customFields as $field)
				if ($field['label'] == $label)
					return get_post_meta($eventID, $field['name'], true);
	}

}