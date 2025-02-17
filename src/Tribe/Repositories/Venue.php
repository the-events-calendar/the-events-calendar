<?php
/**
 * The main ORM/Repository class for venues.
 *
 * @since 4.9
 */

/**
 * Class Tribe__Events__Repositories__Venue
 *
 * @since 4.9
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
	 * @since 4.9
	 * @since 6.10.1 Added `show_map` and `show_map_link` aliases.
	 */
	public function __construct() {
		parent::__construct();

		$this->create_args['post_type'] = Tribe__Events__Venue::POSTTYPE;

		$this->default_args = [
			'post_type'                    => Tribe__Events__Venue::POSTTYPE,
			// We'll be handling the dates, let's mark the query as a non-filtered one.
			'tribe_suppress_query_filters' => true,
		];

		// Add venue specific aliases.
		$this->update_fields_aliases = array_merge(
			$this->update_fields_aliases,
			[
				'venue'         => 'post_title',
				'address'       => '_VenueAddress',
				'city'          => '_VenueCity',
				'state'         => '_VenueState',
				'province'      => '_VenueProvince',
				'stateprovince' => '_VenueStateProvince',
				'postal_code'   => '_VenueZip',
				'zip'           => '_VenueZip',
				'country'       => '_VenueCountry',
				'phone'         => '_VenuePhone',
				'website'       => '_VenueURL',
				'show_map'      => '_VenueShowMap',
				'show_map_link' => '_VenueShowMapLink',
			]
		);

		$this->linked_id_meta_key = '_EventVenueID';

		$this->add_simple_meta_schema_entry( 'address', '_VenueAddress' );
		$this->add_simple_meta_schema_entry( 'city', '_VenueCity' );
		$this->add_simple_meta_schema_entry( 'state', '_VenueStateProvince' );
		$this->add_simple_meta_schema_entry( 'province', '_VenueStateProvince' );
		$this->add_simple_meta_schema_entry( 'state_province', '_VenueStateProvince' );
		$this->add_simple_meta_schema_entry( 'postal_code', '_VenueZip' );
		$this->add_simple_meta_schema_entry( 'zip', '_VenueZip' );
		$this->add_simple_meta_schema_entry( 'country', '_VenueCountry' );
		$this->add_simple_meta_schema_entry( 'phone', '_VenuePhone' );
		$this->add_simple_meta_schema_entry( 'website', '_VenueURL' );

		$this->schema = array_merge(
			$this->schema,
			[
				'has_events'    => [ $this, 'filter_by_has_events' ],
				'has_no_events' => [ $this, 'filter_by_has_no_events' ],
			]
		);
	}

	/**
	 * Formats a venue handled by the repository to the expected format.
	 *
	 * @since 6.10.1 Added to Venue ORM.
	 *
	 * @param int|WP_Post $id The ID or object of the venue to be formatted.
	 * @return WP_Post The formatted Venue object.
	 */
	protected function format_item( $id ) {
		$formatted = null === $this->formatter
			? tribe_get_venue_object( $id )
			: $this->formatter->format_item( $id );

		/**
		 * Filters a single formatted venue result.
		 *
		 * @since 6.10.1
		 *
		 * @param mixed|WP_Post                $formatted The formatted venue result, usually a post object.
		 * @param int                          $id        The formatted post ID.
		 * @param Tribe__Repository__Interface $this      The current repository object.
		 */
		$formatted = apply_filters( 'tribe_repository_venues_format_item', $formatted, $id, $this );

		return $formatted;
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter_postarr_for_create( array $postarr ) {
		// Require some minimum fields.
		if ( ! isset( $postarr['post_title'] ) ) {
			return false;
		}

		return parent::filter_postarr_for_create( $postarr );
	}

	/**
	 * Filters a venue query by ones that have associated events.
	 *
	 * @since 5.5.0
	 *
	 * @return array An array of query arguments that will be added to the main query.
	 */
	public function filter_by_has_events() {
		global $wpdb;

		$this->filter_query->join(
			$wpdb->prepare(
				"
				INNER JOIN {$wpdb->postmeta} AS venue_has_events
				ON ({$wpdb->posts}.ID = venue_has_events.meta_value
				AND venue_has_events.meta_key = %s)
					",
				$this->linked_id_meta_key
			)
		);
	}

	/**
	 * Filters a venue query by ones that DO NOT have associated events.
	 *
	 * @since 5.5.0
	 *
	 * @return array An array of query arguments that will be added to the main query.
	 */
	public function filter_by_has_no_events() {
		global $wpdb;

		$this->filter_query->where(
			$wpdb->prepare(
				"NOT EXISTS (
					SELECT * FROM {$wpdb->postmeta}
						WHERE {$wpdb->postmeta}.meta_key = %s
						AND {$wpdb->postmeta}.meta_value = {$wpdb->posts}.ID
					) ",
				$this->linked_id_meta_key
			)
		);
	}
}
