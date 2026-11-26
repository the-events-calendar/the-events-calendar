<?php
/**
 * Finds the existing venue or organizer that an event submission names by typing it.
 *
 * @since 6.18.5
 *
 * @package TEC\Events\Linked_Posts
 */

namespace TEC\Events\Linked_Posts;

use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Venue as Venue;

/**
 * Class Existing_Match
 *
 * A submitted name matches an existing post when no detail filled in on both sides differs.
 * Among the matches, the one with the most identical details wins, then the oldest.
 *
 * @since 6.18.5
 *
 * @package TEC\Events\Linked_Posts
 */
final class Existing_Match {
	/**
	 * Returns the ID of the existing venue the submitted venue data names.
	 *
	 * @since 6.18.5
	 *
	 * @param array{Venue?: mixed, Address?: mixed, City?: mixed, StateProvince?: mixed, State?: mixed, Province?: mixed, Zip?: mixed, Country?: mixed} $data The submitted venue data, keyed as the event forms send it.
	 *
	 * @return int The matching venue ID, or 0 if no venue matches.
	 */
	public function find_venue( array $data ): int {
		// The classic event form passes its data on slashed; saving strips the slashes, and so does the title lookup.
		$details   = wp_unslash( $data );
		$submitted = [
			'address'        => $this->text( $details['Address'] ?? '' ),
			'city'           => $this->text( $details['City'] ?? '' ),
			'state_province' => $this->submitted_region( $details ),
			'zip'            => $this->text( $details['Zip'] ?? '' ),
			'country'        => $this->text( $details['Country'] ?? '' ),
		];

		return $this->find(
			Venue::POSTTYPE,
			$data['Venue'] ?? '',
			$submitted,
			function ( int $venue_id ): array {
				return [
					'address'        => $this->text( get_post_meta( $venue_id, '_VenueAddress', true ) ),
					'city'           => $this->text( get_post_meta( $venue_id, '_VenueCity', true ) ),
					'state_province' => $this->first_filled(
						[
							'StateProvince' => get_post_meta( $venue_id, '_VenueStateProvince', true ),
							'State'         => get_post_meta( $venue_id, '_VenueState', true ),
							'Province'      => get_post_meta( $venue_id, '_VenueProvince', true ),
						],
						[ 'StateProvince', 'State', 'Province' ]
					),
					'zip'            => $this->text( get_post_meta( $venue_id, '_VenueZip', true ) ),
					'country'        => $this->text( get_post_meta( $venue_id, '_VenueCountry', true ) ),
				];
			}
		);
	}

	/**
	 * Returns the ID of the existing organizer the submitted organizer data names.
	 *
	 * @since 6.18.5
	 *
	 * @param array{Organizer?: mixed, Email?: mixed, Website?: mixed} $data The submitted organizer data, keyed as the event forms send it.
	 *
	 * @return int The matching organizer ID, or 0 if no organizer matches.
	 */
	public function find_organizer( array $data ): int {
		// The classic event form passes its data on slashed; saving strips the slashes, and so does the title lookup.
		$details   = wp_unslash( $data );
		$submitted = [
			'email'   => $this->text( $details['Email'] ?? '' ),
			'website' => $this->website( $details['Website'] ?? '' ),
		];

		return $this->find(
			Organizer::POSTTYPE,
			$data['Organizer'] ?? '',
			$submitted,
			function ( int $organizer_id ): array {
				return [
					'email'   => $this->text( get_post_meta( $organizer_id, '_OrganizerEmail', true ) ),
					'website' => $this->website( get_post_meta( $organizer_id, '_OrganizerWebsite', true ) ),
				];
			}
		);
	}

