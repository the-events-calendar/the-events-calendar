<?php

// Don't load directly
if ( !defined('ABSPATH') ) die('-1');

if ( !class_exists('TribeSettingsTab') ) {

	/**
	 * helper class that creates a settings tab
	 * note: this is a work in progress, not everything is properly implemented yet
	 *
	 * @since 2.1
	 * @author jkudish
	 */
	class TribeSettingsTab {

		public static $id;
		public static $name;
		public static $fields;
		public static $order;
		public static $show_save;
		public static $display_callback;

		public function __construct($id, $name, $fields = array(), $placement = null, $show_save = true, $display_callback = false) {

			// set instance variables
			$this->id = apply_filters( 'tribe_settings_tab_id', $id );
			$this->name = apply_filters( 'tribe_settings_tab_name', $name );
			$this->fields = apply_filters( 'tribe_settings_tab_fields', $fields );
			$this->show_save = apply_filters( 'tribe_settings_tab_show_save', $show_save );
			$this->placement = apply_filters( 'tribe_settings_tab_placement', $placement );
			$this->display_callback = apply_filters( 'tribe_settings_tab_display_callback', $display_callback );

			// run actions & filters
			add_filter('tribe_settings_tabs', array(&$this, 'addTab') );

		}

		public function addTab($tabs) {
			$tabs[$this->id] = $this->name;
			return $tabs;
		}


	} // end class

} // endif class_exists