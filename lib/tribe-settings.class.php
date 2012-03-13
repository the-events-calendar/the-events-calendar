<?php

// Don't load directly
if ( !defined('ABSPATH') ) die('-1');

if ( !class_exists('TribeSettings') ) {

	/**
	 * helper class that allows registration of settings
	 * note: this is a work in progress
	 *
	 * @since 2.0.5
	 * @author jkudish
	 */
	class TribeSettings {

		public static $instance;
		public static $admin_page;
		public static $tabs;
		public static $fields;
		public static $defaultTab;
		public static $currentTab;
		public static $noSaveTabs;
		public static $adminSlug;
		public static $menuName;
		public static $requiredCap;
		public static $errors;
		public static $validated;

		/* Static Singleton Factory Method */
		public static function instance() {
			if (!isset(self::$instance)) {
				$className = __CLASS__;
				self::$instance = new $className;
			}
			return self::$instance;
		}

		public function __construct() {

			// set instance variables
			$this->tabs = (array) apply_filters( 'tribe_settings_tabs', array() );
			$this->fields = (array) apply_filters( 'tribe_settings_fields', array() );
			$this->defaultTab = apply_filters( 'tribe_settings_default_tab', 'general' );
			$this->currentTab = apply_filters( 'tribe_settings_current_tab', ( isset($_GET['tab']) && $_GET['tab'] ) ? esc_attr($_GET['tab']) : $this->defaultTab );
			$this->noSaveTabs = (array) apply_filters( 'tribe_settings_no_save_tabs', array() );
			$this->adminSlug = apply_filters( 'tribe_settings_admin_slug', 'tribe-settings' );
			$this->menuName = apply_filters( 'tribe_settings_menu_name', __('The Events Calendar', 'tribe-events-calendar') );
			$this->requiredCap = apply_filters( 'tribe_settings_req_cap', 'manage_options' );
			$this->errors = (array) apply_filters( 'tribe_settings_errors', array() );

			// run actions & filters
			add_action( 'admin_menu', array( $this, 'addPage' ) );
			add_action( 'tribe_settings_top', array( $this, 'validate' ) );
			// add_action( 'tribe_settings_below_tabs', array( $this, 'displayErrors' ) );
			// add_action( 'tribe_settings_below_tabs', array( $this, 'displaySuccess' ) );
		}

		/**
		 * create the main option page
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return void
		 */
		public function addPage() {
			$this->admin_page = add_options_page( $this->menuName, $this->menuName, $this->requiredCap, $this->adminSlug, array(&$this, 'generatePage') );
		}


		/**
		 * generate the main option page
		 * includes the view file
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return void
		 */
		public function generatePage() {
			$tec = TribeEvents::instance();
			do_action( 'tribe_settings_top' );
			echo '<div class="tribe_settings wrap">';
				screen_icon();
				echo '<h2>';
					printf( _x('%s Settings', 'The Event Calendar settings heading', 'tribe-events-calendar'), $this->menuName );
				echo '</h2>';
				$this->generateTabs( $this->currentTab );
				do_action( 'tribe_settings_below_tabs' );
				do_action( 'tribe_settings_below_tabs_tab_'.$this->currentTab );
				echo '<div class="tribe-settings-form form">';
					do_action( 'tribe_settings_above_form_element' );
					do_action( 'tribe_settings_above_form_element_tab_'.$this->currentTab );
					echo apply_filters( 'tribe_settings_form_element', '<form method="post">' );
						do_action( 'tribe_settings_before_content' );
						do_action( 'tribe_settings_before_content_tab_'.$this->currentTab );
						do_action( 'tribe_settings_content_tab_'.$this->currentTab );
						if ( !has_action( 'tribe_settings_content_tab_'.$this->currentTab ) ) {
							echo '<p>'.__('You\'ve requested a non-existent tab.', 'tribe-events-calendar').'</p>';
						}
						do_action( 'tribe_settings_after_content_tab_'.$this->currentTab );
			 			do_action( 'tribe_settings_after_content' );
			  		if ( has_action('tribe_settings_content_tab_'.$this->currentTab) && !in_array($this->currentTab, $this->noSaveTabs) ) {
							wp_nonce_field('saveTribeOptionsNonce');
		    			echo '<input type="hidden" name="current-settings-tab" id="current-settings-tab" value="'.$this->currentTab.'" />';
		    			echo '<input id="saveTribeOptions" class="button-primary" type="submit" name="saveTribeOptions" value="'.__('Save Changes', 'tribe-events-calendar').'" />';
						}
					echo apply_filters( 'tribe_settings_closing_form_element', ' </form>' );
					do_action( 'tribe_settings_after_form_element' );
				echo '</div>';
				do_action( 'tribe_settings_after_form_div' );
			echo '</div>';
			do_action( 'tribe_settings_bottom' );
		}

		/**
		 * generate the tabs in the settings screen
		 *
		 * @since 2.0.5
		 * @author PaulHughes01, jkudish
		 * @return void
		 */
		public function generateTabs() {
			if ( is_array($this->tabs) && !empty($this->tabs) ) {
				echo '<h2 id="tribe-settings-tabs" class="nav-tab-wrapper">';
					foreach ($this->tabs as $tab => $name ) {
						$tab = esc_attr($tab);
						$name = esc_attr($name);
						$class = ( $tab == $this->currentTab ) ? ' nav-tab-active' : '';
						echo '<a id="'.$tab.'" class="nav-tab'.$class.'" href="?page=tribe-settings&tab='.urlencode($tab).'">'.$name.'</a>';
					}
					do_action( 'tribe_settings_after_tabs' );
				echo '</h2>';
			}
		 }


		/**
		 * validate the settings
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return void
		 */
		public function validate() {
			do_action('tribe_validate_settings');
			do_action('tribe_validate_settings_tab_'.$this->currentTab);
			if ( isset($_POST['saveTribeOptions']) && isset($_POST['current-settings-tab']) && $_POST['current-settings-tab'] == $this->currentTab ) {
				$tab = $this->currentTab;
				$fields = $this->fields[$tab];
				if (is_array($fields)) {
					foreach ($fields as $field_id => $field) {
						$value = ( isset($_POST[$field_id]) ) ? $_POST[$field_id] : null;
						if ( isset($field['validation_type']) || isset($field['validation_callback']) ) {
							$validate = new TribeValidate($field_id, $field, $value);
							if (isset($validate->result->error)) {
								$this->errors[$field->id] = $validate->error;
							} elseif ( $validate->result->valid ) {
								$this->validated[$field_id] = $field_id;
							}
						}
					}
					$this->save();
				}
			}
		}

		/**
		 * save the settings
		 * note: this will be refactored
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return void
		 */
		public function save() {
			do_action('tribe_validate_form_settings');
		}

		/**
		 * display errors after saving
		 *
		 * @since 2.0.5
		 * @author PaulHughes01, jkudish
		 * @return void
		 */
		public function displayErrors() {
			$errors = (array) $this->errors;
			$count = apply_filters( 'tribe_settings_count_errors', count( $errors ) );
			if ( $count ) {
				$output = '<div id="message" class="error"><p><strong>';
				$output = __('Your form had the following errors:', 'tribe-events-calendar');
				$output = '</strong></p><ul>';
				foreach ($errors as $error) {
					$output ='<li>'.esc_attr($error).'</li>';
				}
				$message = _n('The above setting was not saved.', 'The above settings were not saved.', $count, 'tribe-events-calendar');
				$output = '</ul><p>'.$message.'</p></div>';
				echo apply_filters( 'tribe_settings_error_message', $output );
			}
		}

		/**
		 * display errors after saving
		 *
		 * @since 2.0.5
		 * @author PaulHughes01, jkudish
		 * @return void
		 */
		public function displaySuccess() {
			if ( isset($_POST['saveTribeOptions']) && check_admin_referer('saveTribeOptions') ) {
				if ( !count( $this->errors ) ) {
					$message = __('Settings saved.', 'tribe-events-calendar');
					$output = '<div id="message" class="updated"><p><strong>' . $message . '</strong></p></div>';
					echo apply_filters( 'tribe_settings_success_message', $output );
				}
			}
		}


	} // end class

} // endif class_exists