<?php
/**
 * Handles compatibility with Restrict Content Pro plugin.
 *
 * @package Tribe\Events\Integrations\Restrict_Content_Pro
 * @since TBD
 */

namespace Tribe\Events\Integrations\Restrict_Content_Pro;

use Tribe__Events__Main as TEC;

/**
 * Integrations with Restrict Content Pro plugin.
 *
 * @package Tribe\Events\Integrations
 *
 * @since TBD
 */

class Service_Provider {

	/**
	 * Hooks all the required methods for Restrict Content Pro usage on our code.
	 *
	 * @since TBD
	 *
	 * @return void  Action hook with no return.
	 */
	public function hook() {
		// Bail when not on V2.
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}

		// Bail if RCP isn't active.
		if ( ! function_exists( 'rcp_user_can_access' ) ) {
			return;
		}

		// add hooks
		add_filter( 'tribe_template_done', array( $this, 'filter_view_events' ), 20, 3 );
	}

	/**
	 * Filter displayed events based on RCP restrictions.
	 *
	 * This should effect all calendar views.
	 *
	 * $done is null by default, if you return _anything_ other than null, the template won't display.
 	 * There are actually 4 params passed, but the last is $echo - useless for our purposes so we don't include it.
	 *
	 * @since TBD
	 *
	 * @param string  null     Whether to continue displaying the template or not.
	 * @param array   $name    Template name. Unused although it could be used for targeting a specific template.
	 * @param array   $context Any context data you need to expose to this file.
	 *
	 * @return null|bool Null to display the event, boolean false to not.
	 */
	public function filter_view_events( $done, $name, $context ) {
		// Don't call a function we can't access.
		if ( ! function_exists( 'rcp_user_can_access' ) ) {
			return $done;
		}

		// No event in the context. We're using this to filter out the "larger" view templates, etc
		if ( empty( $context['event'] ) ) {
			return $done;
		}

		// Avoid issues with single event page.
		if ( is_single( TEC::POSTTYPE ) ) {
			return $done;
		}

		// Get the event.
		$event = $context['event'];

		// Malformed event?
		if ( empty( $event ) || ! $event instanceof \WP_Post ) {
			return $done;
		}

		// Can current user access the event?
		if ( rcp_user_can_access( get_current_user_id(), $event->ID ) ) {
			return $done;
		}

		// No? return something other than null - the event won't display.
		return false;
	}

}
