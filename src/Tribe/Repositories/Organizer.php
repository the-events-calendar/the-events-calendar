<?php
/**
 * The main ORM/Repository class for organizers.
 *
 * @since TBD
 */

/**
 * Class Tribe__Events__Repositories__Organizer
 *
 *
 * @since TBD
 */
class Tribe__Events__Repositories__Organizer extends Tribe__Events__Repositories__Linked_Posts {

	/**
	 * The unique fragment that will be used to identify this repository filters.
	 *
	 * @var string
	 */
	protected $filter_name = 'organizers';

	/**
	 * Tribe__Events__Repositories__Organizer constructor.
	 *
	 * Sets up the repository default parameters and schema.
	 *
	 * @since TBD
	 */
	public function __construct() {
		parent::__construct();

		$this->default_args = array(
			'post_type'                    => Tribe__Events__Organizer::POSTTYPE,
			// We'll be handling the dates, let's mark the query as a non-filtered one.
			'tribe_suppress_query_filters' => true,
		);

		$this->linked_id_meta_key = '_EventOrganizerID';

		$this->schema = array_merge( $this->schema, array(
			'name' => array( $this, 'filter_by_name' ),
		) );

		$this->add_simple_meta_schema_entry( 'email', '_OrganizerEmail' );
		$this->add_simple_meta_schema_entry( 'phone', '_OrganizerPhone' );
		$this->add_simple_meta_schema_entry( 'website', '_OrganizerWebsite' );
	}

	/**
	 * Filters organizers by a specific name. This is an alias of ->search()
	 *
	 * @since TBD
	 *
	 * @param string $value String to search with.
	 */
	public function filter_by_name( $value ) {
		$this->search( $value );
	}

}
