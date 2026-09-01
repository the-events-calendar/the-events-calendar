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
	 * single Event URLs and the `/all/` Occurrences archive.
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

		if ( ! has_filter( 'tribe_events_rewrite_base_slugs', [ $this, 'add_all_base_slug' ] ) ) {
			// Priority 11 matches the Events Calendar Pro registration of the same base.
			add_filter( 'tribe_events_rewrite_base_slugs', [ $this, 'add_all_base_slug' ], 11 );
		}

		if ( ! has_filter( 'query_vars', [ $this, 'add_query_vars' ] ) ) {
			add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
		}

		if ( ! has_filter( 'tec_events_rewrite_dynamic_matchers', [ $this, 'add_dynamic_matchers' ] ) ) {
			add_filter( 'tec_events_rewrite_dynamic_matchers', [ $this, 'add_dynamic_matchers' ], 10, 3 );
		}

		if ( ! has_filter( 'tribe_rewrite_handled_rewrite_rules', [ $this, 'add_handled_rewrite_rules' ] ) ) {
			add_filter( 'tribe_rewrite_handled_rewrite_rules', [ $this, 'add_handled_rewrite_rules' ], 10, 2 );
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
		remove_filter( 'tribe_events_rewrite_base_slugs', [ $this, 'add_all_base_slug' ], 11 );
		remove_filter( 'query_vars', [ $this, 'add_query_vars' ] );
		remove_filter( 'tec_events_rewrite_dynamic_matchers', [ $this, 'add_dynamic_matchers' ] );
		remove_filter( 'tribe_rewrite_handled_rewrite_rules', [ $this, 'add_handled_rewrite_rules' ] );

		$this->container->make( Links_Provider::class )->unregister();
	}

	/**
	 * Adds the rewrite routes resolving date-based single Event URLs to a specific
	 * Occurrence, e.g. `/event/some-event/2026-11-12/` or, for multiple same-day
	 * Occurrences, `/event/some-event/2026-11-12/2/`, and the `/event/some-event/all/`
	 * Occurrences archive URLs.
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
				[ '{{ all }}', '{{ page }}', '(\d+)' ],
				[
					TEC::POSTTYPE           => '%1',
					'post_type'             => TEC::POSTTYPE,
					'eventDisplay'          => 'all',
					'tribe_recurrence_list' => true,
					'page'                  => '%2',
				]
			)
			->single(
				[ '{{ all }}' ],
				[
					TEC::POSTTYPE           => '%1',
					'post_type'             => TEC::POSTTYPE,
					'eventDisplay'          => 'all',
					'tribe_recurrence_list' => true,
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

	/**
	 * Adds the `all` rewrite base resolving the `/event/{slug}/all/` Occurrences archive
	 * URLs.
	 *
	 * The base matches, name and localization included, the one registered by Events
	 * Calendar Pro: when both plugins add it, the entries collapse to the same base.
	 *
	 * @since TBD
	 *
	 * @param array<string,string|array<string>> $bases The rewrite bases.
	 *
	 * @return array<string,string|array<string>> The rewrite bases, including the `all` one.
	 */
	public function add_all_base_slug( $bases = [] ): array {
		$bases = (array) $bases;

		// Support the original and translated forms for added robustness.
		$bases['all'] = [ 'all', sanitize_title( _x( 'all', 'all events slug', 'the-events-calendar' ) ) ];

		return $bases;
	}

	/**
	 * Adds the query variables used by the Occurrences archive to the public list.
	 *
	 * Without this WordPress would drop the `tribe_recurrence_list` variable from the
	 * matched rewrite rule query.
	 *
	 * @since TBD
	 *
	 * @param array<string> $query_vars The public query variables.
	 *
	 * @return array<string> The public query variables, including the Occurrences archive one.
	 */
	public function add_query_vars( $query_vars ): array {
		$query_vars   = (array) $query_vars;
		$query_vars[] = 'tribe_recurrence_list';

		return array_unique( $query_vars );
	}

	/**
	 * Adds the dynamic matcher resolving Occurrences archive query variables back to the
	 * pretty `/event/{slug}/all/` URL form.
	 *
	 * @since TBD
	 *
	 * @param array<string,string> $dynamic_matchers The dynamic matchers map.
	 * @param array<string,mixed>  $query_vars       The query variables being resolved.
	 * @param TEC_Rewrite|null     $rewrite          The rewrite handler resolving the URL.
	 *
	 * @return array<string,string> The dynamic matchers, including the Occurrences archive one.
	 */
	public function add_dynamic_matchers( $dynamic_matchers, $query_vars, $rewrite = null ): array {
		$dynamic_matchers = (array) $dynamic_matchers;

		if ( ! isset( $query_vars[ TEC::POSTTYPE ], $query_vars['tribe_recurrence_list'] ) ) {
			return $dynamic_matchers;
		}

		$rewrite = $rewrite instanceof TEC_Rewrite ? $rewrite : TEC_Rewrite::instance();
		$bases   = (array) $rewrite->get_bases();

		if ( ! isset( $bases['all'] ) ) {
			return $dynamic_matchers;
		}

		$all_regex = $bases['all'];
		preg_match( '/^\(\?:(?<slugs>[^\\)]+)\)/', $all_regex, $matches );

		if ( isset( $matches['slugs'] ) ) {
			$slugs = explode( '|', $matches['slugs'] );
			// The localized version is the last.
			$localized_slug                             = end( $slugs );
			$dynamic_matchers[ "([^/]+)/{$all_regex}" ] = "{$query_vars[ TEC::POSTTYPE ]}/{$localized_slug}";
		}

		return $dynamic_matchers;
	}

	/**
	 * Marks the single Event rewrite rules, the dated Occurrence and `/all/` ones included,
	 * as handled by the plugin canonical URL machinery.
	 *
	 * The default detection only picks up rules whose query contains an explicit
	 * `post_type=tribe_events` fragment, which the dated single Event rules do not have.
	 *
	 * @since TBD
	 *
	 * @param array<string,string> $handled_rules The rewrite rules handled by the plugin.
	 * @param array<string,string> $all_rules     All the registered rewrite rules.
	 *
	 * @return array<string,string> The handled rewrite rules, including the single Event ones.
	 */
	public function add_handled_rewrite_rules( $handled_rules, $all_rules ): array {
		$cache           = tribe_cache();
		$recurring_rules = $cache['tec_recurrence_handled_rewrite_rules'] ?? null;

		if ( ! is_array( $recurring_rules ) ) {
			$recurring_rules = array_filter(
				(array) $all_rules,
				static function ( $rule_query_string ) {
					return is_string( $rule_query_string )
						&& 0 === strpos( $rule_query_string, 'index.php?tribe_events=$matches[1]' );
				}
			);

			$cache['tec_recurrence_handled_rewrite_rules'] = $recurring_rules;
		}

		return array_merge( (array) $handled_rules, $recurring_rules );
	}
}
