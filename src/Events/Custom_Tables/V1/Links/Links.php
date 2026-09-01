<?php
/**
 * Handles the modification and validation of links in the context of the Administration UI.
 *
 * @since 6.0.0
 * @since TBD Migrated to The Events Calendar from Events Calendar Pro.
 *
 * @package TEC\Events\Custom_Tables\V1\Links
 */

namespace TEC\Events\Custom_Tables\V1\Links;

use DateTimeZone;
use Exception;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use WP_Post;

/**
 * Class Links
 *
 * @since 6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Links
 */
class Links {
	/**
	 * Updates the Event edit link to redirect it to the correct Occurrence if the Event is a Recurring one.
	 *
	 * @since 6.0.0
	 *
	 * @param string $link    The edit link, as produced from WordPress or the filters before this one.
	 * @param int    $post_id The post ID to produce the Edit link for. Might be a real post ID, or an
	 *                        Occurrence provisional one.
	 *
	 * @return string The updated edit link, if required.
	 */
	public function update_event_edit_link( string $link, int $post_id ): string {
		if ( get_post_type( $post_id ) !== TEC::POSTTYPE ) {
			return $link;
		}
		$event = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			return $link;
		}

		if ( ! $event->has_recurrence() ) {
			// Single Events should not be redirected.
			return $link;
		}

		/**
		 * Filters the post ID the current request should be redirected to.
		 *
		 * Returning a non `null` value in this filter will replace the default
		 * logic used to detect and redirect post IDs.
		 *
		 * @since 6.0.0
		 *
		 * @param int|null $post_id The post ID to redirect the request to, initially
		 *                          `null`.
		 */
		$provisional_id = apply_filters( 'tec_events_pro_custom_tables_v1_redirect_id', null );

		if ( null === $provisional_id ) {
			$occurrence     = $this->get_next_occurrence( $event );
			$provisional_id = $occurrence ? $occurrence->provisional_id : null;
		}

		if ( empty( $provisional_id ) ) {
			return $link;
		}

