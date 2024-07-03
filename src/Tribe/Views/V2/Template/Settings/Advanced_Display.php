<?php
/**
 * Initializer for The Events Calendar for the template settings
 *
 * Can be changed on Events > Settings > Display > Advanced Settings
 *
 * @since   4.9.11
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2\Template\Settings;

use Tribe\Events\Views\V2\View;

class Advanced_Display {

	/**
	 * Key for the Before HTML settings on the tribe option.
	 *
	 * @since  4.9.11
	 *
	 * @var string
	 */
	public static $key_before_events_html = 'tribeEventsBeforeHTML';

	/**
	 * Key for the After HTML settings on the tribe option.
	 *
	 * @since  4.9.11
	 *
	 * @var string
	 */
	public static $key_after_events_html = 'tribeEventsAfterHTML';

	/**
	 * Fetches the "HTML before event content" from the calendar settings, which can be found under
	 * Events > Settings > Display tab. Applies all the required methods for proper usage and returns it.
	 *
	 * @since  4.9.11
	 *
	 * @param  View_Interface|null $view Instance of the view we are getting this for.
	 *
	 * @return string HTML after all the methods have been applied to it.
	 */
	public function get_before_events_html( $view = null ) {
		$before = stripslashes( tribe_get_option( static::$key_before_events_html, '' ) );
		$before = wptexturize( $before );
		$before = convert_chars( $before );
		$before = wpautop( $before );
		$before = do_shortcode( stripslashes( shortcode_unautop( $before ) ) );
		$before = force_balance_tags( $before );

		/**
		 * Filter imported from V1 of Views, kept since there was no requirement to
		 * remove the backwards compatibility here.
		 *
		 * @since  ???  Unsure which version this was introduced to the codebase.
		 * @since  4.9.11  Moved to the class method in V2, and removed Loader HTML.
		 *
		 * @param  string              $before HTML after passing all the params.
		 * @param  View_Interface|null $view   Instance of the view we are getting this for.
		 */
		$before = apply_filters( 'tribe_events_before_html', $before, $view );

		/**
		 * Filter imported from V1 of Views, kept since there was no requirement to
		 * remove the backwards compatibility here.
		 *
		 * @since  4.9.11
		 *
		 * @param  string              $before  HTML after passing all the params.
		 * @param  View_Interface|null $view    Instance of the view we are getting this for.
		 */
		$before = apply_filters( 'tribe_events_views_v2_view_before_events_html', $before, $view );

		return $before;
	}

	/**
	 * Fetches the "HTML after event content" from the calendar settings, which can be found under
	 * Events > Settings > Display tab. Applies all the required methods for proper usage and returns it.
	 *
	 * @since  4.9.11
	 *
	 * @param  View_Interface|null $view Instance of the view we are getting this for.
	 *
	 * @return string HTML after all the methods have been applied to it.
	 */
	public function get_after_events_html( $view = null ) {
		$after = stripslashes( tribe_get_option( static::$key_after_events_html, '' ) );
		$after = wptexturize( $after );
		$after = convert_chars( $after );
		$after = wpautop( $after );
		$after = do_shortcode( stripslashes( shortcode_unautop( $after ) ) );
		$after = force_balance_tags( $after );

		/**
		 * Filter imported from V1 of Views, kept since there was no requirement to
		 * remove the backwards compatibility here.
		 *
		 * @since  ???  Unsure which version this was introduced to the codebase.
		 * @since  4.9.11  Moved to a class method in V2.
		 *
		 * @param  string              $after  HTML after passing all the params.
		 * @param  View_Interface|null $view   Instance of the view we are getting this for.
		 */
		$after = apply_filters( 'tribe_events_after_html', $after, $view );

		/**
		 * Filter imported from V1 of Views, kept since there was no requirement to
		 * remove the backwards compatibility here.
		 *
		 * @since  4.9.11
		 *
		 * @param  string              $after HTML after passing all the params.
		 * @param  View_Interface|null $view  Instance of the view we are getting this for.
		 */
		$after = apply_filters( 'tribe_events_views_v2_view_after_events_html', $after, $view );

		return $after;
	}
}
