<?php
/**
 * Class that answers the licensing questions the onboarding UI asks.
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding
 */

namespace TEC\Events\Admin\Onboarding;

use TEC\Common\LiquidWeb\Harbor\Licensing\Product_Collection;
use TEC\Common\LiquidWeb\Harbor\Licensing\Repositories\License_Repository;
use TEC\Common\LiquidWeb\Harbor\Licensing\Results\Product_Entry;
use Tribe__Dependency;

/**
 * Class License_Data
 *
 * Whether this site has activated the calendar, and where to send a user who
 * still has something to do about it. Sits alongside Data, which answers the
 * same shape of question about the calendar's own settings, so that the pages
 * consuming both stay about rendering rather than about Harbor.
 *
 * Every method degrades to "nothing to offer" — false, null or an empty string —
 * when the bundled Harbor library is older than the API it needs. Callers can
 * treat that as "hide the UI" without repeating the version checks themselves.
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding
 */
class License_Data {

	/**
	 * The product slug the calendar is licensed under in the Liquid Web catalog.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const PRODUCT_SLUG = 'the-events-calendar';

	/**
	 * Whether the bundled Harbor library can build an activation URL at all.
	 *
	 * Older copies predate the activation URL API. Callers guard on this, some
	 * to skip work they would only discard and some because they cannot proceed
	 * without it, so it lives here rather than as a duplicated function_exists()
	 * call at each site.
	 *
	 * We reach Harbor through its stable global functions rather than resolving
	 * its classes directly: the functions always resolve to the loaded,
	 * highest-version Harbor copy, so we are never at the mercy of whichever
	 * version our own bundled copy happens to be.
	 *
	 * @since TBD
	 *
	 * @return bool True when the activation URL functions are available.
	 */
	public function can_build_activation_url(): bool {
		return function_exists( 'lw_harbor_get_activation_url' )
			&& function_exists( 'lw_harbor_get_product_activation_url' );
	}

	/**
	 * Whether this site runs an active premium plugin a license would unlock.
	 *
	 * The calendar is free, and a license only unlocks the premium plugins built
	 * on top of it. Offering activation UI to a site running none of them would
	 * imply a license is needed to publish events at all, so onboarding asks this
	 * first and stays silent when the answer is no.
	 *
	 * Delegates to Common's shared answer rather than keeping a second list of
	 * premium plugins here. That answer is only reliable once plugins have
	 * registered themselves, which is long settled by the time an admin page
	 * renders.
	 *
	 * @since TBD
	 *
	 * @return bool True when a premium plugin is active on this site.
	 */
	public function has_active_premium_plugin(): bool {
		return Tribe__Dependency::instance()->has_active_premium_plugin();
	}

	/**
	 * Get the URL that sends a user to the portal to activate a license, for
	 * callers that only want one while the user still has something to do.
	 *
	 * Returns an empty string when there is nothing to be done: the site runs no
	 * premium plugin a license would unlock, the bundled Harbor library predates
	 * the activation URL API, or the site already holds a valid activated
	 * license. The onboarding wizard treats an empty string as "hide the button".
	 *
	 * @since TBD
	 *
	 * @param string $return_url Where the portal returns the user afterwards.
	 *
	 * @return string The activation URL, or an empty string when unavailable.
	 */
	public function get_activation_url( string $return_url ): string {
		// Most fundamental question first: on a site with nothing a license would
		// unlock there is no reason to ask anything else, or to offer any UI.
		if ( ! $this->has_active_premium_plugin() ) {
			return '';
		}

		// With no way to build a URL there is no reason to read licensing state to
		// find out whether we would have wanted one.
		if ( ! $this->can_build_activation_url() ) {
			return '';
		}

		if ( $this->is_activated() ) {
			return '';
		}

		return $this->build_activation_url( $return_url );
	}

