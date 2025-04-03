<?php

namespace TEC\Events\Views\Modifiers;

use TEC\Events\Views\Modifiers\Visibility_Modifier_Abstract;
use Tribe\Events\Views\V2\Manager as Views_Manager;
use Tribe__Context;
use WP_Post;

/**
 * Class Hide_End_Time_Modifier.
 *
 * This class is used to manage the visibility of end time for different views.
 *
 * @since 6.4.1
 *
 * @package TEC\Events\Views\Modifiers
 */
class Hide_End_Time_Modifier extends Visibility_Modifier_Abstract {

	/**
	 * @var Tribe__Context The context object.
	 */
	protected $context;

	/**
	 * Set the context object.
	 *
	 * @since 6.4.1
	 *
	 * @param Tribe__Context $context The context object.
	 */
	public function set_context( Tribe__Context $context ) {
		$this->context = $context;
	}

	/**
	 * Get the context object.
	 *
	 * @since 6.4.1
	 *
	 * @return Tribe__Context $context The context object.
	 */
	public function get_context(): Tribe__Context {
		return $this->context ?? tribe_context();
	}

	/**
	 * Get the list of valid options for visibility.
	 *
	 * @since 6.4.1
	 *
	 * @return array The visibility options.
	 */
	public function get_options(): array {
		return $this->options;
	}

	/**
	 * Get the default options.
	 *
	 * @return array<string, bool> The default options.
	 */
	public function get_defaults(): array {
		$defaults = [
			'recent'       => true,
			'single-event' => true,
			'day'          => true,
			'list'         => true,
			'month'        => true,
		];

		/**
		 * Filter to registering any additional views with default values.
		 *
		 * @since 6.6.3
		 *
		 * @param array                  $defaults The views and their default show flag.
		 * @param Hide_End_Time_Modifier $modifier Which modifier we are using.
		 */
		return apply_filters( 'tec_events_hide_end_time_modifier_defaults', $defaults, $this );
	}

	/**
	 * Check the visibility of the view for end time fields.
	 *
	 * @since 6.4.1
	 *
	 * @param string                  $area The view to check visibility for.
	 * @param null|int|string|WP_Post $post The post object to check visibility for.
	 *
	 * @return bool Whether the end time should be hidden or visible.
	 */
	final public function check_visibility( string $area, $post = null ): bool {
		$views = $this->get_options();

		// If the area is the default view, we need to replace 'default' with the actual view slug.
		if ( $area === 'default' ) {
			$area = tribe( Views_Manager::class )->get_default_view_slug();
		}

		if ( isset( $views[ $area ] ) ) {
			// Is this view flagged to hide the end time?
			return $views[ $area ];
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug(): string {
		return 'hide_end_time';
	}
}
