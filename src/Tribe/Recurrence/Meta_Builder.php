<?php


class Tribe__Events__Pro__Recurrence__Meta_Builder {

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var int An event type post ID
	 */
	protected $event_id;
	/**
	 * @var Tribe__Events__Pro__Recurrence__Utils
	 */
	protected $utils;

	/**
	 * Tribe__Events__Pro__Recurrence__Meta_Builder constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $event_id, array $data = array(), Tribe__Events__Pro__Recurrence__Utils $utils = null ) {
		$this->event_id = $event_id;
		$this->data = $data;
		$this->utils = $utils ? $utils : new Tribe__Events__Pro__Recurrence__Utils();
	}

	public function build_meta() {
		if ( empty( $this->data ) || empty( $this->data['recurrence'] ) ) {
			return $this->get_zero_array();
		}
		$recurrence_meta       = $this->get_zero_array();

		if ( isset( $this->data['recurrence']['recurrence-description'] ) ) {
			unset( $this->data['recurrence']['recurrence-description'] );
		}

		foreach ( array( 'rules', 'exclusions' ) as $rule_type ) {
			if ( ! isset( $this->data['recurrence'][ $rule_type ] ) ) {
				continue;
			}//end if

			foreach ( $this->data['recurrence'][ $rule_type ] as $key => &$recurrence ) {
				if ( ! $recurrence ) {
					continue;
				}

				// Ignore the rule if the type isn't set OR the type is set to 'None'
				// (we're not interested in exclusions here)
				if ( ( empty( $recurrence['type'] ) || 'None' === $recurrence['type'] ) && $rule_type !== 'exclusions' ) {
					continue;
				}

				if ( ( empty( $recurrence['type'] ) && empty( $recurrence['custom']['type'] ) ) || ( 'exclusions' == $rule_type && ! empty( $recurrence['custom']['type'] ) && 'None' === $recurrence['custom']['type'] ) ) {
					unset( $this->data['recurrence'][ $rule_type ][ $key ] );
					continue;
				}

				if ( isset( $recurrence['custom'] ) && isset( $recurrence['custom']['type-text'] ) ) {
					unset( $recurrence['custom']['type-text'] );
				}

				unset( $recurrence['occurrence-count-text'] );

				$datepicker_format = $this->utils->datepicker_formats( tribe_get_option( 'datepickerFormat' ) );

				if ( ! empty( $recurrence['end'] ) ) {
					$recurrence['end'] = $this->utils->datetime_from_format( $datepicker_format, $recurrence['end'] );
				}

				if ( isset( $recurrence['custom'] ) && 'Date' === $recurrence['custom']['type'] ) {
					$recurrence['custom']['date']['date'] = $this->utils->datetime_from_format( $datepicker_format, $recurrence['custom']['date']['date'] );
				}

				// if this isn't an exclusion and it isn't a Custom rule, then we don't need the custom array index
				if ( 'rules' === $rule_type && 'Custom' !== $recurrence['type'] ) {
					if ( isset( $recurrence['custom'] ) ) {
						unset( $recurrence['custom'] );
					}
				} else {
					$custom_types = array(
						'date', 'day', 'week', 'month', 'year',
					);

					$custom_type_key = $this->utils->to_key( $recurrence['custom']['type'] );

					// clean up extraneous array elements
					foreach ( $custom_types as $type ) {
						if ( $type === $custom_type_key ) {
							continue;
						}

						if ( ! isset( $recurrence['custom'][ $type ] ) ) {
							continue;
						}

						unset( $recurrence['custom'][ $type ] );
					}
				}//end else

				$recurrence['EventStartDate'] = $this->data['EventStartDate'];
				$recurrence['EventEndDate']   = $this->data['EventEndDate'];

				if ( $this->utils->is_valid( $this->event_id, $recurrence ) ) {
					$recurrence_meta[ $rule_type ][] = $recurrence;
				}
			}
		}

		return $recurrence_meta;
	}

	private function get_zero_array() {
		return array(
			'rules'       => array(), 'exclusions' => array(),
			'description' => empty( $this->data['recurrence']['description'] ) ? null : sanitize_text_field( $this->data['recurrence']['description'] ),
		);
	}
}