	/**
	 * Build the activation URL whatever this site's activation state.
	 *
	 * The portal returns the user to the given address once they are done, so
	 * they pick up where they left off.
	 *
	 * When the stored license already covers the calendar, the URL is scoped to
	 * the product and tier so the portal pre-selects the right subscription.
	 * Otherwise the user lands on their subscription list and picks it
	 * themselves, which is the best that can be done without knowing what they
	 * hold.
	 *
	 * Callers that only want a URL for a user with something left to do should
	 * use get_activation_url() instead. This one answers the narrower question
	 * of whether a URL can be built at all, which the setup guide needs so it
	 * can show a step as done rather than hide it.
	 *
	 * @since TBD
	 *
	 * @param string $return_url Where the portal returns the user afterwards.
	 *
	 * @return string The activation URL, or an empty string when the bundled
	 *                Harbor library cannot build one.
	 */
	public function build_activation_url( string $return_url ): string {
		// Guarded inline (not only via can_build_activation_url()) so static
		// analysis can see the functions are called only when they exist.
		if (
			! function_exists( 'lw_harbor_get_activation_url' )
			|| ! function_exists( 'lw_harbor_get_product_activation_url' )
		) {
			return '';
		}

		$entitlement = $this->get_licensed_entry();

		if ( ! $entitlement instanceof Product_Entry ) {
			return lw_harbor_get_activation_url( $return_url );
		}

		return lw_harbor_get_product_activation_url(
			$entitlement->get_product_slug(),
			$entitlement->get_tier(),
			$return_url
		);
	}

	/**
	 * Get the URL of the in-WP page where a user manages their Liquid Web
	 * licenses.
	 *
	 * This is the Software Manager settings page Harbor registers, not the
	 * external portal: an activated user manages their products without leaving
	 * the site. Returns an empty string when Harbor is not active, so callers can
	 * treat that as "hide the button".
	 *
	 * @since TBD
	 *
	 * @return string The management page URL, or an empty string when unavailable.
	 */
	public function get_management_url(): string {
		if ( ! function_exists( 'lw_harbor_get_license_page_url' ) ) {
			return '';
		}

		return lw_harbor_get_license_page_url();
	}

	/**
	 * Whether this site already holds a valid, activated license for the calendar.
	 *
	 * Mirrors the check Harbor's own license UI makes: an entry counts only when
	 * it is activated against this domain and its entitlement is currently
	 * valid. A key that exists but has not been activated here does not count,
	 * because the user still has something to do in the portal.
	 *
	 * @since TBD
	 *
	 * @return bool True when the calendar is licensed and activated on this site.
	 */
	public function is_activated(): bool {
		$products = $this->get_products();

		if ( ! $products instanceof Product_Collection ) {
			return false;
		}

		$entry = $products->get_activated_entry( self::PRODUCT_SLUG );

		return $entry instanceof Product_Entry && $entry->is_valid();
	}

	/**
	 * Get the licensed entry for the calendar, whatever its activation state.
	 *
	 * Used to scope the activation URL. The tier is known as soon as the key
	 * covers the product, well before it has been activated on this domain, so
	 * this deliberately does not filter on activation.
	 *
	 * Returns the first entry when a key covers several tiers. Picking between
	 * them is the portal's job, and it still receives the product either way.
	 *
	 * @since TBD
	 *
	 * @return Product_Entry|null The entry, or null when the key does not cover the calendar.
	 */
	protected function get_licensed_entry(): ?Product_Entry {
		$products = $this->get_products();

		if ( ! $products instanceof Product_Collection ) {
			return null;
		}

		$entries = $products->get_all_by_slug( self::PRODUCT_SLUG );

		return $entries ? reset( $entries ) : null;
	}

	/**
	 * Get the licensed products Harbor holds for this site.
	 *
	 * Harbor returns a WP_Error when its last fetch failed, and null when it has
	 * never fetched. Neither is something the onboarding UI can act on, so both
	 * are flattened to null here and read by callers as "no license".
	 *
	 * @since TBD
	 *
	 * @return Product_Collection|null The products, or null when unavailable.
	 */
	protected function get_products(): ?Product_Collection {
		if ( ! class_exists( License_Repository::class ) ) {
			return null;
		}

		$products = tribe( License_Repository::class )->get_products();

		return $products instanceof Product_Collection ? $products : null;
	}
}
