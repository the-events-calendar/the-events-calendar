<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Field' ) ) {

	/**
	 * helper class that creates fields for use in Settings, MetaBoxes, Users, anywhere.
	 * Instantiate it whenever you need a field
	 *
	 */
	class Tribe__Events__Field {

		/**
		 * the field's id
		 * @var string
		 */
		public $id;

		/**
		 * the field's name (also known as it's label)
		 * @var string
		 */
		public $name;

		/**
		 * the field's attributes
		 * @var array
		 */
		public $attributes;

		/**
		 * the field's arguments
		 * @var array
		 */
		public $args;

		/**
		 * field defaults (static)
		 * @var array
		 */
		public $defaults;

		/**
		 * valid field types (static)
		 * @var array
		 */
		public $valid_field_types;


		/**
		 * Class constructor
		 *
		 * @param string     $id    the field id
		 * @param array      $field the field settings
		 * @param null|mixed $value the field's current value
		 *
		 * @return void
		 */
		public function __construct( $id, $field, $value = null ) {

			// setup the defaults
			$this->defaults = array(
				'type'             => 'html',
				'name'             => $id,
				'attributes'       => array(),
				'class'            => null,
				'label'            => null,
				'tooltip'          => null,
				'size'             => 'medium',
				'html'             => null,
				'error'            => false,
				'value'            => $value,
				'options'          => null,
				'conditional'      => true,
				'display_callback' => null,
				'if_empty'         => null,
				'can_be_empty'     => false,
				'clear_after'      => true,
			);

			// a list of valid field types, to prevent screwy behaviour
			$this->valid_field_types = array(
				'heading',
				'html',
				'text',
				'textarea',
				'wysiwyg',
				'radio',
				'checkbox_bool',
				'checkbox_list',
				'dropdown',
				'dropdown_chosen',
				'dropdown_select2',
				'license_key',
			);

			$this->valid_field_types = apply_filters( 'tribe_valid_field_types', $this->valid_field_types );

			// parse args with defaults and extract them
			$args = wp_parse_args( $field, $this->defaults );

			// sanitize the values just to be safe
			$id         = esc_attr( $id );
			$type       = esc_attr( $args['type'] );
			$name       = esc_attr( $args['name'] );
			$class      = sanitize_html_class( $args['class'] );
			$label      = wp_kses(
				$args['label'], array(
					'a'      => array( 'href' => array(), 'title' => array() ),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'b'      => array(),
					'i'      => array(),
					'u'      => array(),
					'img'    => array(
						'title' => array(),
						'src'   => array(),
						'alt'   => array()
					)
				)
			);
			$tooltip    = wp_kses(
				$args['tooltip'], array(
					'a'      => array( 'href' => array(), 'title' => array(), 'target' => array() ),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'b'      => array(),
					'i'      => array(),
					'u'      => array(),
					'img'    => array(
						'title' => array(),
						'src'   => array(),
						'alt'   => array()
					),
					'code'   => array( 'span' => array() ),
					'span'   => array()
				)
			);
			$attributes = $args['attributes'];
			if ( is_array( $attributes ) ) {
				foreach ( $attributes as $key => &$val ) {
					$val = esc_attr( $val );
				}
			}
			if ( is_array( $args['options'] ) ) {
				$options = array();
				foreach ( $args['options'] as $key => $val ) {
					$options[$key] = $val;
				}
			} else {
				$options = $args['options'];
			}
			$size             = esc_attr( $args['size'] );
			$html             = $args['html'];
			$error            = (bool) $args['error'];
			$value            = is_array( $value ) ? array_map( 'esc_attr', $value ) : esc_attr( $value );
			$conditional      = $args['conditional'];
			$display_callback = $args['display_callback'];
			$if_empty         = (bool) $args['if_empty'];
			$can_be_empty     = (bool) $args['can_be_empty'];
			$clear_after      = (bool) $args['clear_after'];


			// set the ID
			$this->id = apply_filters( 'tribe_field_id', $id );

			// set each instance variable and filter
			foreach ( $this->defaults as $key => $value ) {
				$this->{$key} = apply_filters( 'tribe_field_' . $key, $$key, $this->id );
			}

			// epicness
			$this->doField();

		}

		/**
		 * Determines how to handle this field's creation
		 * either calls a callback function or runs this class' course of action
		 * logs an error if it fails
		 *
		 * @return void
		 */
		public function doField() {

			if ( $this->conditional ) {

				if ( $this->display_callback && is_callable( $this->display_callback ) ) {

					// if there's a callback, run it
					call_user_func( $this->display_callback );

				} elseif ( in_array( $this->type, $this->valid_field_types ) ) {

					// the specified type exists, run the appropriate method
					$field = call_user_func( array( $this, $this->type ) );

					// filter the output
					$field = apply_filters( 'tribe_field_output_' . $this->type, $field, $this->id, $this );
					echo apply_filters( 'tribe_field_output_' . $this->type . '_' . $this->id, $field, $this->id, $this );

				} else {

					// fail, log the error
					Tribe__Events__Main::debug( __( 'Invalid field type specified', 'tribe-events-calendar' ), $this->type, 'notice' );

				}

			}
		}

		/**
		 * returns the field's start
		 *
		 * @return string the field start
		 */
		public function doFieldStart() {
			$return = '<fieldset id="tribe-field-' . $this->id . '"';
			$return .= ' class="tribe-field tribe-field-' . $this->type;
			$return .= ( $this->error ) ? ' tribe-error' : '';
			$return .= ( $this->size ) ? ' tribe-size-' . $this->size : '';
			$return .= ( $this->class ) ? ' ' . $this->class . '"' : '"';
			$return .= '>';

			return apply_filters( 'tribe_field_start', $return, $this->id, $this->type, $this->error, $this->class, $this );
		}

		/**
		 * returns the field's end
		 *
		 * @return string the field end
		 */
		public function doFieldEnd() {
			$return = '</fieldset>';
			$return .= ( $this->clear_after ) ? '<div class="clear"></div>' : '';

			return apply_filters( 'tribe_field_end', $return, $this->id, $this );
		}

		/**
		 * returns the field's label
		 *
		 * @return string the field label
		 */
		public function doFieldLabel() {
			$return = '';
			if ( $this->label ) {
				$return = '<legend class="tribe-field-label">' . $this->label . '</legend>';
			}

			return apply_filters( 'tribe_field_label', $return, $this->label, $this );
		}

		/**
		 * returns the field's div start
		 *
		 * @return string the field div start
		 */
		public function doFieldDivStart() {
			$return = '<div class="tribe-field-wrap">';

			return apply_filters( 'tribe_field_div_start', $return, $this );
		}

		/**
		 * returns the field's div end
		 *
		 * @return string the field div end
		 */
		public function doFieldDivEnd() {
			$return = $this->doToolTip();
			$return .= '</div>';

			return apply_filters( 'tribe_field_div_end', $return, $this );
		}

		/**
		 * returns the field's tooltip/description
		 *
		 * @return string the field tooltip
		 */
		public function doToolTip() {
			$return = '';
			if ( $this->tooltip ) {
				$return = '<p class="tooltip description">' . $this->tooltip . '</p>';
			}

			return apply_filters( 'tribe_field_tooltip', $return, $this->tooltip, $this );
		}

		/**
		 * returns the screen reader label
		 *
		 * @return string the screen reader label
		 */
		public function doScreenReaderLabel() {
			$return = '';
			if ( $this->tooltip ) {
				$return = '<label class="screen-reader-text">' . $this->tooltip . '</label>';
			}

			return apply_filters( 'tribe_field_screen_reader_label', $return, $this->tooltip, $this );
		}

		/**
		 * returns the field's value
		 *
		 * @return string the field value
		 */
		public function doFieldValue() {
			$return = '';
			if ( $this->value ) {
				$return = ' value="' . $this->value . '"';
			}

			return apply_filters( 'tribe_field_value', $return, $this->value, $this );
		}

		/**
		 * returns the field's name
		 *
		 * @param bool $multi
		 *
		 * @return string the field name
		 */
		public function doFieldName( $multi = false ) {
			$return = '';
			if ( $this->name ) {
				if ( $multi ) {
					$return = ' name="' . $this->name . '[]"';
				} else {
					$return = ' name="' . $this->name . '"';
				}
			}

			return apply_filters( 'tribe_field_name', $return, $this->name, $this );
		}

		/**
		 * Return a string of attributes for the field
		 *
		 * @return string
		 **/
		public function doFieldAttributes() {
			$return = '';
			if ( ! empty( $this->attributes ) ) {
				foreach ( $this->attributes as $key => $value ) {
					$return .= ' ' . $key . '="' . $value . '"';
				}
			}

			return apply_filters( 'tribe_field_attributes', $return, $this->name, $this );
		}

		/**
		 * generate a heading field
		 *
		 * @return string the field
		 */
		public function heading() {
			$field = '<h3>' . $this->label . '</h3>';

			return $field;
		}

		/**
		 * generate an html field
		 *
		 * @return string the field
		 */
		public function html() {
			$field = $this->doFieldLabel();
			$field .= $this->html;

			return $field;
		}


		/**
		 * generate a simple text field
		 *
		 * @return string the field
		 */
		public function text() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			$field .= '<input';
			$field .= ' type="text"';
			$field .= $this->doFieldName();
			$field .= $this->doFieldValue();
			$field .= '/>';
			$field .= $this->doScreenReaderLabel();
			$field .= $this->doFieldDivEnd();
			$field .= $this->doFieldEnd();

			return $field;
		}

		/**
		 * generate a textarea field
		 *
		 * @return string the field
		 */
		public function textarea() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			$field .= '<textarea';
			$field .= $this->doFieldName();
			$field .= '>';
			$field .= esc_html( stripslashes( $this->value ) );
			$field .= '</textarea>';
			$field .= $this->doScreenReaderLabel();
			$field .= $this->doFieldDivEnd();
			$field .= $this->doFieldEnd();

			return $field;
		}

		/**
		 * generate a wp_editor field
		 *
		 * @return string the field
		 */
		public function wysiwyg() {
			$settings = array(
				'teeny'   => true,
				'wpautop' => true
			);
			ob_start();
			wp_editor( html_entity_decode( ( $this->value ) ), $this->name, $settings );
			$editor = ob_get_clean();
			$field  = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			$field .= $editor;
			$field .= $this->doScreenReaderLabel();
			$field .= $this->doFieldDivEnd();
			$field .= $this->doFieldEnd();

			return $field;
		}

		/**
		 * generate a radio button field
		 *
		 * @return string the field
		 */
		public function radio() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			if ( is_array( $this->options ) ) {
				foreach ( $this->options as $option_id => $title ) {
					$field .= '<label title="' . esc_attr( $title ) . '">';
					$field .= '<input type="radio"';
					$field .= $this->doFieldName();
					$field .= ' value="' . esc_attr( $option_id ) . '" ' . checked( $this->value, $option_id, false ) . '/>';
					$field .= $title;
					$field .= '</label>';
				}
			} else {
				$field .= '<span class="tribe-error">' . __( 'No radio options specified', 'tribe-events-calendar' ) . '</span>';
			}
			$field .= $this->doFieldDivEnd();
			$field .= $this->doFieldEnd();

			return $field;
		}

		/**
		 * generate a checkbox_list field
		 *
		 * @return string the field
		 */
		public function checkbox_list() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();

			if ( ! is_array( $this->value ) ) {
				if ( ! empty( $this->value ) ) {
					$this->value = array( $this->value );
				} else {
					$this->value = array();
				}
			}

			if ( is_array( $this->options ) ) {
				foreach ( $this->options as $option_id => $title ) {
					$field .= '<label title="' . esc_attr( $title ) . '">';
					$field .= '<input type="checkbox"';
					$field .= $this->doFieldName( true );
					$field .= ' value="' . esc_attr( $option_id ) . '" ' . checked( in_array( $option_id, $this->value ), true, false ) . '/>';
					$field .= $title;
					$field .= '</label>';
				}
			} else {
				$field .= '<span class="tribe-error">' . __( 'No checkbox options specified', 'tribe-events-calendar' ) . '</span>';
			}
			$field .= $this->doFieldDivEnd();
			$field .= $this->doFieldEnd();

			return $field;
		}

		/**
		 * generate a boolean checkbox field
		 *
		 * @return string the field
		 */
		public function checkbox_bool() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			$field .= '<input type="checkbox"';
			$field .= $this->doFieldName();
			$field .= ' value="1" ' . checked( $this->value, true, false );
			$field .= $this->doFieldAttributes();
			$field .= '/>';
			$field .= $this->doScreenReaderLabel();
			$field .= $this->doFieldDivEnd();
			$field .= $this->doFieldEnd();

			return $field;
		}

		/**
		 * generate a dropdown field
		 *
		 * @return string the field
		 */
		public function dropdown() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			if ( is_array( $this->options ) && ! empty( $this->options ) ) {
				$field .= '<select';
				$field .= $this->doFieldName();
				$field .= '>';
				foreach ( $this->options as $option_id => $title ) {
					$field .= '<option value="' . esc_attr( $option_id ) . '"';
					if ( is_array( $this->value ) ) {
						$field .= isset( $this->value[0] ) ? selected( $this->value[0], $option_id, false ) : '';
					} else {
						$field .= selected( $this->value, $option_id, false );
					}
					$field .= '>' . esc_html( $title ) . '</option>';
				}
				$field .= '</select>';
				$field .= $this->doScreenReaderLabel();
			} elseif ( $this->if_empty ) {
				$field .= '<span class="empty-field">' . (string) $this->if_empty . '</span>';
			} else {
				$field .= '<span class="tribe-error">' . __( 'No select options specified', 'tribe-events-calendar' ) . '</span>';
			}
			$field .= $this->doFieldDivEnd();
			$field .= $this->doFieldEnd();

			return $field;
		}

		/**
		 * generate a chosen dropdown field - the same as the
		 * regular dropdown but wrapped so it can have the
		 * right css class applied to it
		 *
		 * @return string the field
		 */
		public function dropdown_chosen() {
			$field = $this->dropdown();

			return $field;
		}

		/**
		 * generate a select2 dropdown field - the same as the
		 * regular dropdown but wrapped so it can have the
		 * right css class applied to it
		 *
		 * @return string the field
		 */
		public function dropdown_select2() {
			$field = $this->dropdown();

			return $field;
		}

		/**
		 * generate a license key field
		 *
		 * @return string the field
		 */
		public function license_key() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			$field .= '<input';
			$field .= ' type="text"';
			$field .= $this->doFieldName();
			$field .= $this->doFieldValue();
			$field .= '/>';
			$field .= '<p class="license-test-results"><img src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" class="ajax-loading-license" alt="Loading" style="display: none"/>';
			$field .= '<span class="valid-key"></span>';
			$field .= '<span class="invalid-key"></span></p>';
			$field .= $this->doScreenReaderLabel();
			$field .= $this->doFieldDivEnd();
			$field .= $this->doFieldEnd();

			return $field;
		}

	} // end class

} // endif class_exists
