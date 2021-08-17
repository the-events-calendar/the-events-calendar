<?php
// Don't load directly
defined( 'WPINC' ) or die;

use Tribe\Events\I18n;
use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Events__Main as TEC;
use Tribe__Main as Common;
use Tribe__Utils__Array as Arr;

/**
 * Rewrite Configuration Class
 * Permalinks magic Happens over here!
 */
class Tribe__Events__Rewrite extends Tribe__Rewrite {

	/**
	 * Constant holding the transient key for delayed triggered flush from activation.
	 *
	 * If this value is updated make sure you look at the method in the main class of TEC.
	 *
	 * @see TEC::activate
	 *
	 * @since 5.0.0.1
	 *
	 * @var string
	 */
	const KEY_DELAYED_FLUSH_REWRITE_RULES = '_tribe_events_delayed_flush_rewrite_rules';

	/**
	 * After creating the Hooks on WordPress we lock the usage of the function
	 * @var boolean
	 */
	protected $hook_lock = false;

	/**
	 * A map providing each current base to its current locale translation.
	 *
	 * @since 5.1.1
	 *
	 * @var array<string,string>
	 */
	protected $localized_bases = [];

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Rewrite
	 */
	public static function instance( $wp_rewrite = null ) {
		if ( version_compare( Common::VERSION, '4.9.11-dev', '>=' ) ) {
			return parent::instance();
		}

		/**
		 * Deprecated piece of code, but we need it in place to make sure
		 * we dont break with older version of Event Tickets.
		 *
		 * @todo  remove once we have common version compare back working
		 */
		if ( ! static::$instance ) {
			static::$instance = new static;
			static::$instance->setup();
		}

		return static::$instance;
	}

	/**
	 * Generate the Rewrite Rules
	 *
	 * @param  WP_Rewrite $wp_rewrite WordPress Rewrite that will be modified, pass it by reference (&$wp_rewrite)
	 */
	public function filter_generate( WP_Rewrite $wp_rewrite ) {
		parent::filter_generate( $wp_rewrite );

		/**
		 * Use this to change the Tribe__Events__Rewrite instance before new rules
		 * are committed.
		 *
		 * Should be used when you want to add more rewrite rules without having to
		 * deal with the array merge, noting that rules for The Events Calendar are
		 * themselves added via this hook (default priority).
		 *
		 * @var Tribe__Events__Rewrite $rewrite
		 */
		do_action( 'tribe_events_pre_rewrite', $this );

		/**
		 * Provides an opportunity to modify The Events Calendar's rewrite rules before they
		 * are merged in to WP's own rewrite rules.
		 *
		 * @param array $events_rewrite_rules
		 * @param Tribe__Events__Rewrite $tribe_rewrite
		 * @param WP_Rewrite $wp_rewrite WordPress Rewrite that will be modified.
		 */
		$this->rules = apply_filters( 'tribe_events_rewrite_rules_custom', $this->rules, $this, $wp_rewrite );

		$wp_rewrite->rules = $this->rules + $wp_rewrite->rules;
	}