	/**
	 * Finds the best existing post with the given title whose details do not conflict with the submitted ones.
	 *
	 * @since 6.18.5
	 *
	 * @param string               $post_type The post type to search.
	 * @param mixed                $title     The submitted title.
	 * @param array<string,string> $submitted The normalized submitted details.
	 * @param callable             $stored    A function that takes a post ID and returns its normalized stored details.
	 *
	 * @return int The matching post ID, or 0 if none matches.
	 */
	private function find( string $post_type, $title, array $submitted, callable $stored ): int {
		$title = is_string( $title ) ? trim( $title ) : '';

		if ( '' === $title ) {
			return 0;
		}

		/*
		 * A title saved by a user without `unfiltered_html` goes through kses, which stores `&` as `&amp;`,
		 * while one saved by an administrator keeps the raw `&`, so look for both forms.
		 */
		$candidates = [];

		foreach ( array_unique( [ $title, wp_kses_normalize_entities( $title ) ] ) as $title_form ) {
			$candidates = array_merge(
				$candidates,
				get_posts(
					[
						'post_type'      => $post_type,
						'title'          => $title_form,
						'post_status'    => [ 'publish', 'future', 'draft', 'pending', 'private' ],
						'fields'         => 'ids',
						'posts_per_page' => -1,
						'no_found_rows'  => true,
					]
				)
			);
		}

		// Ties go to the oldest post.
		$candidates = array_unique( $candidates );
		sort( $candidates );

		$best_id    = 0;
		$best_score = -1;

		foreach ( $candidates as $candidate_id ) {
			/*
			 * A draft, pending or private post can hold details that are not public yet,
			 * so only the people who can edit it may have an event linked to it.
			 */
			if ( 'publish' !== get_post_status( $candidate_id ) && ! current_user_can( 'edit_post', $candidate_id ) ) {
				continue;
			}

			$score = $this->score( $submitted, $stored( $candidate_id ) );

			if ( $score > $best_score ) {
				$best_id    = $candidate_id;
				$best_score = $score;
			}
		}

		return $best_id;
	}

	/**
	 * Counts the details filled in on both sides that are identical.
	 *
	 * @since 6.18.5
	 *
	 * @param array<string,string> $submitted The normalized submitted details.
	 * @param array<string,string> $stored    The normalized stored details.
	 *
	 * @return int The number of identical details, or -1 if any detail filled in on both sides differs.
	 */
	private function score( array $submitted, array $stored ): int {
		$score = 0;

		foreach ( $submitted as $key => $value ) {
			$stored_value = $stored[ $key ] ?? '';

			if ( '' === $value || '' === $stored_value ) {
				continue;
			}

			if ( $value !== $stored_value ) {
				return -1;
			}

			++$score;
		}

		return $score;
	}

	/**
	 * Returns the submitted region the way saving the venue picks it, normalized.
	 *
	 * The classic form can send both a State and a Province, for example when the country changes after a
	 * state was picked. Saving a venue keeps the State only for the United States or no country.
	 *
	 * @since 6.18.5
	 *
	 * @see Tribe__Events__Venue::save_meta()
	 *
	 * @param array<string,mixed> $data The unslashed submitted venue data.
	 *
	 * @return string The normalized region, or an empty string.
	 */
	private function submitted_region( array $data ): string {
		$state_province = $this->text( $data['StateProvince'] ?? '' );

		if ( '' !== $state_province ) {
			return $state_province;
		}

		$country = $data['Country'] ?? '';
		$is_us   = ! $country || 'US' === $country || esc_html__( 'United States', 'the-events-calendar' ) === $country;
		$state   = $this->text( $data['State'] ?? '' );

		if ( '' !== $state && $is_us ) {
			return $state;
		}

		return $this->text( $data['Province'] ?? '' );
	}

	/**
	 * Returns the first of the given keys that holds a filled-in value, normalized.
	 *
	 * @since 6.18.5
	 *
	 * @param array<string,mixed> $data The data to read.
	 * @param string[]            $keys The keys to try, in order.
	 *
	 * @return string The normalized value, or an empty string.
	 */
	private function first_filled( array $data, array $keys ): string {
		foreach ( $keys as $key ) {
			$value = $this->text( $data[ $key ] ?? '' );

			if ( '' !== $value ) {
				return $value;
			}
		}

		return '';
	}

	/**
	 * Normalizes a text detail so that case and extra spaces do not count as a difference.
	 *
	 * @since 6.18.5
	 *
	 * @param mixed $value The value to normalize.
	 *
	 * @return string The normalized value, or an empty string if the value is not a string.
	 */
	private function text( $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		// The `u` modifier fails on invalid UTF-8, so fall back to the byte-based pattern.
		$collapsed = preg_replace( '/\s+/u', ' ', $value ) ?? preg_replace( '/\s+/', ' ', $value );
		$collapsed = trim( $collapsed );

		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $collapsed ) : strtolower( $collapsed );
	}

	/**
	 * Normalizes a website so that the scheme, a leading "www." and a trailing slash do not count as a difference.
	 *
	 * @since 6.18.5
	 *
	 * @param mixed $value The value to normalize.
	 *
	 * @return string The normalized website, or an empty string.
	 */
	private function website( $value ): string {
		$website = $this->text( $value );
		$website = preg_replace( '#^[a-z][a-z0-9+.-]*://#', '', $website );
		$website = preg_replace( '#^www\.#', '', $website );

		return rtrim( $website, '/' );
	}
}
