<?php

/**
 * Merge pre-3.0 duplicate venues and organizers
 */
class Tribe_Amalgamator {
	private $default_venue = 0;
	private $default_community_venue = 0;
	private $default_organizer = 0;
	private $default_community_organizer = 0;

	/**
	 * constructor
	 */
	public function __construct() {
		$this->default_venue     = (int) tribe_get_option( 'eventsDefaultVenueID', 0 );
		$this->default_organizer = (int) tribe_get_option( 'eventsDefaultOrganizerID', 0 );

		if ( class_exists( 'TribeCommunityEvents' ) ) {
			$community                         = TribeCommunityEvents::instance();
			$this->default_community_venue     = (int) $community->getOption( 'defaultCommunityVenueID', 0 );
			$this->default_community_organizer = (int) $community->getOption( 'defaultCommunityOrganizerID', 0 );
		}
	}

	/**
	 * Merge all duplicate event-related posts
	 *
	 * @return void
	 */
	public function merge_duplicates() {
		$this->merge_identical_organizers();
		$this->merge_identical_venues();

		$events = TribeEvents::instance();
		$events->setOption( 'organizer_venue_amalgamation', 1 );
		wp_cache_flush();
	}

	/**
	 * Merge identical organizers
	 *
	 * @return void
	 */
	public function merge_identical_organizers() {
		$titles  = $this->get_redundant_titles( TribeEvents::ORGANIZER_POST_TYPE );
		$buckets = array();
		foreach ( $titles as $t ) {
			$organizer_ids = $this->get_posts_with_title( $t, TribeEvents::ORGANIZER_POST_TYPE );
			foreach ( $organizer_ids as $id ) {
				$post = get_post( $id );
				$data = array(
					'title'             => $post->post_title,
					'status'            => $post->post_status,
					'content'           => $post->post_content,
					'_OrganizerPhone'   => get_post_meta( $id, '_OrganizerPhone', true ),
					'_OrganizerWebsite' => get_post_meta( $id, '_OrganizerWebsite', true ),
					'_OrganizerEmail'   => get_post_meta( $id, '_OrganizerEmail', true ),
				);
				$hash = md5( serialize( $data ) );
				if ( ! isset( $buckets[$hash] ) ) {
					$buckets[$hash] = array();
				}
				// prioritize organizers with an eventbrite id
				$eventbrite = get_post_meta( $id, '_OrganizerEventBriteID', true );
				if ( empty( $eventbrite ) ) {
					array_push( $buckets[$hash], $id );
				} else {
					array_unshift( $buckets[$hash], $id );
				}
			}
		}
		foreach ( $buckets as $organizer_ids ) {
			$this->amalgamate_organizers( $organizer_ids );
		}
	}

	/**
	 * Merge identical venues
	 *
	 * @return void
	 */
	public function merge_identical_venues() {
		$titles  = $this->get_redundant_titles( TribeEvents::VENUE_POST_TYPE );
		$buckets = array();
		foreach ( $titles as $t ) {
			$venue_ids = $this->get_posts_with_title( $t, TribeEvents::VENUE_POST_TYPE );
			foreach ( $venue_ids as $id ) {
				$post = get_post( $id );
				$data = array(
					'title'          => $post->post_title,
					'status'         => $post->post_status,
					'content'        => $post->post_content,
					'_VenueAddress'  => get_post_meta( $id, '_VenueAddress', true ),
					'_VenueCity'     => get_post_meta( $id, '_VenueCity', true ),
					'_VenueProvince' => get_post_meta( $id, '_VenueProvince', true ),
					'_VenueState'    => get_post_meta( $id, '_VenueState', true ),
					'_VenueCountry'  => get_post_meta( $id, '_VenueCountry', true ),
					'_VenueZip'      => get_post_meta( $id, '_VenueZip', true ),
					'_VenuePhone'    => get_post_meta( $id, '_VenuePhone', true ),
					'_VenueURL'      => get_post_meta( $id, '_VenueURL', true ),
				);
				$hash = md5( serialize( $data ) );
				if ( ! isset( $buckets[$hash] ) ) {
					$buckets[$hash] = array();
				}
				// prioritize venues with an eventbrite id
				$eventbrite = get_post_meta( $id, '_VenueEventBriteId', true );
				if ( empty( $eventbrite ) ) {
					array_push( $buckets[$hash], $id );
				} else {
					array_unshift( $buckets[$hash], $id );
				}
			}
		}
		foreach ( $buckets as $venue_ids ) {
			$this->amalgamate_venues( $venue_ids );
		}
	}

	/**
	 * Get all post titles of the given post type that have duplicates
	 *
	 * @param string $type The post type to query
	 *
	 * @return array
	 */
	private function get_redundant_titles( $type ) {
		global $wpdb;
		$sql    = "SELECT post_title FROM {$wpdb->posts} WHERE post_type=%s GROUP BY post_title HAVING COUNT(*) > 1";
		$sql    = $wpdb->prepare( $sql, $type );
		$titles = $wpdb->get_col( $sql );

		return $titles;
	}