	/**
	 * Sets up the rules required by The Events Calendar.
	 *
	 * This should be called during tribe_events_pre_rewrite, which means other plugins needing to add rules
	 * of their own can do so on the same hook at a lower or higher priority, according to how specific
	 * those rules are.
	 *
	 * @param Tribe__Events__Rewrite $rewrite
	 */
	public function generate_core_rules( Tribe__Events__Rewrite $rewrite ) {
		$rewrite
			// Single
			->single( [ 'ical' ], [ 'ical' => 1, 'name' => '%1', 'post_type' => Tribe__Events__Main::POSTTYPE ] )

			// Archive
			->archive( [ '{{ page }}', '(\d+)' ], [ 'eventDisplay' => 'list', 'paged' => '%1' ] )
			->archive( [ '{{ featured }}', '{{ page }}', '(\d+)' ], [ 'featured' => true, 'eventDisplay' => 'list', 'paged' => '%1' ] )
			->archive( [ '(feed|rdf|rss|rss2|atom)' ], [ 'eventDisplay' => 'list', 'feed' => '%1' ] )
			->archive( [ '{{ featured }}', '(feed|rdf|rss|rss2|atom)' ], [ 'featured' => true, 'eventDisplay' => 'list', 'feed' => '%1' ] )
			->archive( [ '{{ month }}' ], [ 'eventDisplay' => 'month' ] )
			->archive( [ '{{ month }}', '{{ featured }}' ], [ 'eventDisplay' => 'month', 'featured' => true ] )
			->archive( [ '{{ month }}', '(\d{4}-\d{2})' ], [ 'eventDisplay' => 'month', 'eventDate' => '%1' ] )
			->archive( [ '{{ list }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => 'list', 'paged' => '%1' ] )
			->archive( [ '{{ list }}', '{{ featured }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => 'list', 'featured' => true, 'paged' => '%1' ] )
			->archive( [ '{{ list }}' ], [ 'eventDisplay' => 'list' ] )
			->archive( [ '{{ list }}', '{{ featured }}' ], [ 'eventDisplay' => 'list', 'featured' => true ] )
			->archive( [ '{{ today }}' ], [ 'eventDisplay' => 'day' ] )
			->archive( [ '{{ today }}', '{{ featured }}' ], [ 'eventDisplay' => 'day', 'featured' => true ] )
			->archive( [ '(\d{4}-\d{2})' ], [ 'eventDisplay' => 'month', 'eventDate' => '%1' ] )
			->archive( [ '(\d{4}-\d{2})', '{{ featured }}' ], [ 'eventDisplay' => 'month', 'eventDate' => '%1', 'featured' => true ] )
			->archive( [ '(\d{4}-\d{2}-\d{2})' ], [ 'eventDisplay' => 'day', 'eventDate' => '%1' ] )
			->archive( [ '(\d{4}-\d{2}-\d{2})', '{{ featured }}' ], [ 'eventDisplay' => 'day', 'eventDate' => '%1', 'featured' => true ] )
			->archive( [ '{{ featured }}' ], [ 'featured' => true ] )
			->archive( [ '{{page}}', '(\d+)' ], [ 'eventDisplay' => 'default', 'paged' => '%1' ] )
			->archive( [], [ 'eventDisplay' => 'default' ] )
			->archive( [ 'ical' ], [ 'ical' => 1 ] )
			->archive( [ '{{ featured }}', 'ical' ], [ 'ical' => 1, 'featured' => true ] )
			->archive( [ '(\d{4}-\d{2}-\d{2})', 'ical' ], [ 'ical' => 1, 'eventDisplay' => 'day', 'eventDate' => '%1' ] )
			->archive( [ '(\d{4}-\d{2}-\d{2})', 'ical', 'featured' ], [ 'ical' => 1, 'eventDisplay' => 'day', 'eventDate' => '%1', 'featured' => true ] )

			// Taxonomy
			->tax( [ '{{ page }}', '(\d+)' ], [ 'eventDisplay' => 'list', 'paged' => '%2' ] )
			->tax( [ '{{ featured }}', '{{ page }}', '(\d+)' ], [ 'featured' => true, 'eventDisplay' => 'list', 'paged' => '%2' ] )
			->tax( [ '{{ month }}' ], [ 'eventDisplay' => 'month' ] )
			->tax( [ '{{ month }}', '{{ featured }}' ], [ 'eventDisplay' => 'month', 'featured' => true ] )
			->tax( [ '{{ list }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => 'list', 'paged' => '%2' ] )
			->tax( [ '{{ list }}', '{{ featured }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => 'list', 'featured' => true, 'paged' => '%2' ] )
			->tax( [ '{{ list }}' ], [ 'eventDisplay' => 'list' ] )
			->tax( [ '{{ list }}', '{{ featured }}' ], [ 'eventDisplay' => 'list', 'featured' => true ] )
			->tax( [ '{{ today }}' ], [ 'eventDisplay' => 'day' ] )
			->tax( [ '{{ today }}', '{{ featured }}' ], [ 'eventDisplay' => 'day', 'featured' => true ] )
			->tax( [ '{{ day }}', '(\d{4}-\d{2}-\d{2})' ], [ 'eventDisplay' => 'day', 'eventDate' => '%2' ] )
			->tax( [ '{{ day }}', '(\d{4}-\d{2}-\d{2})', '{{ featured }}' ], [ 'eventDisplay' => 'day', 'eventDate' => '%2', 'featured' => true ] )
			->tax( [ '(\d{4}-\d{2})' ], [ 'eventDisplay' => 'month', 'eventDate' => '%2' ] )
			->tax( [ '(\d{4}-\d{2})', '{{ featured }}' ], [ 'eventDisplay' => 'month', 'eventDate' => '%2', 'featured' => true ] )
			->tax( [ '(\d{4}-\d{2}-\d{2})' ], [ 'eventDisplay' => 'day', 'eventDate' => '%2' ] )
			->tax( [ '(\d{4}-\d{2}-\d{2})', '{{ featured }}' ], [ 'eventDisplay' => 'day', 'eventDate' => '%2', 'featured' => true ] )
			->tax( [ 'feed' ], [ 'eventDisplay' => 'list', 'feed' => 'rss2' ] )
			->tax( [ '{{ featured }}', 'feed' ], [ 'featured' => true, 'eventDisplay' => 'list', 'feed' => 'rss2' ] )
			->tax( [ 'ical' ], [ 'ical' => 1 ] )
			->tax( [ '{{ featured }}', 'ical' ], [ 'featured' => true, 'ical' => 1 ] )
			->tax( [ 'feed', '(feed|rdf|rss|rss2|atom)' ], [ 'feed' => '%2' ] )
			->tax( [ '{{ featured }}', 'feed', '(feed|rdf|rss|rss2|atom)' ], [ 'featured' => true, 'feed' => '%2' ] )
			->tax( [ '{{ featured }}' ], [ 'featured' => true, 'eventDisplay' => 'default' ] )
			->tax( [], [ 'eventDisplay' => 'default' ] )

			// Tag
			->tag( [ '{{ page }}', '(\d+)' ], [ 'eventDisplay' => 'list', 'paged' => '%2' ] )
			->tag( [ '{{ featured }}', '{{ page }}', '(\d+)' ], [ 'featured' => true, 'eventDisplay' => 'list', 'paged' => '%2' ] )
			->tag( [ '{{ month }}' ], [ 'eventDisplay' => 'month' ] )
			->tag( [ '{{ month }}', '{{ featured }}' ], [ 'eventDisplay' => 'month', 'featured' => true ] )
			->tag( [ '{{ list }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => 'list', 'paged' => '%2' ] )
			->tag( [ '{{ list }}', '{{ featured }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => 'list', 'featured' => true, 'paged' => '%2' ] )
			->tag( [ '{{ list }}' ], [ 'eventDisplay' => 'list' ] )
			->tag( [ '{{ list }}', '{{ featured }}' ], [ 'eventDisplay' => 'list', 'featured' => true ] )
			->tag( [ '{{ today }}' ], [ 'eventDisplay' => 'day' ] )
			->tag( [ '{{ today }}', '{{ featured }}' ], [ 'eventDisplay' => 'day', 'featured' => true ] )
			->tag( [ '{{ day }}', '(\d{4}-\d{2}-\d{2})' ], [ 'eventDisplay' => 'day', 'eventDate' => '%2' ] )
			->tag( [ '{{ day }}', '(\d{4}-\d{2}-\d{2})', '{{ featured }}' ], [ 'eventDisplay' => 'day', 'eventDate' => '%2', 'featured' => true ] )
			->tag( [ '(\d{4}-\d{2})' ], [ 'eventDisplay' => 'month', 'eventDate' => '%2' ] )
			->tag( [ '(\d{4}-\d{2})', '{{ featured }}' ], [ 'eventDisplay' => 'month', 'eventDate' => '%2', 'featured' => true ] )
			->tag( [ '(\d{4}-\d{2}-\d{2})' ], [ 'eventDisplay' => 'day', 'eventDate' => '%2' ] )
			->tag( [ '(\d{4}-\d{2}-\d{2})', '{{ featured }}' ], [ 'eventDisplay' => 'day', 'eventDate' => '%2', 'featured' => true ] )
			->tag( [ 'feed' ], [ 'eventDisplay' => 'list', 'feed' => 'rss2' ] )
			->tag( [ '{{ featured }}', 'feed' ], [ 'eventDisplay' => 'list', 'feed' => 'rss2', 'featured' => true ] )
			->tag( [ 'ical' ], [ 'ical' => 1 ] )
			->tag( [ '{{ featured }}', 'ical' ], [ 'featured' => true, 'ical' => 1 ] )
			->tag( [ 'feed', '(feed|rdf|rss|rss2|atom)' ], [ 'feed' => '%2' ] )
			->tag( [ '{{ featured }}', 'feed', '(feed|rdf|rss|rss2|atom)' ], [ 'featured' => true, 'feed' => '%2' ] )
			->tag( [ '{{ featured }}' ], [ 'featured' => true ] )
			->tag( [], [ 'eventDisplay' => 'default' ] );
	}

	/**
	 * Filters the post permalink to take 3rd party plugins into account.
	 *
	 * @param  string $permalink Permalink for the post
	 * @param  WP_Post $post Post Object
	 *
	 * @return string      Permalink with the language
	 */
	public function filter_post_type_link( $permalink, $post ) {
		$supported_post_types = array(
			Tribe__Events__Main::POSTTYPE,
			Tribe__Events__Main::VENUE_POST_TYPE,
			Tribe__Events__Main::ORGANIZER_POST_TYPE,
		);

		if ( ! in_array( $post->post_type, $supported_post_types ) ) {
			return $permalink;
		}

		$permalink = str_replace( self::PERCENT_PLACEHOLDER, '%', $permalink );

		/**
		 * Filters a supported post type permalink to allow third-party plugins to add or remove components.
		 *
		 * @param string $permalink The permalink for the post generated by the The Events Calendar.
		 * @param WP_Post $post The current post object.
		 * @param array $supported_post_types An array of post types supported by The Events Calendar.
		 */
		$permalink = apply_filters( 'tribe_events_post_type_permalink', $permalink, $post, $supported_post_types );

		return $permalink;
	}

	/**
	 * Checking if WPML is active on this WP
	 *
	 * @return boolean
	 */
	public function is_wpml_active() {
		return ! empty( $GLOBALS['sitepress'] ) && $GLOBALS['sitepress'] instanceof SitePress;
	}

	/**
	 * Get the base slugs for the Plugin Rewrite rules
	 *
	 * WARNING: Don't mess with the filters below if you don't know what you are doing
	 *
	 * @param  string $method Use "regex" to return a Regular Expression with the possible Base Slugs using l10n
	 * @return object         Return Base Slugs with l10n variations
	 */
	public function get_bases( $method = 'regex' ) {
		if ( ! empty( $this->bases ) ) {
			return (object) $this->bases;
		}

		$tec = Tribe__Events__Main::instance();

		$default_bases = [
			'month'    => [ 'month', $tec->monthSlug ],
			'list'     => [ 'list', $tec->listSlug ],
			'today'    => [ 'today', $tec->todaySlug ],
			'day'      => [ 'day', $tec->daySlug ],
			'tag'      => [ 'tag', $tec->tag_slug ],
			'tax'      => [ 'category', $tec->category_slug ],
			'page'     => [ 'page', esc_html_x( 'page', 'The "/page/" URL string component.', 'the-events-calendar' ) ],
			'single'   => [ tribe_get_option( 'singleEventSlug', 'event' ), $tec->rewriteSlugSingular ],
			'archive'  => [ tribe_get_option( 'eventsSlug', 'events' ), $tec->rewriteSlug ],
			'featured' => [ 'featured', $tec->featured_slug ],
		];

		/**
		 * If you want to modify the base slugs before the i18n happens filter this use this filter
		 * All the bases need to have a key and a value, they might be the same or not.
		 *
		 * Each value is an array of possible slugs: to improve robustness the "original" English
		 * slug is supported in addition to translated forms for month, list, today and day: this
		 * way if the forms are altered (whether through i18n or other custom mods) *after* links
		 * have already been promulgated, there will be less chance of visitors hitting 404s.
		 *
		 * The term "original" here for:
		 * - events
		 * - event
		 *
		 * Means that is a value that can be overwritten and relies on the user value entered on the
		 * options page.
		 *
		 * @param array $bases
		 */
		$bases = apply_filters( 'tribe_events_rewrite_base_slugs', $default_bases );

		// Remove duplicates (no need to have 'month' twice if no translations are in effect, etc)
		$bases            = array_map( 'array_unique', $bases );
		$unfiltered_bases = $bases;

		apply_filters_deprecated(
			'tribe_events_rewrite_i18n_languages',
			[ array_unique( array( 'en_US', get_locale() ) ) ],
			'5.1.5',
			'Deprecated in version 5.1.1, not used since version 4.2.'
		);

		// By default we load the Default and our plugin domains
		$domains = apply_filters( 'tribe_events_rewrite_i18n_domains', array(
			'default'             => true, // Default doesn't need file path
			'the-events-calendar' => $tec->plugin_dir . 'lang/',
		) );

		// In this moment set up the object locale bases too.
		$this->localized_bases = $this->get_localized_bases( $unfiltered_bases, $domains );

		// Merge the localized bases into the non-localized bases to ensure any following filter will apply to all.
		$bases = $this->merge_localized_bases( $bases );

		/**
		 * Use `tribe_events_rewrite_i18n_slugs_raw` to modify the raw version of the l10n slugs bases.
		 *
		 * This is useful to modify the bases before the method is taken into account.
		 *
		 * @param array  $bases   An array of rewrite bases that have been generated.
		 * @param string $method  The method that's being used to generate the bases; defaults to `regex`.
		 * @param array  $domains An associative array of language domains to use; these would be plugin or themes language
		 *                        domains with a `'plugin-slug' => '/absolute/path/to/lang/dir'`
		 */
		$bases = apply_filters( 'tribe_events_rewrite_i18n_slugs_raw', $bases, $method, $domains );

		// Again, make sure the bases are unique.
		$bases = array_map( 'array_unique', $bases );

		if ( 'regex' === $method ) {
			foreach ( $bases as $type => $base ) {
				// Escape all the Bases
				$base = array_map( 'preg_quote', $base );

				// Create the Regular Expression
				$bases[ $type ] = '(?:' . implode( '|', $base ) . ')';
			}
		}

		/**
		 * Use `tribe_events_rewrite_i18n_slugs` to modify the final version of the l10n slugs bases
		 *
		 * At this stage the method has been applied already and this filter will work with the
		 * finalized version of the bases.
		 *
		 * @param array  $bases   An array of rewrite bases that have been generated.
		 * @param string $method  The method that's being used to generate the bases; defaults to `regex`.
		 * @param array  $domains An associative array of language domains to use; these would be plugin or themes language
		 *                        domains with a `'plugin-slug' => '/absolute/path/to/lang/dir'`
		 */
		$bases = apply_filters( 'tribe_events_rewrite_i18n_slugs', $bases, $method, $domains );

		$this->bases = $bases;

		return (object) $bases;
	}

	/**
	 * Alias to `$this->add()` but adding the archive base first
	 *
	 * @param array|string $regex The regular expression to catch the URL
	 * @param array  $args  The arguments in which the regular expression "alias" to
	 *
	 * @return Tribe__Events__Rewrite
	 */
	public function archive( $regex, $args = array() ) {
		$default = array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
		);
		$args = array_filter( wp_parse_args( $args, $default ) );

		$regex = array_merge( array( $this->bases->archive ), (array) $regex );

		return $this->add( $regex, $args );
	}

	/**
	 * Alias to `$this->add()` but adding the singular base first
	 *
	 * @param array|string $regex The regular expression to catch the URL
	 * @param array  $args  The arguments in which the regular expression "alias" to
	 *
	 * @return Tribe__Events__Rewrite
	 */
	public function single( $regex, $args = array() ) {
		$regex = array_merge( array( $this->bases->single, '([^/]+)' ), (array) $regex );

		return $this->add( $regex, $args );
	}

	/**
	 * Alias to `$this->add()` but adding the taxonomy base first
	 *
	 * @param array|string $regex The regular expression to catch the URL
	 * @param array  $args  The arguments in which the regular expression "alias" to
	 *
	 * @return Tribe__Events__Rewrite
	 */
	public function tax( $regex, $args = array() ) {
		$default = array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			Tribe__Events__Main::TAXONOMY => '%1',
		);
		$args = array_filter( wp_parse_args( $args, $default ) );
		$regex = array_merge( array( $this->bases->archive, $this->bases->tax, '(?:[^/]+/)*([^/]+)' ), (array) $regex );

		return $this->add( $regex, $args );
	}

	/**
	 * Alias to `$this->add()` but adding the tag base first
	 *
	 * @param array|string $regex The regular expression to catch the URL
	 * @param array  $args  The arguments in which the regular expression "alias" to
	 *
	 * @return Tribe__Events__Rewrite
	 */
	public function tag( $regex, $args = array() ) {
		$default = array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'tag' => '%1',
		);
		$args = array_filter( wp_parse_args( $args, $default ) );
		$regex = array_merge( array( $this->bases->archive, $this->bases->tag, '([^/]+)' ), (array) $regex );

		return $this->add( $regex, $args );
	}

	protected function remove_hooks() {
		parent::remove_hooks();
		remove_filter( 'post_type_link', array( $this, 'filter_post_type_link' ), 15 );
	}

	protected function add_hooks() {
		parent::add_hooks();
		add_action( 'tribe_events_pre_rewrite', array( $this, 'generate_core_rules' ) );
		add_filter( 'post_type_link', array( $this, 'filter_post_type_link' ), 15, 2 );
		add_filter( 'url_to_postid', array( $this, 'filter_url_to_postid' ) );
		add_action( 'wp_loaded', [ $this, 'maybe_delayed_flush_rewrite_rules' ] );
	}

	/**
	 * When dealing with flush of rewrite rules we cannot do it from the activation process due to not all classes being
	 * loaded just yet. We flag a transient without expiration on activation so that on the next page load we flush the
	 * permalinks for the website.
	 *
	 * @see TEC::activate()
	 *
	 * @since 5.0.0.1
	 *
	 * @return void
	 */
	public function maybe_delayed_flush_rewrite_rules() {
		$should_flush_rewrite_rules = tribe_is_truthy( get_transient( static::KEY_DELAYED_FLUSH_REWRITE_RULES ) );

		if ( ! $should_flush_rewrite_rules ) {
			return;
		}

		delete_transient( static::KEY_DELAYED_FLUSH_REWRITE_RULES );

		flush_rewrite_rules();
	}

	/**
	 * Prevent url_to_postid to run if on the main events page to avoid
	 * query conflicts.
	 *
	 * @since 4.6.15
	 *
	 * @param string $url The URL from `url_to_postid()`
	 * @see [94328]
	 *
	 * @return int|string $url
	 */
	public function filter_url_to_postid( $url ) {

		$events_url = Tribe__Events__Main::instance()->getLink();

		// check if the site is using pretty permalinks
		if ( '' !== get_option( 'permalink_structure' ) ) {
			$url_query = @parse_url( $url, PHP_URL_QUERY );

			// Remove the "args" in case we receive any
			if ( ! empty( $url_query ) ) {
				$url = str_replace( '?' . $url_query, '', $url );
			} else {
				// Check if they're viewing the events page with pretty params
				if ( 0 === stripos( $url, $events_url ) ) {
					$url = $events_url;
				}
			}
		}

		if (
			$url === $events_url
			|| $url === Tribe__Events__Main::instance()->getLink( 'month' )
		) {
			return 0;
		}

		return $url;

	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_matcher_to_query_var_map() {
		$map = [
			'month'    => 'eventDisplay',
			'list'     => 'eventDisplay',
			'today'    => 'eventDisplay',
			'day'      => 'eventDisplay',
			'tag'      => 'tag',
			'tax'      => 'tribe_events_cat',
			'single'   => 'name',
			'archive'  => 'post_type',
			'featured' => 'featured',
		];

		/**
		 * Rewrite matchers for each display param, allowing external sources to create new params.
		 *
		 * @since  4.9.5
		 *
		 * @param  array  array of the current matchers for query vars.
		 * @param  self   $rewrite
		 */
		$map = apply_filters( 'tribe_events_rewrite_matchers_to_query_vars_map', $map, $this );

		return $map;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_localized_matchers() {
		$localized_matchers = parent::get_localized_matchers();

		// If possible add a `localized_slug` entry to each localized matcher to support multi-language.
		array_walk(
			$localized_matchers,
			function ( array &$localized_matcher ) {
				if ( isset( $localized_matcher['base'], $this->localized_bases[ $localized_matcher['base'] ] ) ) {
					$localized_matcher['localized_slug'] = $this->localized_bases[ $localized_matcher['base'] ];
				}
			}
		);

		// Handle the dates.
		$localized_matchers['(\d{4}-\d{2})']       = 'eventDate';
		$localized_matchers['(\d{4}-\d{2}-\d{2})'] = 'eventDate';

		// Handle the event archive possible variations.
		$localized_matchers = array_merge(
			$localized_matchers,
			$this->get_option_controlled_slug_entry( $localized_matchers, 'events', 'eventsSlug' )
		);

		return $localized_matchers;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_dynamic_matchers( array $query_vars ) {
		$bases = (array) $this->get_bases();
		$dynamic_matchers = parent::get_dynamic_matchers( $query_vars );

		// Handle The Events Calendar category.
		if ( isset( $query_vars['tribe_events_cat'] ) ) {
			$cat_regex = $bases['tax'];
			preg_match( '/^\(\?:(?<slugs>[^\\)]+)\)/', $cat_regex, $matches );
			if ( isset( $matches['slugs'] ) ) {
				$slugs = explode( '|', $matches['slugs'] );
				// The localized version is the last.
				$localized_slug = end( $slugs );

				/*
				 * Categories can be hierarchical and the path will be something like
				 * `/events/category/grand-parent/parent/child/list/page/2/`.
				 * If we can match the category to an existing one then let's make sure to build the hierarchical slug.
				 * We cast to comma-separated list to ensure multi-category queries will not resolve to a URL.
				 */
				$category_slug = Arr::to_list( $query_vars['tribe_events_cat'] );
				$category_term = get_term_by( 'slug', $category_slug, TEC::TAXONOMY );
				if ( $category_term instanceof WP_Term ) {
					$category_slug = get_term_parents_list(
						$category_term->term_id,
						TEC::TAXONOMY,
						[ 'format' => 'slug', 'separator' => '/', 'link' => false, 'inclusive' => true ]
					);
					// Remove leading/trailing slashes to get something like `grand-parent/parent/child`.
					$category_slug = trim( $category_slug, '/' );

					$dynamic_matchers[ "{$cat_regex}/(?:[^/]+/)*([^/]+)" ] = "{$localized_slug}/{$category_slug}";
				}
			}
		}

		// Where is iCal? It's handled by WordPress.

		return $dynamic_matchers;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_post_types() {
		return [ 'tribe_events', 'tribe_venue', 'tribe_organizer' ];
	}

	/**
	 * Overrides the base method, from common, to filter the parsed query variables and handle some cases related to
	 * the `eventDisplay` query variable.
	 *
	 * {@inheritDoc}
	 */
	public function parse_request( $url, array $extra_query_vars = [], $force = false ) {
		if ( ! has_filter( 'tribe_rewrite_parse_query_vars', [ $this, 'filter_rewrite_parse_query_vars' ] ) ) {
			add_filter( 'tribe_rewrite_parse_query_vars', [ $this, 'filter_rewrite_parse_query_vars' ], 10, 3 );
		}

		return parent::parse_request( $url, $extra_query_vars, $force );
	}

	/**
	 * Filters the parsed query vars to take the `eventDisplay` query var into account.
	 *
	 * When the query variables contain the `eventDisplay=default` variable and we have a different `eventDisplay` value
	 * in the query arguments, then use the query arguments `eventDisplay`.
	 *
	 * @since 4.9.5
	 *
	 * @param array  $query_vars The query variables, as parsed from the parent method.
	 * @param array  $unused     An array of extra query vars, passed as input into the parent method; not used.
	 * @param string $url        The input URL.
	 *
	 * @return array The updated parsed query variables.
	 */
	public function filter_rewrite_parse_query_vars( array $query_vars = [], array $unused = [], $url = '' ) {
		if ( empty( $url ) ) {
			return $query_vars;
		}

		parse_str( parse_url( $url, PHP_URL_QUERY ), $url_query_vars );

		if (
			! isset( $query_vars['eventDisplay'], $url_query_vars['eventDisplay'] )
			|| 'default' !== $query_vars['eventDisplay']
		) {
			return $query_vars;
		}

		$query_vars['eventDisplay'] = $url_query_vars['eventDisplay'];

		return $query_vars;
	}

	/**
	 * Adds an entry for an option controlled slug.
	 *
	 * E.g. the events archive can be changed from `/events` to somethings like `/trainings`.
	 *
	 * @since 4.9.13
	 *
	 * @param array  $localized_matchers An array of the current localized matchers.
	 * @param string $default_slug       The default slug for the option controlled slug; e.g. `events` for the events
	 *                                   archive.
	 * @param string $option_name        The name of the Tribe option that stores the modified slug, if any.
	 *
	 * @return array An entry to add to the localized matchers; this will be an empty array if there's no need to add
	 *               an entry..
	 */
	protected function get_option_controlled_slug_entry( array $localized_matchers, $default_slug, $option_name ) {
		$current_slug       = tribe_get_option( $option_name, $default_slug );
		$using_default_slug = $default_slug === $current_slug;

		$filter = static function ( $matcher ) use ( $default_slug ) {
			return isset( $matcher['query_var'], $matcher['localized_slugs'] )
				   && 'post_type' === $matcher['query_var']
				   && is_array( $matcher['localized_slugs'] );
		};

		$target_matcher = array_filter( $localized_matchers, $filter );
		$target_matcher = reset( $target_matcher );

		if ( $using_default_slug || false === $target_matcher ) {
			return [];
		}

		/**
		 * Add the slugs in the following order: default slug, option-controlled slug, localized slug.
		 */
		array_unshift( $target_matcher['localized_slugs'], $default_slug, $current_slug );

		// Make sure we do not have duplicated slugs.
		$target_matcher['localized_slugs'] = array_unique( $target_matcher['localized_slugs'] );

		// Create a replacement string that contains all of them.
		$all_slugs = array_unique( array_reverse( $target_matcher['localized_slugs'] ) );

		$entry = [
			// Create an entry for the localized slug to replace `(?:events)`.
			'(?:' . $default_slug . ')'              => [
				'query_var'       => 'post_type',
				'en_slug'         => $target_matcher['en_slug'],
				'localized_slugs' => $target_matcher['localized_slugs'],
			],
			// Create an entry for the localized slug to replace `(?:events|foo|bar)`.
			'(?:' . implode( '|', $all_slugs ) . ')' => [
				'query_var'       => 'post_type',
				'en_slug'         => $target_matcher['en_slug'],
				'localized_slugs' => $target_matcher['localized_slugs'],
			],
		];

		return $entry;
	}

	/**
	 * Returns the map of localized bases for the specified text domains.
	 *
	 * The bases are the ones used to build the permalinks, the domains are those of the currently activated plugins
	 * that include a localized rewrite component.
	 *
	 * @since 5.1.1
	 *
	 * @param array<string> $bases   The bases to set up the locale translation for.
	 * @param array<string> $domains A list of text domains belonging to the plugins currently active that handle and
	 *                               provide support for a localized rewrite component.
	 *
	 * @return array<string,string> A map relating the bases in their English, lowercase form to their current locale
	 *                              translated form.
	 */
	public function get_localized_bases( array $bases, array $domains ) {
		$locale             = get_locale();
		$cache_key          = __METHOD__ . md5( serialize( array_merge( $bases, $domains, [ $locale ] ) ) );
		$expiration_trigger = Cache_Listener::TRIGGER_GENERATE_REWRITE_RULES;

		$cached = tribe_cache()->get( $cache_key, $expiration_trigger, false );

		if ( false !== $cached ) {
			return $cached;
		}

		$flags           = I18n::COMPILE_STRTOLOWER;
		$localized_bases = tribe( 'tec.i18n' )
			->get_i18n_strings_for_domains( $bases, [ $locale ], $domains, $flags );

		$return = array_filter(
			array_map(
				static function ( $locale_base ) {
					return is_array( $locale_base ) ? end( $locale_base ) : false;
				},
				$localized_bases
			)
		);

		tribe_cache()->set( $cache_key, $return, DAY_IN_SECONDS, $expiration_trigger );

		return $return;
	}

	/**
	 * Enrich the bases adding the localized ones.
	 *
	 * Note: the method is not conditioned by the current locale (e.g. do not do this if current locale is en_US) to
	 * avoid issues with translation plugins that might filter the locale dynamically.
	 *
	 * @since 5.1.5
	 *
	 * @param array<array<string>> $bases The input bases, in the format `[<base> => [<version_1>, <version_2>, ...]]`.
	 *
	 * @return array<array<string>> The input bases modified to include the localized version of the bases.
	 *                              The format is the same as the input: `[<base> => [<version_1>, <version_2>, ...]]`.
	 */
	protected function merge_localized_bases( array $bases = [] ) {
		foreach ( $bases as $base_slug => $bases_list ) {
			if ( isset( $this->localized_bases[ $base_slug ] ) ) {
				// Deal with 1 or more bases in string or array form.
				$localized_bases = (array) $this->localized_bases[ $base_slug ];
				$localized_base  = reset( $localized_bases );
				$transliterated  = preg_replace( '/[^A-Za-z0-9]/', '', convert_chars( urldecode( $localized_base ) ) );
				$match           = array_search( $transliterated, $bases[ $base_slug ], true );
				if ( false === $match ) {
					$bases[ $base_slug ][] = $localized_base;
				} else {
					$bases[ $base_slug ][ $match ] = $localized_base;
				}
				$bases[ $base_slug ]   = array_unique( $bases[ $base_slug ] );
			}
		}

		return $bases;
	}
}
