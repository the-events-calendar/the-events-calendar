<?php
// Don't load directly
defined( 'WPINC' ) or die;

/**
 * Rewrite Configuration Class
 * Permalinks magic Happens over here!
 */
class Tribe__Events__Rewrite extends  Tribe__Rewrite {
	/**
	 * Static singleton variable
	 * @var self
	 */
	public static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * Just dont...
	 */
	private function __construct() {}

	/**
	 * Generate the Rewrite Rules
	 *
	 * @param  WP_Rewrite $wp_rewrite WordPress Rewrite that will be modified, pass it by reference (&$wp_rewrite)
	 */
	public function filter_generate( WP_Rewrite $wp_rewrite ) {
		parent::filter_generate($wp_rewrite);

		/**
		 * Backwards Compatibility filter, this filters the WP Rewrite Rules.
		 * @todo  Check if is worth deprecating this hook
		 */
		$wp_rewrite->rules = apply_filters( 'tribe_events_rewrite_rules', $this->rules + $wp_rewrite->rules, $this );
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
			->single( array( '(\d{4}-\d{2}-\d{2})' ), array( Tribe__Events__Main::POSTTYPE => '%1', 'eventDate' => '%2' ) )
			->single( array( '(\d{4}-\d{2}-\d{2})', 'embed' ), array( Tribe__Events__Main::POSTTYPE => '%1', 'eventDate' => '%2', 'embed' => 1 ) )
			->single( array( '{{ all }}' ), array( Tribe__Events__Main::POSTTYPE => '%1', 'post_type' => Tribe__Events__Main::POSTTYPE, 'eventDisplay' => 'all' ) )

			->single( array( '(\d{4}-\d{2}-\d{2})', 'ical' ), array( Tribe__Events__Main::POSTTYPE => '%1', 'eventDate' => '%2', 'ical' => 1 ) )
			->single( array( 'ical' ), array( 'ical' => 1, 'name' => '%1', 'post_type' => Tribe__Events__Main::POSTTYPE ) )

			// Archive
			->archive( array( '{{ page }}', '(\d+)' ), array( 'eventDisplay' => 'list', 'paged' => '%1' ) )
			->archive( array( '(feed|rdf|rss|rss2|atom)' ), array( 'eventDisplay' => 'list', 'feed' => '%1' ) )
			->archive( array( '{{ month }}' ), array( 'eventDisplay' => 'month' ) )
			->archive( array( '{{ list }}', '{{ page }}', '(\d+)' ), array( 'eventDisplay' => 'list', 'paged' => '%1' ) )
			->archive( array( '{{ list }}' ), array( 'eventDisplay' => 'list' ) )
			->archive( array( '{{ today }}' ), array( 'eventDisplay' => 'day' ) )
			->archive( array( '(\d{4}-\d{2})' ), array( 'eventDisplay' => 'month', 'eventDate' => '%1' ) )
			->archive( array( '(\d{4}-\d{2}-\d{2})' ), array( 'eventDisplay' => 'day', 'eventDate' => '%1' ) )
			->archive( array(), array( 'eventDisplay' => 'default' ) )

			->archive( array( 'ical' ), array( 'ical' => 1 ) )
			->archive( array( '(\d{4}-\d{2}-\d{2})', 'ical' ), array( 'ical' => 1, 'eventDisplay' => 'day', 'eventDate' => '%1' ) )

			// Taxonomy
			->tax( array( '{{ page }}', '(\d+)' ), array( 'eventDisplay' => 'list', 'paged' => '%2' ) )
			->tax( array( '{{ month }}' ), array( 'eventDisplay' => 'month' ) )
			->tax( array( '{{ list }}', '{{ page }}', '(\d+)' ), array( 'eventDisplay' => 'list', 'paged' => '%2' ) )
			->tax( array( '{{ list }}' ), array( 'eventDisplay' => 'list' ) )
			->tax( array( '{{ today }}' ), array( 'eventDisplay' => 'day' ) )
			->tax( array( '{{ day }}', '(\d{4}-\d{2}-\d{2})' ), array( 'eventDisplay' => 'day', 'eventDate' => '%2' ) )
			->tax( array( '(\d{4}-\d{2})' ), array( 'eventDisplay' => 'month', 'eventDate' => '%2' ) )
			->tax( array( '(\d{4}-\d{2}-\d{2})' ), array( 'eventDisplay' => 'day', 'eventDate' => '%2' ) )
			->tax( array( 'feed' ), array( 'eventDisplay' => 'list', 'feed' => 'rss2' ) )
			->tax( array( 'ical' ), array( 'ical' => 1 ) )
			->tax( array( 'feed', '(feed|rdf|rss|rss2|atom)' ), array( 'feed' => '%2' ) )
			->tax( array(), array( 'eventDisplay' => 'default' ) )

			// Tag
			->tag( array( '{{ page }}', '(\d+)' ), array( 'eventDisplay' => 'list', 'paged' => '%2' ) )
			->tag( array( '{{ month }}' ), array( 'eventDisplay' => 'month' ) )
			->tag( array( '{{ list }}', '{{ page }}', '(\d+)' ), array( 'eventDisplay' => 'list', 'paged' => '%2' ) )
			->tag( array( '{{ list }}' ), array( 'eventDisplay' => 'list' ) )
			->tag( array( '{{ today }}' ), array( 'eventDisplay' => 'day' ) )
			->tag( array( '{{ day }}', '(\d{4}-\d{2}-\d{2})' ), array( 'eventDisplay' => 'day', 'eventDate' => '%2' ) )
			->tag( array( '(\d{4}-\d{2})' ), array( 'eventDisplay' => 'month', 'eventDate' => '%2' ) )
			->tag( array( '(\d{4}-\d{2}-\d{2})' ), array( 'eventDisplay' => 'day', 'eventDate' => '%2' ) )
			->tag( array( 'feed' ), array( 'eventDisplay' => 'list', 'feed' => 'rss2' ) )
			->tag( array( 'ical' ), array( 'ical' => 1 ) )
			->tag( array( 'feed', '(feed|rdf|rss|rss2|atom)' ), array( 'feed' => '%2' ) )
			->tag( array(), array( 'eventDisplay' => 'default' ) );
	}

