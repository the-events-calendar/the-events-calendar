<?php
class Tribe_ECP_Custom_Meta {
	public static function init() {
		add_action( 'tribe_events_options_bottom', array( __CLASS__, 'event_meta_options' ) );
		add_filter( 'tribe-events-options', array( __CLASS__, 'save_meta_options' ) );
	}
	
	public static function event_meta_options() {
		global $sp_ecp;
		$customFields = tribe_get_option('custom-fields');
		$count = 1;
		include( $sp_ecp->pluginPath . 'admin-views/event-meta-options.php' );
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
					'name'=>$name,
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
