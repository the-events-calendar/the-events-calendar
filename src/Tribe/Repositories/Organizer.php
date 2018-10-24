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
class Tribe__Events__Repositories__Organizer extends Tribe__Repository {

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

		$this->schema = array_merge( $this->schema, array(
			'email'   => array( $this, 'filter_by_email' ),
			'name'    => array( $this, 'filter_by_name' ),
			'phone'   => array( $this, 'filter_by_phone' ),
			'website' => array( $this, 'filter_by_website' ),
		) );
	}

	/**
	 * Filter by LIKE or REGEX string.
	 *
	 * @param string $meta_key Meta key to filter by.
	 * @param string $value    MySQL compatible LIKE or REGEX string.
	 */
	public function filter_by_like_regex( $meta_key, $value ) {
		if ( tribe_is_regex( $value ) ) {
			$this->by( 'meta_regexp', $meta_key, tribe_unfenced_regex( $value ) );

			return;
		}

		$this->by( 'meta_like', $meta_key, $value );
	}

	/**
	 * Filters organizers to include organizers that have a specified email.
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 */
	public function filter_by_email( $value ) {
		$meta_key = '_OrganizerEmail';

		$this->filter_by_like_regex( $meta_key, $value );
	}

	/**
	 * Filters organizers by a specific name. This is an alias of ->search()
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 */
	public function filter_by_name( $value ) {
		$this->search( $value );
	}

	/**
	 * Filters organizers by a specific phone.
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 */
	public function filter_by_phone( $value ) {
		$meta_key = '_OrganizerPhone';

		$this->filter_by_like_regex( $meta_key, $value );
	}

	/**
	 * Filters organizers by a specific website.
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 */
	public function filter_by_website( $value ) {
		$meta_key = 'OrganizerWebsite';

		$this->filter_by_like_regex( $meta_key, $value );
	}

}
