<?php
/**
 * The main ORM/Repository class for organizers.
 *
 * @since 4.9
 */

/**
 * Class Tribe__Events__Repositories__Organizer
 *
 *
 * @since 4.9
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
	 * @since 4.9
	 */
	public function __construct() {
		parent::__construct();

		$this->create_args['post_type'] = Tribe__Events__Organizer::POSTTYPE;

		$this->default_args = [
			'post_type'                    => Tribe__Events__Organizer::POSTTYPE,
			// We'll be handling the dates, let's mark the query as a non-filtered one.
			'tribe_suppress_query_filters' => true,
		];

		// Add organizer specific aliases.
		$this->update_fields_aliases = array_merge( $this->update_fields_aliases, [
			'organizer' => 'post_title',
			'phone'     => '_OrganizerPhone',
			'website'   => '_OrganizerWebsite',
			'email'     => '_OrganizerEmail',
		] );

		$this->linked_id_meta_key = '_EventOrganizerID';

		$this->add_simple_meta_schema_entry( 'email', '_OrganizerEmail' );
		$this->add_simple_meta_schema_entry( 'phone', '_OrganizerPhone' );
		$this->add_simple_meta_schema_entry( 'website', '_OrganizerWebsite' );

		$this->schema = array_merge(
			$this->schema,
			[
				'has_events'          => [ $this, 'filter_by_has_events' ],
				'has_no_events'       => [ $this, 'filter_by_has_no_events' ],
			]
		);
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
	 * Filters the organizer query by ones that have associated events.
	 *
	 * @since 5.5.0
	 */
	public function filter_by_has_events(): void {
		global $wpdb;

		$this->filter_query->join(
			$wpdb->prepare(
				"
				INNER JOIN {$wpdb->postmeta} AS organizer_has_events
				ON ({$wpdb->posts}.ID = organizer_has_events.meta_value
				AND organizer_has_events.meta_key = %s)
					",
				$this->linked_id_meta_key
			)
		);
	}

	/**
	 * Filters the organizer query by ones that DO NOT have associated events.
	 *
	 * @since 5.5.0
	 */
	public function filter_by_has_no_events(): void {
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

	/**
	 * {@inheritDoc}
	 */
	protected function format_item( $id ) {
		$formatted = null === $this->formatter
			? tribe_get_organizer_object( $id )
			: $this->formatter->format_item( $id );

		/**
		 * Filters a single formatted organizer result.
		 *
		 * @since 6.15.0
		 *
		 * @param mixed|WP_Post                $formatted The formatted event result, usually a post object.
		 * @param int                          $id        The formatted post ID.
		 * @param Tribe__Repository__Interface $this      The current repository object.
		 */
		$formatted = apply_filters( 'tribe_repository_organizers_format_item', $formatted, $id, $this );

		return $formatted;
	}
}
