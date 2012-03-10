<?php

// Don't load directly
if ( !defined('ABSPATH') ) die('-1');

if ( !class_exists('TribeSettingsTab') ) {

	/**
	 * helper class that creates a settings tab
	 * note: this is a work in progress, not everything is properly implemented yet
	 *
	 * @since 2.0.5
	 * @author jkudish
	 */
	class TribeSettingsTab {

		public $id;
		public $name;
		public $args;
		public static $defaults;

		public function __construct($id, $name, $args = array()) {

			// seetup the defaults
			$this->defaults = array(
				'fields' => array(),
				'placement' => null,
				'show_save' => true,
				'display_callback' => false,
			);

			// parse args with defaults and extract them
			$args = wp_parse_args($args, $this->defaults);
			extract($args);

			// set each instance variable and filter
			$this->id = apply_filters( 'tribe_settings_tab_id', $id );
			$this->name = apply_filters( 'tribe_settings_tab_name', $name );
			foreach ($this->defaults as $key => $value) {
				$this->{$key} = apply_filters( 'tribe_settings_tab_'.$key, $$key );
			}


			// run actions & filters
			add_filter('tribe_settings_tabs', array($this, 'addTab') );
			add_filter('tribe_settings_no_save_tabs', array($this, 'showSaveTab') );
			add_filter('tribe_settings_content_tab_'.$this->id, array($this, 'doContent') );

		}

		public function addTab($tabs) {
			$tabs[$this->id] = $this->name;
			return $tabs;
		}

		public function showSaveTab($noSaveTabs) {
			if ( !$this->show_save )
				$noSaveTabs[$this->id] = $this->id;
			return $noSaveTabs;
		}

		public function doContent() {

			if ( $this->display_callback && function_exists($this->display_callback) )
				call_user_func($this->display_callback);

			if (is_array($this->fields)) {
				foreach ($this->fields as $key => $field) {
					new TribeField($key, $field);
				}
			} else {
				echo '<p>'.__('There are no fields setup for this tab yet.', 'tribe-events-calendar').'</p>';
			}

		}


	} // end class

} // endif class_exists