	/**
	 * Find all posts of the given type with the given title
	 *
	 * @param string $title
	 * @param string $type
	 *
	 * @return array
	 */
	private function get_posts_with_title( $title, $type ) {
		global $wpdb;
		$sql   = "SELECT ID FROM {$wpdb->posts} WHERE post_type=%s AND post_title=%s ORDER BY ID ASC";
		$sql   = $wpdb->prepare( $sql, $type, $title );
		$posts = $wpdb->get_col( $sql );

		return $posts;
	}

	/**
	 * Merge all venues in the given list into one post (keeping the first)
	 *
	 * @param array $venue_ids
	 *
	 * @return void
	 */
	private function amalgamate_venues( $venue_ids ) {
		if ( empty( $venue_ids ) || count( $venue_ids ) < 2 ) {
			return;
		}
		global $wpdb;
		array_map( 'intval', $venue_ids );
		$keep    = array_shift( $venue_ids );
		$old_ids = implode( ',', $venue_ids );
		$sql     = "UPDATE {$wpdb->postmeta} SET meta_value=%d WHERE meta_key=%s AND meta_value IN($old_ids)";
		$sql     = $wpdb->prepare( $sql, $keep, '_EventVenueID' );
		$wpdb->query( $sql );
		$this->update_default_venues( $keep, $venue_ids );
		$this->delete_posts( $venue_ids );
	}


	/**
	 * Merge all organizers in the given list into one post (keeping the first)
	 *
	 * @param array $organizer_ids
	 *
	 * @return void
	 */
	public function amalgamate_organizers( $organizer_ids ) {
		if ( empty( $organizer_ids ) || count( $organizer_ids ) < 2 ) {
			return;
		}
		global $wpdb;
		array_map( 'intval', $organizer_ids );
		$keep    = array_shift( $organizer_ids );
		$old_ids = implode( ',', $organizer_ids );
		$sql     = "UPDATE {$wpdb->postmeta} SET meta_value=%d WHERE meta_key=%s AND meta_value IN($old_ids)";
		$sql     = $wpdb->prepare( $sql, $keep, '_EventOrganizerID' );
		$wpdb->query( $sql );
		$this->update_default_organizers( $keep, $organizer_ids );
		$this->delete_posts( $organizer_ids );
	}

	/**
	 * If a removed venue is being used as a default, change the default to
	 * its replacement.
	 *
	 * @param int   $keep
	 * @param array $replace
	 *
	 * @return void
	 */
	private function update_default_venues( $keep, array $replace ) {
		if ( $this->default_venue && in_array( $this->default_venue, $replace ) ) {
			$events = TribeEvents::instance();
			$events->setOption( 'eventsDefaultVenueID', $keep );
		}
		if ( $this->default_community_venue && in_array( $this->default_community_venue, $replace ) ) {
			$community = TribeCommunityEvents::instance();
			$community->setOption( 'defaultCommunityVenueID', $keep );
		}
	}


	/**
	 * If a removed organizer is being used as a default, change the default to
	 * its replacement.
	 *
	 * @param int   $keep
	 * @param array $replace
	 *
	 * @return void
	 */
	private function update_default_organizers( $keep, array $replace ) {
		if ( $this->default_organizer && in_array( $this->default_organizer, $replace ) ) {
			$events = TribeEvents::instance();
			$events->setOption( 'eventsDefaultOrganizerID', $keep );
		}
		if ( $this->default_community_organizer && in_array( $this->default_community_organizer, $replace ) ) {
			$community = TribeCommunityEvents::instance();
			$community->setOption( 'defaultCommunityOrganizerID', $keep );
		}
	}

	/**
	 * Delete all the posts given
	 *
	 * @param array $post_ids
	 */
	private function delete_posts( $post_ids ) {
		foreach ( $post_ids as $id ) {
			$force = apply_filters( 'tribe_force_delete_duplicates', true );
			wp_delete_post( $id, $force );
		}
	}

	/**
	 * Make a button to trigger the amalgamation process
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function migration_button( $text = '' ) {
		$text     = $text ? $text : __( 'Merge Duplicates', 'tribe-events-calendar' );
		$html     = '<a href="%s" class="button">%s</a>';
		$settings = TribeSettings::instance();
		// get the base settings page url
		$url  = apply_filters(
			'tribe_settings_url', add_query_arg(
				array(
					'post_type' => TribeEvents::POSTTYPE,
					'page'      => $settings->adminSlug
				), admin_url( 'edit.php' )
			)
		);
		$url  = add_query_arg( array( 'amalgamate' => '1' ), $url );
		$url  = wp_nonce_url( $url, 'amalgamate_duplicates' );
		$html = sprintf( $html, $url, $text );

		return $html;
	}

	/**
	 * If the migration button is clicked, start working
	 *
	 * @return void
	 */
	public static function listen_for_migration_button() {
		if ( empty( $_REQUEST['amalgamate'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'amalgamate_duplicates' ) ) {
			return;
		}

		$amalgamator = new self();
		$amalgamator->merge_duplicates();

		// redirect to base settings page
		$settings = TribeSettings::instance();
		$url      = apply_filters(
			'tribe_settings_url', add_query_arg(
				array(
					'post_type' => TribeEvents::POSTTYPE,
					'page'      => $settings->adminSlug
				), admin_url( 'edit.php' )
			)
		);
		wp_redirect( $url );
		exit();
	}
}