	/**
	 * When WPML is active we need to return the language Query Arg
	 *
	 * @param  string $uri Permalink for the post
	 * @param  WP_Post $post Post Object
	 *
	 * @return string      Permalink with the language
	 */
	public function filter_post_type_link( $permalink, $post ) {
		// When creating the link we need to re-do the Percent Placeholder
		$permalink = str_replace( self::PERCENT_PLACEHOLDER, '%', $permalink );

		if ( ! $this->is_wpml_active() || empty( $_GET['lang'] ) ) {
			return $permalink;
		}

		$lang = wp_strip_all_tags( $_GET['lang'] );

		return add_query_arg( array( 'lang' => $lang ), $permalink );
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
		$tec = Tribe__Events__Main::instance();

		/**
		 * If you want to modify the base slugs before the i18n happens filter this use this filter
		 * All the bases need to have a key and a value, they might be the same or not.
		 *
		 * Each value is an array of possible slugs: to improve robustness the "original" English
		 * slug is supported in addition to translated forms for month, list, today and day: this
		 * way if the forms are altered (whether through i18n or other custom mods) *after* links
		 * have already been promulgated, there will be less chance of visitors hitting 404s.
		 *
		 * @var array $bases
		 */
		$bases = apply_filters( 'tribe_events_rewrite_base_slugs', array(
			'month' => array( 'month', $tec->monthSlug ),
			'list' => array( 'list', $tec->listSlug ),
			'today' => array( 'today', $tec->todaySlug ),
			'day' => array( 'day', $tec->daySlug ),
			'tag' => array( 'tag', $tec->tag_slug ),
			'tax' => array( 'category', $tec->category_slug ),
			'page' => (array) 'page',
			'all' => (array) 'all',
			'single' => (array) Tribe__Settings_Manager::get_option( 'singleEventSlug', 'event' ),
			'archive' => (array) Tribe__Settings_Manager::get_option( 'eventsSlug', 'events' ),
		) );

		// Remove duplicates (no need to have 'month' twice if no translations are in effect, etc)
		$bases = array_map( 'array_unique', $bases );

		// By default we always have `en_US` to avoid 404 with older URLs
		$languages = apply_filters( 'tribe_events_rewrite_i18n_languages', array_unique( array( 'en_US', get_locale() ) ) );

		// By default we load the Default and our plugin domains
		$domains = apply_filters( 'tribe_events_rewrite_i18n_domains', array(
			'default' => true, // Default doesn't need file path
			'the-events-calendar' => $tec->pluginDir . 'lang/',
		) );

		// If WPML exists we treat the multiple languages
		if ( $this->is_wpml_active() ) {
			global $sitepress;

			// Grab all languages
			$langs = $sitepress->get_active_languages();

			foreach ( $langs as $lang ) {
				$languages[] = $sitepress->get_locale( $lang['code'] );
			}

			// Prevent Duplicates and Empty langs
			$languages = array_filter( array_unique( $languages ) );

			// Query the Current Language
			$current_locale = $sitepress->get_locale( $sitepress->get_current_language() );

			// Get the strings on multiple Domains and Languages
			$bases = $tec->get_i18n_strings( $bases, $languages, $domains, $current_locale );
		}

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
		 */
		return (object) apply_filters( 'tribe_events_rewrite_i18n_slugs', $bases, $method );
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
	}
}
