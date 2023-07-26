<?php
/**
 * Handles compatibility with Restrict Content Pro plugin.
 *
 * @package Tribe\Events\Integrations\Restrict_Content_Pro
 * @since 6.0.2
 */

namespace Tribe\Events\Integrations\Restrict_Content_Pro;

use Tribe__Events__Main as TEC;

/**
 * Integrations with Restrict Content Pro plugin.
 *
 * @package Tribe\Events\Integrations
 *
 * @since 6.0.2
 */

class Service_Provider {

	/**
	 * Option slug used for storing the choice to apply post-type restrictions to the calendar views.
	 *
	 * @since 6.0.2
	 *
	 * @var string
	 */
	protected static $option_slug = 'tec_events_rcp_hide_on_views';

	/**
	 * Hooks all the required methods for Restrict Content Pro usage on our code.
	 *
	 * @since 6.0.2
	 *
	 * @return void  Action hook with no return.
	 */
	public function hook() {
		// Bail when not on V2.
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}

		// add actions
		add_action( 'rcp_post_type_restrictions', [ $this, 'add_rcp_post_type_restrictions' ], 10, 2 );
		add_action( 'rcp_save_post_type_restrictions', [ $this, 'rcp_save_post_type_restrictions' ], 20 );
		add_action( 'rcp_action_save_post_type_restrictions', [ $this, 'rcp_save_post_type_restrictions' ], 20 );


		// add hooks
		add_filter( 'tribe_template_done', [ $this, 'filter_view_events' ], 20, 3 );
	}

	/**
	 * Adds control for applying post-type restrictions to the calendar views.
	 *
	 * @since 6.0.2
	 */
	public function add_rcp_post_type_restrictions() {
		$option = tribe_get_option( self::$option_slug, false );
		?>
			<p>
				<label for="<?php echo esc_attr( self::$option_slug ); ?>">
					<input type="checkbox" name="<?php echo esc_attr( self::$option_slug ); ?>" id="<?php echo esc_attr( self::$option_slug ); ?>" value="1" <?php checked( $option, '1' ); ?>>
					&nbsp;<?php echo esc_html_x( "Hide restricted events on calendar views.", 'Text label for control to hide restricted events on main calendar views.', 'the-events-calendar' ); ?>
				</label>
			</p>
		<?php
	}

	/**
	 * Saves the value for applying post-type restrictions to the calendar views.
	 *
	 * @since 6.0.2
	 *
	 * @return void
	 */
	public function rcp_save_post_type_restrictions() {
		if ( empty( tribe_get_request_var( self::$option_slug ) ) ) {
			tribe_remove_option( self::$option_slug );
			return;
		}

		tribe_update_option( self::$option_slug, tribe_get_request_var( self::$option_slug ) );
	}

	/**
	 * Filter displayed events based on RCP restrictions.
	 *
	 * This should effect all calendar views.
	 *
	 * $done is null by default, if you return _anything_ other than null, the template won't display.
 	 * There are actually 4 params passed, but the last is $echo - useless for our purposes so we don't include it.
	 *
	 * @since 6.0.2
	 *
	 * @param string  null     Whether to continue displaying the template or not.
	 * @param array   $name    Template name. Unused although it could be used for targeting a specific template.
	 * @param array   $context Any context data you need to expose to this file.
	 *
	 * @return null|bool Null to display the event, boolean false to not.
	 */
	public function filter_view_events( $done, $name, $context ) {
		// Obey the setting.
		if ( ! tribe_get_option( self::$option_slug, false ) ) {
			return $done;
		}

		// No event in the context. We're using this to filter out the "larger" view templates, etc
		if ( empty( $context['event'] ) ) {
			return $done;
		}

		// Avoid issues with single event page. RCP handles that just fine.
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

	/**
	 * Get the option slug.
	 *
	 * @since 6.0.2
	 *
	 * @return string $option_slug The option slug for this setting.
	 */
	public static function get_slug() {
		return self::$option_slug;
	}

}
