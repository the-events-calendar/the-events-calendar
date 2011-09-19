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
		add_action( 'tribe_events_options_bottom', array( __CLASS__, 'event_meta_options' ) );
      	add_action( 'tribe_events_details_table_bottom', array(__CLASS__, 'single_event_meta') );
		add_action( 'tribe_community_events_details_table_bottom', array(__CLASS__, 'single_event_meta') );			
		add_action( 'tribe_events_update_meta', array(__CLASS__, 'save_single_event_meta') );
		add_filter( 'tribe-events-save-options', array( __CLASS__, 'save_meta_options' ) );	
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
		include( TribeEventsPro::instance()->pluginPath . 'admin-views/event-meta.php' );
    }

	/**
	 * save_single_event_meta
	 * 
	 * saves the custom fields for a single event
	 * 
	 * @return void
	 */
    public static function save_single_event_meta($postId) {
		$customFields = (array)tribe_get_option('custom-fields');

      	foreach( $customFields as $customField) {
			$val = $_POST[$customField['name']];
        	$val = is_array($val) ? implode("|", $val) : $val;
        	update_post_meta($postId,  wp_kses_data($customField['name']), $val);
		}
    }
	
	/**
	 * save_meta_options
	 * 
	 * saves the custom field configuration (what custom fields exist for events)
	 * 
	 * @return void
	 */	
	public static function save_meta_options($ecp_options) {
		$count = 1;
		$ecp_options['custom-fields'] = array();

		while( isset($_POST['custom-field-' . $count]) ) {
			$name = $_POST['custom-field-' . $count];
			$type = $_POST['custom-field-type-' . $count];
			$values = $_POST['custom-field-options-' . $count];

			if( $name ) {
				$ecp_options['custom-fields'][] = array(
               'name'=>'_ecp_custom_' . $count,
					'label'=>$name,
					'type'=>$type,
					'values'=>$values
				);
			}
				
			$count++;
		}

		return $ecp_options;
	}
}
