<?php
/**
 * An immutable value object modeling the migration report for a site.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

/**
 * Class Site_Report.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Site_Report {

	/**
	 * Site_Report constructor.
	 * since TBD
	 *
	 * @param array <string,mixed> $data The report data in array format.
	 */
	public function __construct( $data ) {
		// @todo Hack - not sure how we want to consume and structure this... just want to test w/ frontend.
		foreach ( $data as $k => $f ) {
			$this->$k = $f;
		}


		// @todo Should we generate all of the dynamic il8n messages on backend?
		if ( $this->estimated_time_in_hours <= 1 ) {
			$this->estimated_time_in_hours_text = esc_html( '(Estimated time: %1$s hour)', 'ical-tec' );
		} else {
			$this->estimated_time_in_hours_text = esc_html( '(Estimated time: %1$s hours)', 'ical-tec' );
		}

		// @todo Put this in a function.
		// @todo Should we generate action message HTML that is consumed on the frontend...?
		foreach ( $this->events as $i => $event ) {

			if ( empty( $event->actions_taken ) ) {
				continue;
			}

			$this->events[ $i ]->actions_message = '';
			foreach ( $event->actions_taken as $action ) {
				if ( 'split' === $action ) {
					$this->events[ $i ]->actions_message .= sprintf(
						                                        esc_html( 'This event will be %1$ssplit into %2$s recurring events%3$s with identical content.', 'ical-tec' ),
						                                        '<strong>',
						                                        count( $event->events ),
						                                        '</strong>'
					                                        ) . ' ';

					$this->events[ $i ]->actions_message .= sprintf(
						                                        esc_html( 'The events will be part of a new %1$s.', 'ical-tec' ),
						                                        $event->series->post_title
					                                        ) . ' ';
				}

				if ( 'modified-exclusions' === $action ) {
					$this->events[ $i ]->actions_message .= sprintf(
						                                        esc_html( '%1$sOne or more exclusion rules will be modified%2$s, but no occurrences will be added or removed.', 'ical-tec' ),
						                                        '<strong>',
						                                        '</strong>'
					                                        ) . ' ';
				}

				if ( 'modified-rules' === $action ) {
					$this->events[ $i ]->actions_message .= sprintf(
						                                        esc_html( '%1$sOne or more recurrence rules will be modified%2$s, but no occurrences will be added or removed.', 'ical-tec' ),
						                                        '<strong>',
						                                        '</strong>'
					                                        ) . ' ';
				}
			}
		}

	}
}