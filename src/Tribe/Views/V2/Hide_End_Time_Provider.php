<?php
/**
 * Handles hooking all the actions and filters used by the Hide End Time module.
 *
 * @since 6.6.3
 *
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2;

use TEC\Events\Views\Modifiers\Hide_End_Time_Modifier;
use Tribe__Template;
use TEC\Common\Contracts\Service_Provider;


/**
 * Class Hide_End_Time_Provider
 *
 * @since 6.6.3
 *
 * @package Tribe\Events\Views\V2
 */
class Hide_End_Time_Provider extends Service_Provider {
	/**
	 * @var Hide_End_Time_Modifier The modifier to hide the end time.
	 */
	protected Hide_End_Time_Modifier $end_time_modifier;

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.6.3
	 */
	public function register() {
		// One of two possibilities for our view rendering initialization.
		add_action( 'tribe_views_v2_after_setup_loop', [ $this, 'hide_event_end_time' ], 2 );
		add_action( 'tribe_events_views_v2_bootstrap_pre_get_view_html', [ $this, 'hide_event_end_time' ], 2 );
	}

	/**
	 * Remove our initialization hooks.
	 */
	public function remove_init_actions() {
		remove_action( 'tribe_views_v2_after_setup_loop', [ $this, 'hide_event_end_time' ], 2 );
		remove_action( 'tribe_events_views_v2_bootstrap_pre_get_view_html', [ $this, 'hide_event_end_time' ], 2 );
	}

	/**
	 * Hook for the hide end time setting to flag the view accordingly.
	 */
	public function hide_event_end_time(): void {
		// So we don't register init twice in cases where the init hooks repeat.
		$this->remove_init_actions();

		$views = (array) tribe_get_option( 'remove_event_end_time', [] );
		if ( empty( $views ) ) {
			return;
		}
		// Make an associative array to be the shape we expect.
		$views = array_flip( $views );
		// Any elements here should be false to indicate that the end time should be hidden.
		$views = array_map(
			function () {
				return false;
			},
			$views
		);

		// Create the modifier that handles when to show/hide the end time.
		$this->end_time_modifier = new Hide_End_Time_Modifier( $views );

		// Let's setup our context, in either one of two hooks.
		add_action( 'tribe_views_v2_after_setup_loop', [ $this, 'set_context_for_views_v2_setup_loop' ] );
		add_action(
			'tribe_events_views_v2_bootstrap_pre_get_view_html',
			[
				$this,
				'set_context_for_views_v2_endtime',
			],
			10,
			4
		);

		// If there are any views checked, then run the filter.
		add_filter( 'tribe_events_event_schedule_details_formatting', [ $this, 'handle_end_time_visibility' ] );

		// Hook to add the flag for month view template.
		add_action(
			'tribe_template_pre_html:events/v2/month/calendar-body/day/calendar-events/calendar-event/date',
			[ $this, 'handle_template_hide_end_time' ],
			10,
			4
		);

		/**
		 * Once we are setup, broadcast ourself for further integrations.
		 *
		 * @since 6.6.3
		 *
		 * @param Hide_End_Time_Provider $this The provider.
		 */
		do_action( 'tec_events_views_v2_hide_end_time_init', $this );
	}

	/**
	 * Hook callback for the month calendar-event/date template, where we add the hide end time flag.
	 *
	 * @since 6.6.3
	 *
	 * @param string          $html Current template HTML.
	 * @param string          $file File path.
	 * @param string          $name Template name.
	 * @param Tribe__Template $template The month template.
	 */
	public function handle_template_hide_end_time( $html, $file, $name, $template ) {
		// Will check hide flag with current view context.
		$settings = $this->handle_end_time_visibility();

		// Set the hide flag on this Month Day template.
		$template->set_values( $settings );
	}

	/**
	 * Handles the visibility of the end time.
	 *
	 * @since 6.4.1
	 *
	 * @param array<string, boolean> $settings The settings.
	 *
	 * @return array
	 */
	public function handle_end_time_visibility( $settings = [] ) {
		$context = $this->end_time_modifier->get_context();

		// Is this view flagged to hide the end time?
		$settings['show_end_time'] = $this->end_time_modifier->is_visible( $context->get( 'view' ) );

		return $settings;
	}

	/**
	 * Sets the context for the hide end time modifier.
	 *
	 * @since 6.4.1
	 *
	 * @param View $view The view.
	 */
	public function set_context_for_views_v2_setup_loop( $view ) {
		// We need further context to determine if we should hide the end time for a particular area.
		$this->end_time_modifier->set_context( $view->get_context() );
	}

	/**
	 * Sets the context for the views v2 end time view modifier.
	 *
	 * @since 6.4.1
	 *
	 * @param string $html      The HTML to be filtered.
	 * @param string $view_slug The view slug.
	 * @param array  $query     The query.
	 * @param array  $context   The context.
	 */
	public function set_context_for_views_v2_endtime( $html, $view_slug, $query, $context ) {
		// We need further context to determine if we should hide the end time for a particular area.
		$this->end_time_modifier->set_context( $context );
	}
}