		return preg_replace( '/([?&])post=\d+(&|$)/u', '$1post=' . $provisional_id . '$2', $link );
	}

	/**
	 * Get the next occurrence relative to the current date, if none was found get the closest (previous) to the
	 * current date.
	 *
	 * @since 6.0.0
	 * @since TBD Made public: the dateless single Event redirect resolves its target with it.
	 *
	 * @param Event $event The instance of the event used to find the occurrence.
	 *
	 * @return Occurrence|null The found occurrence `null` otherwise.
	 */
	public function get_next_occurrence( Event $event ): ?Occurrence {
		try {
			$current_date = Dates::immutable( 'now', new DateTimeZone( 'UTC' ) );
			$upcoming     = Occurrence::where( 'event_id', $event->event_id )
										->where( 'end_date_utc', '>', $current_date )
										->order_by( 'start_date' )
										->first();

			if ( $upcoming instanceof Occurrence ) {
				return $upcoming;
			}

			// If the most recent event has passed, open the one closest to the end date of the event, meaning
			// the closest to the current date.
			$last = Occurrence::where( 'event_id', $event->event_id )
								->order_by( 'start_date', 'DESC' )
								->first();

			if ( $last instanceof Occurrence ) {
				return $last;
			}

			return null;
		} catch ( Exception $exception ) {
			do_action(
				'tribe_log',
				'error',
				__CLASS__,
				[
					'message' => 'Error while getting next Occurrence.',
					'error'   => $exception->getMessage(),
					'post_id' => $event->post_id,
				] 
			);

			return null;
		}
	}

	/**
	 * Updates the link to view an Occurrence in the context of the Administration UI.
	 *
	 * @since 6.0.0
	 * @since TBD A provisional Occurrence post now links to ITS date: the Event lookup normalizes
	 *            the provisional ID (it would find nothing and bail before) and the hydrated
	 *            Occurrence wins over the next-upcoming one. Provisional posts are handled on
	 *            the front end too, so each Occurrence card links its own date; real Event
	 *            posts keep the dateless permalink outside the Administration UI.
	 *
	 * @param string  $post_link   The View post link, as produced by WordPress and previous filters.
	 * @param WP_Post $post        A reference to the Post instance.
	 * @param bool    $leavename   Whether to leave the post name in the link or not.
	 * @param bool    $sample      Whether the link is being produced for the purpose of providing a sample of
	 *                             the view link, or not.
	 *
	 * @return string The updated view link, if required.
	 */
	public function update_recurrence_view_link( string $post_link, WP_Post $post, bool $leavename, bool $sample ): string {
		if ( $post->post_type !== TEC::POSTTYPE ) {
			return $post_link;
		}

		if ( ! is_admin() && ! tribe( Provisional_Post::class )->is_provisional_post_id( (int) $post->ID ) ) {
			// On the front end only provisional Occurrence posts get a dated permalink.
			return $post_link;
		}

		$event = Event::find( Occurrence::normalize_id( (int) $post->ID ), 'post_id' );

		if ( ! $event instanceof Event ) {
			return $post_link;
		}

		if ( ! $event->has_recurrence() ) {
			return $post_link;
		}

		$occurrence  = null;
		$provisional = tribe( Provisional_Post::class );

		if ( $provisional->is_provisional_post_id( (int) $post->ID ) ) {
			// A provisional Occurrence post links to the date of the Occurrence it represents.
			$hydrated   = $post->_tec_occurrence ?? null;
			$occurrence = $hydrated instanceof Occurrence
				? $hydrated
				: Occurrence::find( $provisional->normalize_provisional_post_id( (int) $post->ID ), 'occurrence_id' );
		}

		if ( ! $occurrence instanceof Occurrence ) {
			$occurrence = $this->get_next_occurrence( $event );
		}

		if ( ! $occurrence instanceof Occurrence ) {
			return $post_link;
		}

		$invalid_status = [
			'draft'      => true,
			'pending'    => true,
			'auto-draft' => true,
		];

		$unpublished = isset( $post->post_status, $invalid_status[ $post->post_status ] );

		if ( $unpublished && ! $sample ) {
			return $post_link;
		}

		// URL Arguments on home_url() pre-check. A missing permalink structure option returns `false`: plain permalinks either way.
		$url_query           = wp_parse_url( $post_link, PHP_URL_QUERY );
		$url_args            = wp_parse_args( $url_query, [] );
		$permalink_structure = (string) get_option( 'permalink_structure' );

		// Remove the "args".
		if ( ! empty( $url_query ) && '' !== $permalink_structure ) {
			$post_link = str_replace( '?' . $url_query, '', $post_link );
		}

		global $wp_rewrite;
		$permastruct = $wp_rewrite->get_extra_permastruct( $post->post_type );

		try {
			$start_date = Dates::immutable( $occurrence->start_date, new DateTimeZone( 'UTC' ) );
			$date       = $start_date->format( 'Y-m-d' );
		} catch ( Exception $exception ) {
			do_action(
				'tribe_log',
				'error',
				__CLASS__,
				[
					'message' => 'Error while building Occurrence start date.',
					'error'   => $exception->getMessage(),
					'post_id' => $event->post_id,
				] 
			);

			return $post_link;
		}

		if ( '' === $permalink_structure ) {
			$post_link = remove_query_arg( TEC::POSTTYPE, $post_link );
			$post_link = add_query_arg(
				[
					TEC::POSTTYPE => $post->post_name,
					'eventDate'   => $date,
				],
				$post_link
			);
		} elseif ( ! empty( $permastruct ) ) {
			if ( ! $leavename ) {
				$post_link = str_replace( "%$post->post_type%", $post->post_name, $permastruct );
			}
			$post_link = trailingslashit( $post_link ) . $date;
			$post_link = str_replace( [ home_url( '/' ), site_url( '/' ), get_option( 'home' ) ], '', $post_link );

			// Reconstruct URL.
			$reconstructed_url = home_url( user_trailingslashit( $post_link ) );

			/**
			 * Filters the reconstructed recurrence view link URL.
			 *
			 * This filter allows integrations to modify the reconstructed URL,
			 * for example to add language-specific prefixes or domain modifications.
			 *
			 * @since 7.7.12
			 *
			 * @param string  $reconstructed_url The reconstructed URL.
			 * @param string  $post_link         The relative post link path.
			 * @param WP_Post $post              The post object.
			 * @param string  $date              The occurrence date in Y-m-d format.
			 */
			$reconstructed_url = apply_filters(
				'tec_events_pro_custom_tables_v1_recurrence_view_link_url',
				$reconstructed_url,
				$post_link,
				$post,
				$date
			);

			$post_link = $reconstructed_url;
		}

		// Add the Arguments back.
		return add_query_arg( $url_args, $post_link );
	}
}
