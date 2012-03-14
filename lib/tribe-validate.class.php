<?php

// Don't load directly
if ( !defined('ABSPATH') ) die('-1');

if ( !class_exists('TribeValidate') ) {

	/**
	 * helper class that validates fields for use in Settings, MetaBoxes, Users, anywhere...
	 *
	 * @since 2.0.5
	 * @author jkudish
	 */
	class TribeValidate {

		public $field;
		public $value;
		public $label;
		public $error;
		public $type;
		public $callback;
		public $result;
		protected $valid_types;

		/**
		 * Class constructor
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @param string $field_id the field ID to validate
		 * @param array $field_id the field object to validate
		 * @param mixed $value the value to validate
		 * @return array $result the result of the validation
		 */
		public function __construct($field_id, $field, $value) {

			// prepare object properties
			$this->result = new stdClass;
			$this->field = $field;
			$this->field['id'] = $field_id;
			$this->value = $value;

			// if the field is invalid or incomplete, fail validation
			if ( !is_array($this->field) || ( !isset($this->field['validation_type']) && !isset($this->field['validation_callback']) ) ) {
				$this->result->valid = false;
				$this->result->error = __('Invalid or incomplete field passed', 'tribe-events-calendar');
				$this->result->error .= (isset($this->field['id'])) ? ' ('.__('Field ID:', 'tribe-events-calendar').' '.$this->field['id'].' )' : '';
				return $this->result;
			}

			// call validation callback if a validation callback function is set
			if ( isset($this->field['validation_callback']) ) {
				if ( function_exists($this->field['validation_callback']) ) {
					return call_user_func($validation_callback);
				}
			}


			if ( isset($this->field['validation_type']) ) {

				if ( method_exists( $this, $this->field['validation_type'] ) ) {

					// make sure there's a field validation type set for this validation and that such method exists
					$this->type = $this->field['validation_type'];
					$this->label = isset($this->field['label']) ? $this->field['label'] : $this->field['id'];
					call_user_method($this->type, $this); // run the validation

				} else {

					// invalid validation type set, validation fails
					$this->result->valid = false;
					$this->result->error = __('Non-existant field validation function passed', 'tribe-events-calendar');
					$this->result->error .= (isset($this->field['id'])) ? ' ('.__('Field ID:', 'tribe-events-calendar').' '.$this->field['id'].' '._x('with function name:', 'non-existant function name passed for field validation', 'tribe-events-calendar' ).' '.$this->field['validation_type'].' )' : '';
				}
			} else {

				// no validation type set, validation fails
				$this->result->valid = false;
				$this->result->error = __('Invalid or incomplete field passed', 'tribe-events-calendar');
				$this->result->error .= (isset($this->field['id'])) ? ' ('.__('Field ID:', 'tribe-events-calendar').' '.$this->field['id'].' )' : '';
			}

			// return the result
			return $this->result;
		}

		/**
		 * validates & sanitizes fields as being positive integers
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return stdClass validation result object
		 */
		public function positive_int() {
			if ( preg_match( '/^[0-9]+$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __('%s must be a positive integer.', 'tribe-events-calendar'), $this->label);
			}
		}

		/**
		 * validates & sanitizes fields as URL slugs
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return stdClass validation result object
		 */
		public function slug() {

		}

		/**
		 * validates & sanitizes fields as not being able to be the same
		 * as the specified field
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return stdClass validation result object
		 */
		public function cannot_be_the_same_as() {

		}

		/**
		 * validates & sanitizes fields as being a number or a percentage
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return stdClass validation result object
		 */
		public function number_or_percent() {

		}

		/**
		 * validates & sanitizes fields as being a number in between
		 * two specified numbers
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return stdClass validation result object
		 */
		public function number_between() {

		}

		/**
		 * validates & sanitizes fields as beeing a boolean
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return stdClass validation result object
		 */
		public function boolean() {

		}

		/**
		 * validates & sanitizes fields as being part of an address
		 * allows for letters, numbers, dashses and spaces only
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return stdClass validation result object
		 */
		public function address() {

		}

		/**
		 * validates & sanitizes fields as being a city or province
		 * allows for letters, dashses and spaces only
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return stdClass validation result object
		 */
		public function city_or_province() {

		}

		/**
		 * validates & sanitizes fields as being a zip code
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return stdClass validation result object
		 */
		public function zip() {

		}

		/**
		 * validates & sanitizes fields as being a phone number
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return stdClass validation result object
		 */
		public function phone() {

		}

		/**
		 * automatically validate a field regardless of the value
		 * Don't use this unless you know what you are doing
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return stdClass validation result object
		 */
		public function none() {

		}

	} // end class

} // endif class_exists