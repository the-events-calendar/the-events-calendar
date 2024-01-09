<?php
/**
 * Handles hooking all the actions and filters used by Event Tickets Site Health.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Site_Health
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Site_Health;

/**
 * Class Hooks.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Site_Health
 */
class Hooks extends \TEC\Common\Contracts\Service_Provider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register(): void {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions required by each Site Health Component.
	 *
	 * @since TBD
	 */
	protected function add_actions(): void {
	}

	/**
	 * Adds the filters required by each Site Health Component.
	 *
	 * @since TBD
	 */
	protected function add_filters(): void {
		add_filter(
			'tec_tickets_site_health_subsections',
			[
				$this,
				'site_health_additional_subsections',
			]
		);
	}

	/**
	 * Appends an additional subsection to the site health subsections array.
	 *
	 * @since TBD
	 *
	 * @param array $subsections The existing array of site health subsections.
	 *
	 * @return array The modified array of subsections with The Events Calendar subsection appended.
	 */
	public function site_health_additional_subsections( $subsections ) {

		$subsections[] = tribe( The_Events_Calendar_Subsection::class )->get_subsection();

		return $subsections;
	}
}
