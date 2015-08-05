<?php

class Tribe__Events__Pro__Community_Modifications {

	private static $instance = NULL;

	public static function init() {
		self::instance()->add_hooks();
	}

	protected function add_hooks() {
		add_filter( 'tribe_events_community_required_fields', array( self::instance(), 'add_recurrence_required_fields' ) );
		add_filter( 'tribe_community_form_field_label', array( self::instance(), 'field_labels' ), 10, 2 );
	}

	public function add_recurrence_required_fields( $required_fields ) {

		if ( empty( $_POST ) ) {
			return $required_fields;
		}

		return $required_fields;
	}

	public function field_labels( $label, $field ) {
		switch ( $field ) {
			case 'recurrence[end]':
				$label = __( 'Recurrence End Date', 'tribe-events-calendar-pro' );
				break;
			case 'recurrence[end-count]':
				$label = __( 'Recurrence Count', 'tribe-events-calendar-pro' );
				break;
		}
		return $label;

	}

	/**
	 * @return self
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

} // Tribe__Events__Pro__Community_Modifications
