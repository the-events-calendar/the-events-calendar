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
class Tribe__Events__Repositories__Venue extends Tribe__Repository {

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
			'post_type'                    => Tribe__Events__Main::POSTTYPE,
			// We'll be handling the dates, let's mark the query as a non-filtered one.
			'tribe_suppress_query_filters' => true,
		);

		$this->schema = array_merge( $this->schema, array(
			'address'        => array( $this, 'filter_by_address' ),
			'city'           => array( $this, 'filter_by_city' ),
			'country'        => array( $this, 'filter_by_country' ),
			'phone'          => array( $this, 'filter_by_phone' ),
			'postal_code'    => array( $this, 'filter_by_postal_code' ),
			'province'       => array( $this, 'filter_by_province' ),
			'state'          => array( $this, 'filter_by_state' ),
			'state_province' => array( $this, 'filter_by_state_province' ),
			'website'        => array( $this, 'filter_by_website' ),
		) );
	}

	public function filter_by_like_regex( $meta_key, $value ) {
		if ( tribe_is_regex( $value ) ) {
			$this->by( 'meta_regexp', $meta_key, tribe_unfenced_regex( $value ) );

			return;
		}

		$this->by( 'meta_like', $meta_key, $value );
	}

	/**
	 * Filters venues by a specific address line.
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 *
	 * @return array|null An array of query arguments or null if modified with internal methods.
	 */
	public function filter_by_address( $value ) {
		$meta_key = '_VenueAddress';

		$this->filter_by_like_regex( $meta_key, $value );
	}

	/**
	 * Filters venues by a specific city.
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 *
	 * @return array|null An array of query arguments or null if modified with internal methods.
	 */
	public function filter_by_city( $value ) {
		$meta_key = '_VenueCity';

		$this->filter_by_like_regex( $meta_key, $value );
	}

	/**
	 * Filters venues by a specific country.
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 *
	 * @return array|null An array of query arguments or null if modified with internal methods.
	 */
	public function filter_by_country( $value ) {
		$meta_key = '_VenueCountry';

		$this->filter_by_like_regex( $meta_key, $value );
	}

	/**
	 * Filters venues by a specific phone.
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 *
	 * @return array|null An array of query arguments or null if modified with internal methods.
	 */
	public function filter_by_phone( $value ) {
		$meta_key = '_VenuePhone';

		$this->filter_by_like_regex( $meta_key, $value );
	}

	/**
	 * Filters venues by a specific postal code.
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 *
	 * @return array|null An array of query arguments or null if modified with internal methods.
	 */
	public function filter_by_postal_code( $value ) {
		$meta_key = '_VenueZip';

		$this->filter_by_like_regex( $meta_key, $value );
	}

	/**
	 * Filters venues by a specific province. This is an alias for filter_by_state_province()
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 *
	 * @return array|null An array of query arguments or null if modified with internal methods.
	 */
	public function filter_by_province( $value ) {
		$this->filter_by_state_province( $value );
	}

	/**
	 * Filters venues by a specific state. This is an alias for filter_by_state_province()
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 *
	 * @return array|null An array of query arguments or null if modified with internal methods.
	 */
	public function filter_by_state( $value ) {
		$this->filter_by_state_province( $value );
	}

	/**
	 * Filters venues by a specific state/province.
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 *
	 * @return array|null An array of query arguments or null if modified with internal methods.
	 */
	public function filter_by_state_province( $value ) {
		$meta_key = '_VenueStateProvince';

		$this->filter_by_like_regex( $meta_key, $value );
	}

	/**
	 * Filters venues by a specific website.
	 *
	 * @since TBD
	 *
	 * @param string $value MySQL compatible LIKE or REGEX string.
	 *
	 * @return array|null An array of query arguments or null if modified with internal methods.
	 */
	public function filter_by_website( $value ) {
		$meta_key = '_VenueURL';

		$this->filter_by_like_regex( $meta_key, $value );
	}

}
