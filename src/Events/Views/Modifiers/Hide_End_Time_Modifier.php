<?php

namespace TEC\Events\Views\Modifiers;

use TEC\Events\Views\Modifiers\Visibility_Modifier_Abstract;
use Tribe__Context;
use WP_Post;

/**
 * Class Hide_End_Time_Modifier.
 *
 * This class is used to manage the visibility of end time for different views.
 *
 * @since   TBD
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
	 * @since TBD
	 *
	 * @param Tribe__Context $context The context object.
	 */
	public function set_context( Tribe__Context $context ) {
		$this->context = $context;
	}

	/**
	 * Get the context object.
	 *
	 * @since TBD
	 *
	 * @return Tribe__Context $context The context object.
	 */
	public function get_context(): Tribe__Context {
		return $this->context;
	}

	/**
	 * Get the list of valid options for visibility.
	 *
	 * @since   TBD
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
		return [
			'recent'       => true,
			'single-event' => true,
			'day'          => true,
			'list'         => true,
			'month'        => true,
		];
	}

	/**
	 * Check the visibility of the view for end time fields.
	 *
	 * @since TBD
	 *
	 * @param string                  $area The view to check visibility for.
	 * @param null|int|string|WP_Post $post The post object to check visibility for.
	 *
	 * @return bool Whether the end time should be hidden or visible.
	 */
	final public function check_visibility( string $area, $post = null ): bool {
		$views        = $this->get_options();
		$display_mode = $this->get_context()->get( 'event_display_mode' );

		if ( $area === 'list' && $display_mode === 'past' ) {
			// Recent past events list view should not show the end time.
			return isset( $views['recent'] ) ?? true;
		} elseif ( isset( $views[ $area ] ) ) {
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