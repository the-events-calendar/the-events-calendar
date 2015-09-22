<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Validate' ) ) {
	/**
	 * helper class that validates fields for use in Settings, MetaBoxes, Users, anywhere.
	 * Instantiate whenever you want to validate a field
	 *
	 */
	class Tribe__Events__Validate {

		/**
		 * the field object to validate
		 * @var array
		 */
		public $field;

		/**
		 * the field's value
		 * @var mixed
		 */
		public $value;

		/**
		 * additional arguments for validation
		 * used by some methods only
		 * @var array
		 */
		public $additional_args;


		/**
		 * the field's label, used in error messages
		 * @var string
		 */
		public $label;

		/**
		 * the type of validation to perform
		 * @var string
		 */
		public $type;


		/**
		 * the result object of the validation
		 * @var stdClass
		 */
		public $result;

		/**
		 * Class constructor
		 *
		 * @param string $field_id the field ID to validate
		 * @param array  $field_id the field object to validate
		 * @param mixed  $value    the value to validate
		 *
		 * @return array $result the result of the validation
		 */
		public function __construct( $field_id, $field, $value, $additional_args = array() ) {

			// prepare object properties
			$this->result          = new stdClass;
			$this->field           = $field;
			$this->field['id']     = $field_id;
			$this->value           = $value;
			$this->additional_args = $additional_args;

			// if the field is invalid or incomplete, fail validation
			if ( ! is_array( $this->field ) || ( ! isset( $this->field['validation_type'] ) && ! isset( $this->field['validation_callback'] ) ) ) {
				$this->result->valid = false;
				$this->result->error = __( 'Invalid or incomplete field passed', 'the-events-calendar' );
				$this->result->error .= ( isset( $this->field['id'] ) ) ? ' (' . __( 'Field ID:', 'the-events-calendar' ) . ' ' . $this->field['id'] . ' )' : '';

				return $this->result;
			}

			// call validation callback if a validation callback function is set
			if ( isset( $this->field['validation_callback'] ) ) {
				if ( function_exists( $this->field['validation_callback'] ) ) {
					if ( ( ! isset( $_POST[ $field_id ] ) || ! $_POST[ $field_id ] || $_POST[ $field_id ] == '' ) && isset( $this->field['can_be_empty'] ) && $this->field['can_be_empty'] ) {
						$this->result->valid = true;

						return $this->result;
					} else {
						return call_user_func( $validation_callback );
					}
				}
			}


			if ( isset( $this->field['validation_type'] ) ) {
				if ( method_exists( $this, $this->field['validation_type'] ) ) {
					// make sure there's a field validation type set for this validation and that such method exists
					$this->type  = $this->field['validation_type'];
					$this->label = isset( $this->field['label'] ) ? $this->field['label'] : $this->field['id'];
					if ( ( ! isset( $_POST[ $field_id ] ) || ! $_POST[ $field_id ] || $_POST[ $field_id ] == '' ) && isset( $this->field['can_be_empty'] ) && $this->field['can_be_empty'] ) {
						$this->result->valid = true;

						return $this->result;
					} else {
						call_user_func( array( $this, $this->type ) ); // run the validation
					}
				} else {
					// invalid validation type set, validation fails
					$this->result->valid = false;
					$this->result->error = __( 'Non-existant field validation function passed', 'the-events-calendar' );
					$this->result->error .= ( isset( $this->field['id'] ) ) ? ' (' . __( 'Field ID:', 'the-events-calendar' ) . ' ' . $this->field['id'] . ' ' . _x( 'with function name:', 'non-existant function name passed for field validation', 'the-events-calendar' ) . ' ' . $this->field['validation_type'] . ' )' : '';
				}
			} else {
				// no validation type set, validation fails
				$this->result->valid = false;
				$this->result->error = __( 'Invalid or incomplete field passed', 'the-events-calendar' );
				$this->result->error .= ( isset( $this->field['id'] ) ) ? ' (' . __( 'Field ID:', 'the-events-calendar' ) . ' ' . $this->field['id'] . ' )' : '';
			}

			// return the result
			return $this->result;
		}

		/**
		 * validates a field as a string containing only letters and numbers
		 *
		 * @return stdClass validation result object
		 */
		public function alpha_numeric() {
			if ( preg_match( '/^[a-zA-Z0-9]+$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must contain numbers and letters only', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates a field as a string containing only letters,
		 * numbers and carriage returns
		 *
		 * @return stdClass validation result object
		 */
		public function alpha_numeric_multi_line() {
			if ( preg_match( '/^[a-zA-Z0-9\s]+$/', $this->value ) ) {
				$this->result->valid = true;
				$this->value         = tribe_multi_line_remove_empty_lines( $this->value );
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must contain numbers and letters only', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates a field as a string containing only letters,
		 * numbers, dots and carriage returns
		 *
		 * @return stdClass validation result object
		 */
		public function alpha_numeric_multi_line_with_dots_and_dashes() {
			if ( preg_match( '/^[a-zA-Z0-9\s.-]+$/', $this->value ) ) {
				$this->result->valid = true;
				$this->value         = tribe_multi_line_remove_empty_lines( $this->value );
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must contain numbers, letters and dots only', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates a field as being positive integers
		 *
		 * @return stdClass validation result object
		 */
		public function positive_int() {
			if ( preg_match( '/^[0-9]+$/', $this->value ) && $this->value > 0 ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must be a positive number.', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates & sanitizes fields as URL slugs
		 *
		 * @return stdClass validation result object
		 */
		public function slug() {
			if ( preg_match( '/^[a-zA-Z0-9-_]+$/', $this->value ) ) {
				$this->result->valid = true;
				$this->value         = sanitize_title( $this->value );
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must be a valid slug (numbers, letters, dashes, and underscores).', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates & sanitizes fields as URLs
		 *
		 * @return stdClass validation result object
		 */
		public function url() {

			if ( esc_url_raw( $this->value ) == $this->value ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must be a valid absolute URL.', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates fields that have options (radios, dropdowns, etc.)
		 * by making sure the value is part of the options array
		 *
		 * @return stdClass validation result object
		 */
		public function options() {
			if ( array_key_exists( $this->value, $this->field['options'] ) ) {
				$this->value         = ( $this->value === 0 ) ? false : $this->value;
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( "%s must have a value that's part of its options.", 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates fields that have multiple options (checkbox list, etc.)
		 * by making sure the value is part of the options array
		 *
		 * @return stdClass validation result object
		 */
		public function options_multi() {
			foreach ( $this->value as $val ) {
				if ( array_key_exists( $val, $this->field['options'] ) ) {
					$this->value         = ( $this->value === 0 ) ? false : $this->value;
					$this->result->valid = true;
				} else {
					$this->result->valid = false;
					$this->result->error = sprintf( __( "%s must have a value that's part of its options.", 'the-events-calendar' ), $this->label );
				}
			}
		}

		/**
		 * validates fields that have options (radios, dropdowns, etc.)
		 * by making sure the value is part of the options array
		 * then combines the value into an array containg the value
		 * and name from the option
		 *
		 * @return stdClass validation result object
		 */
		public function options_with_label() {
			if ( array_key_exists( $this->value, $this->field['options'] ) ) {
				$this->value         = ( $this->value === 0 ) ? false : array(
					$this->value,
					$this->field['options'][ $this->value ],
				);
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( "%s must have a value that's part of its options.", 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates a field as not being able to be the same
		 * as the specified value as specified in
		 * $this->additional_args['compare_name']
		 *
		 * @return stdClass validation result object
		 */
		public function cannot_be_the_same_as() {
			if ( ! isset( $this->additional_args['compare'] ) ) {
				$this->result->valid = false;
				$this->result->error = sprintf( __( 'Comparison validation failed because no comparison value was provided, for field %s', 'the-events-calendar' ), $this->field['id'] );
			} else {
				if ( $this->value != $this->additional_args['compare'] ) {
					$this->result = true;
				} else {
					$this->result->valid = false;
					if ( isset( $this->additional_args['compare_name'] ) ) {
						$this->result->error = sprintf( __( '%s cannot be the same as %s.', 'the-events-calendar' ), $this->label, $this->additional_args['compare_name'] );
					} else {
						$this->result->error = sprintf( __( '%s cannot be a duplicate', 'the-events-calendar' ), $this->label );
					}
				}
			}
		}

		/**
		 * validates a field as being a number or a percentage
		 *
		 * @return stdClass validation result object
		 */
		public function number_or_percent() {
			if ( preg_match( '/^[0-9]+%{0,1}$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must be a number or percentage.', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * sanitizes an html field
		 *
		 * @return stdClass validation result object
		 */
		public function html() {
			$this->value         = balanceTags( $this->value );
			$this->result->valid = true;
		}

		/**
		 * sanitizes a license key
		 *
		 * @return stdClass validation result object
		 */
		public function license_key() {
			$this->value         = trim( $this->value );
			$this->result->valid = true;
		}

		/**
		 * sanitizes a textarea field
		 *
		 * @return stdClass validation result object
		 */
		public function textarea() {
			$this->value         = wp_kses( $this->value, array() );
			$this->result->valid = true;
		}

		/**
		 * sanitizes a field as beeing a boolean
		 *
		 * @return stdClass validation result object
		 */
		public function boolean() {
			$this->value         = (bool) $this->value;
			$this->result->valid = true;
		}

		/**
		 * validates a Google Maps Zoom field
		 *
		 * @return stdClass validation result object
		 */
		public function google_maps_zoom() {
			if ( preg_match( '/^([0-9]|[0-1][0-9]|2[0-1])$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must be a number between 0 and 21.', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates a field as being part of an address
		 * allows for letters, numbers, dashses and spaces only
		 *
		 * @return stdClass validation result object
		 */
		public function address() {
			$this->value = stripslashes( $this->value );
			if ( preg_match( "/^[0-9\S '-]+$/", $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must consist of letters, numbers, dashes, apostrophes, and spaces only.', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates a field as being a city or province
		 * allows for letters, dashses and spaces only
		 *
		 * @return stdClass validation result object
		 */
		public function city_or_province() {
			$this->value = stripslashes( $this->value );
			if ( preg_match( "/^[\D '\-]+$/", $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must consist of letters, spaces, apostrophes, and dashes.', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates a field as being a zip code
		 *
		 * @return stdClass validation result object
		 */
		public function zip() {
			if ( preg_match( '/^[0-9]{5}$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must consist of 5 numbers.', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates a field as being a phone number
		 *
		 * @return stdClass validation result object
		 */
		public function phone() {
			if ( preg_match( '/^[0-9\(\)\+ -]+$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( __( '%s must be a phone number.', 'the-events-calendar' ), $this->label );
			}
		}

		/**
		 * validates & sanitizes a field as being a country list
		 *
		 * @return stdClass validation result object
		 */
		public function country_list() {
			$country_rows = explode( "\n", $this->value );
			if ( is_array( $country_rows ) ) {
				foreach ( $country_rows as $crow ) {
					$country = explode( ',', $crow );
					if ( ! isset( $country[0] ) || ! isset( $country[1] ) ) {
						$this->result->valid = false;
						$this->result->error = sprintf( __( 'Country List must be formatted as one country per line in the following format: <br>US, United States <br> UK, United Kingdom.', 'the-events-calendar' ), $this->label );
						$this->value         = wp_kses( $this->value, array() );

						return;
					}
				}
			}
			$this->result->valid = true;
		}

		/**
		 * automatically validate a field regardless of the value
		 * Don't use this unless you know what you are doing
		 *
		 * @return stdClass validation result object
		 */
		public function none() {
			$this->result->valid = true;
		}

	} // end class
} // endif class_exists
