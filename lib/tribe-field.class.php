<?php

// Don't load directly
if ( !defined('ABSPATH') ) die('-1');

if ( !class_exists('TribeField') ) {

	/**
	 * helper class that creates fields for use in Settings, MetaBoxes, Users, anywhere.
	 * Instantiate it whenever you need a field
	 *
	 * @since 2.0.5
	 * @author jkudish
	 */
	class TribeField {

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
		 * the field's arguments
		 * @var array
		 */
		public $args;

		/**
		 * field defaults (static)
		 * @var array
		 */
		public static $defaults;

		/**
		 * valid field types (static)
		 * @var array
		 */
		public static $valid_field_types;


		/**
		 * Class constructor
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @param string $id the field id
		 * @param array $field the field settings
		 * @param mixed $value the field's current value
		 * @return void
		 */
		public function __construct($id, $field, $value = null) {

			// seetup the defaults
			$this->defaults = array(
				'type' => 'html',
				'name' => $id,
				'class' => null,
				'label' => null,
				'tooltip' => null,
				'size' => 'medium',
				'html' => null,
				'error' => false,
				'value' => $value,
				'options' => null,
				'conditional' => true,
				'display_callback' => null,
				'if_empty' => null,
			);

			// a list of valid field types, to prevent screwy behaviour
			$this->valid_field_types = array(
				'heading',
				'html',
				'text',
				'textarea',
				'radio',
				'checkbox_bool',
				'dropdown',
				'dropdown_chosen',
				'license_key',
			);

			apply_filters( 'tribe_valid_field_types', $this->valid_field_types );

			// parse args with defaults and extract them
			$args = wp_parse_args($field, $this->defaults);
			extract($args);

			// sanitize the values just to be safe
			$id = esc_attr($id);
			$type = esc_attr($type);
			$name = esc_attr($name);
			$class = sanitize_html_class($class);
			$label = esc_attr($label);
			$tooltip = esc_attr($tooltip);
			$size = esc_attr($size);
			$html = $html;
			$error = (bool) $error;
			$value = $value;
			$conditional = $conditional;
			$display_callback = esc_attr($display_callback);


			// set the ID
			$this->id = apply_filters( 'tribe_field_id', $id );

			// set each instance variable and filter
			foreach ($this->defaults as $key => $value) {
				$this->{$key} = apply_filters( 'tribe_field_'.$key, $$key, $this->id );
			}

			// epicness
			$this->doField();

		}

		/**
		 * Determines how to handle this field's creation
		 * either calls a callback function or runs this class' course of action
		 * logs an error if it fails
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @param string $id the field id
		 * @param array $field the field settings
		 * @return void
		 */
		public function doField() {

			if ($this->conditional) {

				if ( $this->display_callback && function_exists($this->display_callback) ) {

					// if there's a callback, run it
					call_user_func($this->display_callback);

				} elseif ( in_array($this->type, $this->valid_field_types) ) {

					// the specified type exists, run the appropriate method
					$field = call_user_method($this->type, $this);

					// filter the output
					$field = apply_filters( 'tribe_field_output_'.$this->type, $field, $this->id, $this );
					echo apply_filters( 'tribe_field_output_'.$this->type.'_'.$this->id, $field, $this->id, $this );

				} else {

					// fail, log the error
					TribeEvents::debug( __('Invalid field type specified', 'tribe-events-calendar'), $this->type, 'notice');

				}

			}
		}

		/**
		 * returns the field's start
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field start
		 */
		public function doFieldStart() {
			$return = '<fieldset id="tribe-field-'.$this->id.'"';
			$return .= ' class="tribe-field tribe-field-'.$this->type;
			$return .= ($this->error) ? 'tribe-error' : '';
			$return .= ($this->class) ? ' '.$this->class.'"' : '"';
			$return .= '>';
			return apply_filters( 'tribe_field_start', $return, $this->id, $this->type, $this->error, $this->class, $this );
		}

		/**
		 * returns the field's end
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field end
		 */
		public function doFieldEnd() {
			$return = '</fieldset>';
			return apply_filters( 'tribe_field_end', $return, $this->id, $this );
		}

		/**
		 * returns the field's label
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field label
		 */
		public function doFieldLabel() {
			$return = '';
			if ($this->label)
				$return = '<legend class="tribe-field-label">'.$this->label.'</legend>';
			return apply_filters( 'tribe_field_label', $return, $this->label, $this );
		}

		/**
		 * returns the field's div start
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field div start
		 */
		public function doFieldDivStart() {
			$return = '<div class="tribe-field-wrap">';
			return apply_filters( 'tribe_field_div_start', $return, $this );
		}

		/**
		 * returns the field's div end
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field div end
		 */
		public function doFieldDivEnd() {
			$return = '</div>';
			return apply_filters( 'tribe_field_div_end', $return, $this );
		}

		/**
		 * returns the field's title, which is used as the tooltip
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field tooltip
		 */
		public function doToolTip() {
			$return = '';
			if ($this->tooltip)
				$return = ' title="'.$this->tooltip.'"';
			return apply_filters( 'tribe_field_tooltip', $return, $this->tooltip, $this );
		}

		/**
		 * returns the screen reader label
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the screen reader label
		 */
		public function doScreenReaderLabel() {
			$return = '';
			if ($this->tooltip)
				$return = '<label class="screen-reader-text">'.$this->tooltip.'</label>';
			return apply_filters( 'tribe_field_screen_reader_label', $return, $this->tooltip, $this );
		}

		/**
		 * returns the field's value
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field value
		 */
		public function doFieldValue() {
			$return = '';
			if ($this->value)
				$return = ' value="'.$this->value.'"';
			return apply_filters( 'tribe_field_value', $return, $this->value, $this );
		}

		/**
		 * returns the field's name
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field name
		 */
		public function doFieldName() {
			$return = '';
			if ($this->name)
				$return = ' name="'.$this->name.'"';
			return apply_filters( 'tribe_field_name', $return, $this->name, $this );
		}

		/**
		 * generate a heading field
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field
		 */
		public function heading() {
			$field = '<h3>'.$this->label.'</h3>';
			return $field;
		}

		/**
		 * generate an html field
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field
		 */
		public function html() {
			$field = $this->html;
			return $field;
		}


		/**
		 * generate a simple text field
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field
		 */
		public function text() {
			switch ($this->size) {
				case 'large' : $size = '118'; break;
				case 'medium' : $size = '30'; break;
				case 'small'; default : $size = '4'; break;
			}
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			$field .= '<input';
			$field .= ' type="text"';
			$field .= $this->doFieldName();
			$field .= ' size="'.$size.'"';
			$field .= $this->doFieldValue();
			$field .= $this->doToolTip();
			$field .= '/>';
			$field .= $this->doScreenReaderLabel();
			$field .= $this->doFieldEnd();
			$field .= $this->doFieldEnd();
			return $field;
		}

		/**
		 * generate a textarea field
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field
		 */
		public function textarea() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			$field .= '<textarea';
			$field .= $this->doFieldName();
			$field .= $this->doToolTip();
			$field .= '>';
			$field .= stripslashes($this->value);
			$field .= '</textarea>';
			$field .= $this->doScreenReaderLabel();
			$field .= $this->doFieldEnd();
			$field .= $this->doFieldEnd();
			return $field;
		}

		/**
		 * generate a radio button field
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field
		 */
		public function radio() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			if ( is_array($this->options) ) {
				foreach ($this->options as $option_id => $title) {
					$field .= '<label title="'.$title.'">';
					$field .= '<input type="radio"';
					$field .= $this->doFieldName();
 					$field .= ' value="'.$option_id.'" '.checked( $this->value, $option_id, false ).'/>';
					$field .= $title;
					$field .= '</label>';
				}
			} else {
				$field .= '<span class="tribe-error">'.__('No radio options specified', 'tribe-events-calendar').'</span>';
			}
			$field .= $this->doFieldDivEnd();
			$field .= $this->doFieldEnd();
			return $field;
		}

		/**
		 * generate a boolean checkbox field
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field
		 */
		public function checkbox_bool() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			$field .= '<input type="checkbox"';
			$field .= $this->doFieldName();
			$field .= ' value="1" '.checked( $this->value, true, false );
			$field .= $this->doToolTip();
			$field .= '/>';
			$field .= $this->doScreenReaderLabel();
			$field .= $this->doFieldDivEnd();
			$field .= $this->doFieldEnd();
			return $field;
		}

		/**
		 * generate a dropdown field
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field
		 */
		public function dropdown() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= $this->doFieldDivStart();
			if ( is_array($this->options) && !empty($this->options) ) {
				$field .= '<select';
				$field .= $this->doFieldName();
				$field .= $this->doToolTip();
				$field .= '>';
				foreach ($this->options as $option_id => $title) {
					$field .= '<option value="'.$option_id.'"';
					$field .= selected( $this->value, $option_id, false );
					$field .= '>'.$title.'</option>';
				}
				$field .= '</select>';
				$field .= $this->doScreenReaderLabel();
			} elseif ($this->if_empty) {
				$field .= '<span class="empty-field">'.(string) $this->if_empty.'</span>';
			} else {
				$field .= '<span class="tribe-error">'.__('No select options specified', 'tribe-events-calendar').'</span>';
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
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field
		 */
		public function dropdown_chosen() {
			$field = $this->dropdown();
			return $field;
		}

		/**
		 * generate a license key field - the same as the
		 * regular text field but wrapped so it can have the
		 * right css class applied to it
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return string the field
		 */
		public function license_key() {
			$field = $this->text();
			return $field;
		}

	} // end class

} // endif class_exists