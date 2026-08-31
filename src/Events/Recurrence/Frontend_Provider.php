<?php
/**
 * Registers the front-end pieces of the Recurrence feature: per-Occurrence links and the
 * rewrite rules resolving them.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Links\Provider as Links_Provider;
use Tribe__Events__Main as TEC;
use Tribe__Events__Rewrite as TEC_Rewrite;

/**
 * Class Frontend_Provider.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Frontend_Provider extends Service_Provider {
	/**
	 * Registers the per-Occurrence link handling and the rewrite rules for date-based
	 * single Event URLs.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( self::class, $this );

		$this->container->register( Links_Provider::class );

		if ( ! has_action( 'tribe_events_pre_rewrite', [ $this, 'add_rewrite_routes' ] ) ) {
			/*
			 * Priority 5 matches the Events Calendar Pro registration of the same rules:
			 * when both plugins add them, the identical regular expressions collapse to
			 * a single rewrite rule.
			 */
			add_action( 'tribe_events_pre_rewrite', [ $this, 'add_rewrite_routes' ], 5 );
		}
	}

	/**
	 * Unregisters the hooks added by the provider.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'tribe_events_pre_rewrite', [ $this, 'add_rewrite_routes' ], 5 );

		$this->container->make( Links_Provider::class )->unregister();
	}

	/**
	 * Adds the rewrite routes resolving date-based single Event URLs to a specific
	 * Occurrence, e.g. `/event/some-event/2026-11-12/` or, for multiple same-day
	 * Occurrences, `/event/some-event/2026-11-12/2/`.
	 *
	 * The routes mirror the ones registered by Events Calendar Pro.
	 *
	 * @since TBD
	 *
	 * @param TEC_Rewrite $rewrite The rewrite handler.
	 *
	 * @return void
	 */
	public function add_rewrite_routes( $rewrite ): void {
		if ( ! $rewrite instanceof TEC_Rewrite ) {
			return;
		}

		$rewrite
			->single(
				[ '(\d{4}-\d{2}-\d{2})' ],
				[
					TEC::POSTTYPE => '%1',
					'eventDate'   => '%2',
				] 
			)
			->single(
				[ '(\d{4}-\d{2}-\d{2})', '(feed|rdf|rss|rss2|atom)' ],
				[
					TEC::POSTTYPE => '%1',
					'eventDate'   => '%2',
					'feed'        => '%3',
				]
			)
			->single(
				[ '(\d{4}-\d{2}-\d{2})', '(\d+)', '(feed|rdf|rss|rss2|atom)' ],
				[
					TEC::POSTTYPE   => '%1',
					'eventDate'     => '%2',
					'eventSequence' => '%3',
					'feed'          => '%4',
				]
			)
			->single(
				[ '(\d{4}-\d{2}-\d{2})', '(\d+)' ],
				[
					TEC::POSTTYPE   => '%1',
					'eventDate'     => '%2',
					'eventSequence' => '%3',
				]
			)
			->single(
				[ '(\d{4}-\d{2}-\d{2})', 'embed' ],
				[
					TEC::POSTTYPE => '%1',
					'eventDate'   => '%2',
					'embed'       => 1,
				] 
			)
			->single(
				[ '(\d{4}-\d{2}-\d{2})', 'ical' ],
				[
					TEC::POSTTYPE => '%1',
					'eventDate'   => '%2',
					'ical'        => 1,
				] 
			);
	}
}
