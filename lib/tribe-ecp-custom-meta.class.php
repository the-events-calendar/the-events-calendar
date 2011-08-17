<?php
/*
 * This class allows events to have user-defined custom meta
 */
class Tribe_ECP_Custom_Meta {
	public static function init() {
		add_action( 'tribe_events_options_bottom', array( __CLASS__, 'event_meta_options' ) );
		add_filter( 'tribe-events-options', array( __CLASS__, 'save_meta_options' ) );
      	add_action( 'tribe_events_details_table_bottom', array(__CLASS__, 'single_event_meta') );
		add_action( 'tribe_community_events_details_table_bottom', array(__CLASS__, 'single_event_meta') );		
      	add_action( 'tribe_events_update_meta', array(__CLASS__, 'save_single_event_meta') );
		add_action( 'wp_ajax_remove_option', array(__CLASS__, 'remove_meta_field') );
	}

    public static function remove_meta_field() {
		global $wpdb, $sp_ecp;
      	$options = $sp_ecp->getOptions();
      	array_splice($options['custom-fields'], $_POST['field'] - 1, 1);
      	$sp_ecp->saveOptions($options);
      	$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_key=%s", '_ecp_custom_' . $_POST['field']));
      	die();
    }
	
	public static function event_meta_options() {
		global $sp_ecp;
		$customFields = tribe_get_option('custom-fields');
		$count = 1;
		include( ECP_Premium::instance()->pluginPath . 'admin-views/event-meta-options.php' );
	}

    public static function single_event_meta() {
		global $sp_ecp;
      	$customFields = tribe_get_option('custom-fields');
		include( ECP_Premium::instance()->pluginPath . 'admin-views/event-meta.php' );
    }

    public static function save_single_event_meta($postId) {
		$customFields = tribe_get_option('custom-fields');

      	foreach( $customFields as $customField) {
			$val = $_POST[$customField['name']];
        	$val = is_array($val) ? implode("|", $val) : $val;
        	update_post_meta($postId, $customField['name'], $val);
		}
    }
	
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
?>
