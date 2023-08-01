<?php
/**
 * Widget Shortcode Templates
 *
 * @since   TBD
 *
 * @package Tribe\Events\Pro\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views\Traits;

/**
 * Class With_Noindex
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Views\Traits
 */
trait With_Noindex {
	/**
	 * Do a short query (one event) to determine if we should add a noindex meta tag to the page.
	 *
	 * @since TBD
	 *
	 * @param Tribe__Repository|false $events     The events repository. False by default.
	 * @param DateTime                $start_date The start date (object) of the query.
	 * @param DateTime|null           $end_date   The end date (object) of the query.
	 * @param Tribe__Context| null    $context    The current context.
	 *
	 * @return Tribe__Repository|false $events     The events repository results.
	 */
	public function get_noindex_events( $events, $start_date, $end_date = null, $context = null ) {
		if ( null === $events )  { return; }

		$this->repository->get_query_vars();
		$cache     = new \Tribe__Cache();
		$trigger   = \Tribe__Cache_Listener::TRIGGER_SAVE_POST;
		$cache_key = $cache->make_key(
			[
				'view'    => $this->get_view_slug(),
				'start'   => $start_date->format( \Tribe__Date_Utils::DBDATEFORMAT ),
				'end'     => $end_date->format( \Tribe__Date_Utils::DBDATEFORMAT ),
				'args'    => $this->repository_args,
			],
			'tec_noindex_'
		);

		$events = $cache->get( $cache_key, $trigger );

		if ( ! $events ) {
			$this->repository->where( 'ends_after', $start_date );
			if ( ! empty( $end_date ) ) {
				$this->repository->where( 'starts_before', $end_date );
			}

			// We only need one ID to know we have events!
			$events = $this->repository->per_page( 1 )->fields( 'ids' );

			$cache->set( $cache_key, $events, 0, $trigger );
		}

		return $events;
	}
}
