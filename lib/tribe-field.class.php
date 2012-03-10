<?php

// Don't load directly
if ( !defined('ABSPATH') ) die('-1');

if ( !class_exists('TribeField') ) {

	/**
	 * helper class that creates fields for use in Settings, MetaBoxes, Users, anywhere...
	 *
	 * @since 2.0.5
	 * @author jkudish
	 */
	class TribeField {

		public $id;
		public $name;
		public $args;
		public static $defaults;

		public function __construct($id, $field) {

			// seetup the defaults
			$this->defaults = array(
				'type' => 'html',
				'name' => $id,
				'class' => null,
				'label' => null,
				'tooltip' => null,
				'size' => 'small',
				'html' => null,
				'error' => null,
				'value' => null,
				'options' => null,
				'display_callback' => null,
			);

			$this->valid_field_types = array(
				'heading',
				'html',
				'text',
				'radio',
				'checkbox_bool',
				'dropdown',
				'dropdown_chosen',
				'textarea',
				'license_key',
			);

			apply_filters( 'tribe_valid_field_types', $this->valid_field_types );

			// parse args with defaults and extract them
			$args = wp_parse_args($field, $this->defaults);
			extract($args);

			$this->id = apply_filters( 'tribe_field_id', $id );

			// set each instance variable and filter
			foreach ($this->defaults as $key => $value) {
				$this->{$key} = apply_filters( 'tribe_field_'.$key, $$key, $this->id );
			}

			$this->doField();

		}

		public function doField() {
			if ( $this->display_callback && function_exists($this->display_callback) ) {
				call_user_func($this->display_callback);
			} elseif ( in_array($this->type, $this->valid_field_types) ) {
				$field = call_user_method($this->type, $this);
				echo apply_filters('tribe_field_'.$this->type.'_'.$this->id, $field, $this->id, $this);
			} else {
				TribeEvents::debug( __('Invalid field type specified', 'tribe-events-calendar'), $this->type, 'notice');
			}
		}

		public function doFieldStart() {
			$return = '<fieldset id="tribe-field-'.$this->id.'"';
			$return .= ($this->class) ? ' class="'.$this->class.'"' : '';
			$return .= '>';
			return apply_filters('tribe_field_start', $return, $this->id, $this->class, $this);
		}

		public function doFieldEnd() {
			$return = '</fieldset>';
			return apply_filters('tribe_field_end', $return, $this->id, $this);
		}

		public function doFieldLabel() {
			$return = '';
			if ($this->label)
				$return = '<legend class="tribe-field-label">'.$this->label.'</legend>';
			return apply_filters('tribe_field_label', $return, $this->label, $this);
		}

		public function doToolTip() {
			$return = '';
			if ($this->tooltip)
				$return = ' title="'.$this->tooltip.'"';
			return apply_filters('tribe_field_tooltip', $return, $this->tooltip, $this);
		}

		public function doScreenReaderLabel() {
			$return = '';
			if ($this->tooltip)
				$return = '<label class="screen-reader-text">'.$this->tooltip.'</label>';
			return apply_filters('tribe_field_screen_reader_label', $return, $this->tooltip, $this);
		}

		public function doFieldValue() {
			$return = '';
			if ($this->value)
				$return = ' value="'.$this->value.'"';
			return apply_filters('tribe_field_value', $return, $this->value, $this);
		}

		public function heading() {
			$field = '<h3>'.esc_attr($this->label).'</h3>';
			return $field;
		}

		public function html() {
			$field = esc_html($this->html);
			return $field;
		}

		public function text() {
			switch ($this->size) {
				case 'large' : $size = '10'; break;
				case 'medium' : $size = '6'; break;
				case 'small'; default : $size = '4'; break;
			}
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= '<input ';
			$field .= ( isset($this->error) ) ? 'class="tribe-error"' : '';
			$field .= 'type="text" name="'.$this->name.'" size="'.$size.'"';
			$field .= $this->doFieldValue();
			$field .= $this->doToolTip();
			$field .= '/>';
			$field .= $this->doScreenReaderLabel();
			$field .= $this->doFieldEnd();
			return $field;
		}

		public function radio() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			if ( is_array($this->options) ) {
				foreach ($this->options as $option_id => $title) {
					$field .= '<label title="'.$title.'">';
					$field .= '<input type="radio"';
					$field .= ( isset($this->error) ) ? 'class="tribe-error"' : '';
 					$field .= ' name="'.$this->name.'" value="'.$option_id.'" '.checked( $this->value, $option_id, false ).'/>';
					$field .= $title;
					$field .= '</label>';
				}
			} else {
				$field .= '<span class="tribe-error">'.__('No radio options specified', 'tribe-events-calendar').'</span>';
			}
			$field .= $this->doFieldEnd();
			return $field;
		}

		public function checkbox_bool() {
			$field = $this->doFieldStart();
			$field .= $this->doFieldLabel();
			$field .= '<input type="checkbox" name="'.$this->name.'" value="1" '.checked( $this->value, true, false );
			$field .= $this->doToolTip();
			$field .= '/>';
			$field .= $this->doScreenReaderLabel();
			$field .= $this->doFieldEnd();
			return $field;
		}


	} // end class

} // endif class_exists