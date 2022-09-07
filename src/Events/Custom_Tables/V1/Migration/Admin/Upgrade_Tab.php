<?php
/**
 * Handles the registration of classes, implementations, and filters for Migration activities.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Admin
 */

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

class Upgrade_Tab {
	/**
	 * A reference to the current migration state handler.
	 *
	 * @since 6.0.0
	 *
	 * @var State
	 */
	protected $state;

	/**
	 * Upgrade_Tab constructor.
	 *
	 * since 6.0.0
	 *
	 * @param State $state A reference to the current migration state handler.
	 */
	public function __construct( State $state ) {
		$this->state = $state;
	}

	/**
	 * Whether or not the upgrade tab in Event Settings should show. The tab will disappear after 30 days of migration completion.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the upgrade tab should show or not.
	 */
	public function should_show() {
		// If complete, use a 30 day expiration.
		$complete_timestamp = $this->state->get( 'complete_timestamp' );
		if ( $complete_timestamp && $this->state->get_phase() === State::PHASE_MIGRATION_COMPLETE ) {

			$current_date   = ( new \DateTime( 'now', wp_timezone() ) );
			$date_completed = ( new \DateTime( 'now', wp_timezone() ) )->setTimestamp( $complete_timestamp );
			// 30 day old expiration
			$expires_in_seconds = 30 * 24 * 60 * 60;

			// If time for our reverse migration has expired
			return ( $current_date->format( 'U' ) - $expires_in_seconds ) < $date_completed->format( 'U' );
		}


		return $this->state->is_required()
		       || $this->state->is_running()
		       || $this->state->is_completed();
	}

	/**
	 * Get the migration phase content and inject into the admin fields.
	 *
	 * @since 6.0.0
	 *
	 * @param array $upgrade_fields TEC Settings options.
	 *
	 * @return mixed
	 */
	public function add_phase_content( $upgrade_fields ) {
		$phase_html = $this->get_phase_html();

		$upgrade_fields['ct1_migration'] = [
			'type' => 'html',
			'html' => $phase_html,
		];

		return $upgrade_fields;
	}


	/**
	 * Renders and returns the current phase HTML code.
	 *
	 * @since 6.0.0
	 *
	 * @return string The current phase HTML code.
	 */
	public function get_phase_html() {
		$template_vars = [
			'phase' => $this->state->get_phase(),
			'text'  => tribe( String_Dictionary::class ),
			'tab'   => $this,
		];

		return (string) tribe( Template::class )->template( 'migration/upgrade-box', $template_vars, false );
	}
}
