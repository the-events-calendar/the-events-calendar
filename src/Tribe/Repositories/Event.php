<?php
/**
 * The main ORM/Repository class for events.
 *
 * @since TBD
 */

/**
 * Class Tribe__Events__Repositories__Event
 *
 *
 * @since TBD
 */
class Tribe__Events__Repositories__Event extends Tribe__Repository {

	/**
	 * The unique fragment that will be used to identify this repository filters.
	 *
	 * @var string
	 */
	protected $filter_name = 'events';

	/**
	 * Tribe__Events__Repositories__Event constructor.
	 *
	 * Sets up the repository default parameters and schema.
	 *
	 * @since TBD
	 */
	public function __construct() {
		parent::__construct();

		$this->default_args = array(
			'post_type'          => Tribe__Events__Main::POSTTYPE,
			// Since we'll be handling the dates let's avoid filtering side-effects.
			'do_not_inject_date' => true,
		);

		$this->schema = array_merge( $this->schema, array(
			'all_day' => array( $this, 'filter_by_all_day' ),
		) );
	}

	/**
	 * Filters the event by their all-day status.
	 *
	 * @since TBD
	 *
	 * @param bool $all_day Whether the events should be all-day or not.
	 *
	 * @return array|null An array of query arguments or null if modified with internal methods.
	 */
	public function filter_by_all_day( $all_day ) {
		if ( (bool) $all_day ) {
			$this->by( 'meta_equals', '_EventAllDay', 'yes' );

			return null;
		}

		return array(
			'meta_query' => array(
				'by-all-day' => array(
					'not-exists' => array(
						'key'     => '_EventAllDay',
						'compare' => 'NOT EXISTS',
						'value'   => '#'
					),
					'relation'   => 'OR',
					'is-not-yes' => array(
						'key'     => '_EventAllDay',
						'compare' => '!=',
						'value'   => 'yes',
					)
				),
			),
		);
	}
}