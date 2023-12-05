<?php
/**
 * Manages the legacy view removal and messaging.
 *
 * @since 6.2.3
 *
 * @package TEC\Events\SEO
 */

 namespace TEC\Events\SEO;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use Tribe\Events\Views\V2\View_Interface;
use Tribe\Events\Views\V2\Views;
use Tribe__Date_Utils as Dates;
use Tribe__Context;

/**
 * Class Provider
 *
 * @since 6.2.3

 * @package TEC\Events\SEO
 */
class Controller extends Controller_Contract {
	/**
	 * @inerhitDoc
	 */
	public function do_register(): void {
		$this->container->singleton( static::class, $this );

		// We hook an extra layer
		add_action( 'wp', [ $this, 'hook_issue_noindex' ] );
	}

	/**
	 * @inerhitDoc
	 */
	public function unregister(): void {
		remove_action( 'wp', [ $this, 'hook_issue_noindex' ] );
		remove_action( 'tribe_views_v2_after_setup_loop', [ $this, 'issue_noindex' ] );
	}

	/**
	 * Hooked to wp action to check if we should bail before hooking the full noindex logic.
	 *
	 * @since 6.2.6
	 *
	 * @return void
	 */
	public function hook_issue_noindex() {
		if ( is_home() || is_front_page() ) {
			return;
		}

		if ( is_single() ) {
			$post_type = get_post_type();

			$linked_post_types           = (array) \Tribe__Events__Linked_Posts::instance()->get_linked_post_types();
			$robots_enabled_post_types   = array_keys( $linked_post_types );

			/**
			 * Allows for the filtering of post types that should allow noindex tags.
			 *
			 * @since 6.2.6
			 *
			 * @param array  $robots_enabled_post_types The post types that should allow noindex tags.
			 * @param string $post_type                 The current post type.
			 */
			$robots_enabled_post_types = (array) apply_filters( 'tec_events_seo_robots_meta_allowable_post_types', $robots_enabled_post_types, $post_type );

			if ( ! in_array( $post_type, $robots_enabled_post_types ) ) {
				return;
			}
		}

		add_action( 'tribe_views_v2_after_setup_loop', [ $this, 'issue_noindex' ] );
	}

	/**
	 * Runs on the "wp" action. Inspects the main query object and if it relates to an events
	 * query makes a decision to add a noindex meta tag based on whether events were returned
	 * in the query results or not.
	 *
	 * @since 3.12.4
	 * @since 6.0.0 Relies on v2 code.
	 *
	 * Disabling this behavior completely is possible with:
	 *
	 *     add_filter( 'tec_events_seo_robots_meta_include', '__return_false' );
	 *
	 *  Always adding the noindex meta tag for all event views is possible with:
	 *
	 *     add_filter( 'tec_events_seo_robots_meta_include', '__return_true' );
	 *
	 *  Always adding the noindex meta tag for a specific event view is possible with:
	 *
	 *     add_filter( "tec_events_seo_robots_meta_include_{$view}", '__return_true' );
	 *
	 *  Where `$view` above is the view slug, e.g. `month`, `day`, `list`, etc.
	 *
	 * @param View_Interface $instance The view instance.
	 */
	public function issue_noindex( $instance ): void {
		$context = $instance->get_context();
		$view    = $context->get( 'view' );

		// If we don't have a view, bail.
		if ( empty( $view ) ) {
			return;
		}

		// Let's avoid adding noindex to shortcode views.
		if ( $context->get( 'shortcode' ) ) {
			return;
		}

		// If we have a view class and it is a subclass of the By_Day_View class (grid views), default to including noindex, nofollow.
		if ( $instance instanceof Views\By_Day_View ) {
			$do_include = true;
			add_filter( 'tec_events_seo_robots_meta_content', [ $this, 'get_noindex_nofollow' ] );
		} else {
			$do_include = $this->should_add_no_index_for_list_based_views( $instance );
		}

		/**
		 * Allows filtering of if a noindex meta tag will be set for the current event view.
		 *
		 * @since 6.2.3
		 * @deprecated 6.2.6
		 *
		 * @var bool $do_include Whether to add the noindex meta tag.
		 */
		$do_include = (bool) apply_filters_deprecated( 'tec_events_add_no_index_meta_tag', [ $do_include ], '6.2.6', 'tec_events_seo_robots_meta_include' );

		/**
		 * Filter to disable the noindex meta tag on Views V2.
		 *
		 * @since 6.2.6
		 *
		 * @param bool $do_include Whether to add the noindex, nofollow meta tag or not.
		 * @param string $view The current view slug.
		 */
		$do_include = (bool) apply_filters( 'tec_events_seo_robots_meta_include', $do_include, $view );

		/**
		 * Filter to disable the noindex meta tag on Views V2 for a specific view.
		 *
		 * @since 6.2.6
		 *
		 * @param bool $do_include Whether to add the noindex, nofollow meta tag or not.
		 * @param string $view The current view slug.
		 */
		$do_include = (bool) apply_filters( "tec_events_seo_robots_meta_include_{$view}", $do_include, $view );

		if ( ! $do_include ) {
			return;
		}

		/**
		 * Determines if a noindex meta tag will be set for the current event view.
		 *
		 * @since  3.12.4
		 *
		 * @var bool $do_include
		 * @var Tribe__Context $context The view context.
		 */
		$do_include = apply_filters_deprecated( 'tribe_events_add_no_index_meta', [ $do_include, $context ], '6.2.6', 'tec_events_seo_robots_meta_include' );

		/**
		 * Determines if a noindex meta tag will be set for a specific event view.
		 *
		 * @since 6.2.3
		 *
		 * @var bool $add_noindex
		 * @var Tribe__Context $context The view context.
		 */
		$do_include = apply_filters_deprecated( "tec_events_{$view}_add_no_index_meta", [ $do_include, $context ], '6.2.6', "tec_events_seo_robots_meta_include_{$view}" );

		if ( $do_include ) {
			if ( did_action( 'wp_head' ) ) {
				ob_start();
				$this->print_noindex_meta();
				$meta_html = trim( ob_get_clean() );
				?>
				<script>
					document.head.insertAdjacentHTML( 'beforeend', '<?php echo $meta_html; ?>' );
				</script>
				<?php
			} else {
				add_action( 'wp_head', [ $this, 'print_noindex_meta' ] );
			}
		}
	}

