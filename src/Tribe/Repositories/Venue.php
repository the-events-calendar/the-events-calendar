<?php
/**
 * The main ORM/Repository class for venues.
 *
 * @since TBD
 */

/**
 * Class Tribe__Events__Repositories__Venue
 *
 *
 * @since TBD
 */
class Tribe__Events__Repositories__Venue extends Tribe__Events__Repositories__Linked_Posts {

	/**
	 * The unique fragment that will be used to identify this repository filters.
	 *
	 * @var string
	 */
	protected $filter_name = 'venues';

	/**
	 * Tribe__Events__Repositories__Venue constructor.
	 *
	 * Sets up the repository default parameters and schema.
	 *
	 * @since TBD
	 */
	public function __construct() {
		parent::__construct();

		$this->default_args = array(
			'post_type'                    => Tribe__Events__Venue::POSTTYPE,
			// We'll be handling the dates, let's mark the query as a non-filtered one.
			'tribe_suppress_query_filters' => true,
		);

		$this->linked_id_meta_key = '_EventVenueID';

		$this->add_simple_meta_schema_entry( 'address', '_VenueAddress' );
		$this->add_simple_meta_schema_entry( 'city', '_VenueCity' );
		$this->add_simple_meta_schema_entry( 'country', '_VenueCountry' );
		$this->add_simple_meta_schema_entry( 'phone', '_VenuePhone' );
		$this->add_simple_meta_schema_entry( 'postal_code', '_VenueZip' );
		$this->add_simple_meta_schema_entry( 'province', '_VenueStateProvince' );
		$this->add_simple_meta_schema_entry( 'state', '_VenueStateProvince' );
		$this->add_simple_meta_schema_entry( 'state_province', '_VenueStateProvince' );
		$this->add_simple_meta_schema_entry( 'website', '_VenueURL' );
	}

}
