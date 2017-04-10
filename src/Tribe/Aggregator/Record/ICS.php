<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__ICS extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'ics';

	/**
	 * Creates an import record
	 *
	 * @param string $origin EA origin
	 * @param string $type Type of record to create - import or schedule
	 * @param array $args Post type args
	 *
	 * @return WP_Post|WP_Error
	 */
	public function create( $type = 'manual', $args = array(), $meta = array() ) {
		$defaults = array(
			'file'   => empty( $this->meta['file'] ) ? null : $this->meta['file'],
		);

		$meta = wp_parse_args( $meta, $defaults );

		return parent::create( $type, $args, $meta );
	}

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'ICS', 'the-events-calendar' );
	}

	/**
	 * Filters the event to ensure that fields are preserved that are not otherwise supported by ICS
	 *
	 * @param array $event Event data
	 * @param Tribe__Events__Aggregator__Record__Abstract $record Aggregator Import Record
	 *
	 * @return array
	 */
	public static function filter_event_to_preserve_fields( $event, $record ) {
		if ( 'ics' !== $record->origin ) {
			return $event;
		}

		return self::preserve_event_option_fields( $event );
	}
}