	/**
	 * Determine if a nonindex should be added for list based views that don't have events.
	 *
	 * @since 6.2.6
	 *
	 * @param View_Interface $instance The view instance.
	 *
	 * @return bool
	 */
	protected function should_add_no_index_for_list_based_views( $instance ): bool {
		$context = $instance->get_context();

		if ( ! $context->is( 'tec_post_type' ) ) {
			return false;
		}

		$events = $instance->get_repository();

		// No posts = no index.
		$count = $events->count();
		return $count <= 0;
	}

	/**
	 * Get the noindex, follow string.
	 *
	 * @since 6.2.6
	 *
	 * @return string
	 */
	public function get_noindex_follow(): string {
		return 'noindex, follow';
	}

	/**
	 * Get the noindex, nofollow string.
	 *
	 * @since 6.2.6
	 *
	 * @return string
	 */
	public function get_noindex_nofollow(): string {
		return 'noindex, nofollow';
	}

	/**
	 * Prints a "noindex,follow" robots tag.
	 *
	 * @since 6.2.3
	 */
	public function print_noindex_meta() :void {
		$robots_meta_content = $this->get_noindex_follow();

		/**
		 * Filter to disable the noindex meta tag on Views V2.
		 *
		 * @since 6.2.6
		 *
		 * @param string $robots_meta_content The contents of the robots meta tag.
		 * @param string $view The current view slug.
		 */
		$robots_meta_content = (string) apply_filters( 'tec_events_seo_robots_meta_content', $robots_meta_content );

		if ( ! $robots_meta_content ) {
			return;
		}

		$noindex_meta = sprintf(
			'<meta name="robots" id="tec_noindex" content="%s" />',
			esc_attr( $robots_meta_content )
		). "\n";

		/**
		 * Filters the noindex meta tag.
		 *
		 * @since 6.2.3
		 *
		 * @param string $noindex_meta
		 */
		$noindex_meta = apply_filters( 'tec_events_no_index_meta', $noindex_meta );

		echo wp_kses(
			$noindex_meta,
			[
				'meta' => [
					'id'      => true,
					'name'    => true,
					'content' => true,
				],
			]
		);
	}

	/**
	 * Returns the end date time object read from the current context.
	 *
	 * @since 6.2.3
	 *
	 * @param [type] $view
	 * @param [type] $start_date
	 * @param [type] $context
	 *
	 * @return DateTime|false A DateTime object or `false` if a DateTime object could not be built.
	 */
	public function get_end_date( $view, $start_date, $context ) {
		$end_date = $context->get( 'end_date' );

		switch ( $view ) {
			case 'day':
				$end_date = clone $start_date;
				$end_date->modify( '+1 day' );
				return $end_date;
				break;
			case 'week':
				$end_date = clone $start_date;
				$end_date->modify( '+6 days' );
				return $end_date;
				break;
			case 'month':
				$end_date = clone $start_date;
				$end_date->modify( '+1 month' );
				return $end_date;
				break;
			default:
				return Dates::build_date_object( $end_date );
				break;
		}
	}
}
