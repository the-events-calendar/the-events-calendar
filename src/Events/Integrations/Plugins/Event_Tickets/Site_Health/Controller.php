<?php
/**
 * Class that handles interfacing with Site Health.
 *
 * @since 6.3.2
 *
 * @package TEC\Tickets_Plus\Integrations\Event_Tickets
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Site_Health;

use TEC\Common\Integrations\Traits\Plugin_Integration;
use TEC\Tickets\Integrations\Integration_Abstract;

/**
 * Class Controller
 *
 * @since 6.3.2
 *
 * @package TEC\Tickets_Plus\Integrations
 */
class Controller extends Integration_Abstract {
	use Plugin_Integration;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'event-tickets-site-health';
	}

	/**
	 * @inheritDoc
	 */
	public function load_conditionals(): bool {
		return function_exists( 'tribe_tickets' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function load(): void {
		$this->register_actions();
		$this->register_filters();
	}

	/**
	 * Register actions.
	 *
	 * @since 6.3.2
	 *
	 * @return void
	 */
	public function register_actions(): void {
	}

	/**
	 * Register filters.
	 *
	 * @since 6.3.2
	 *
	 * @return void
	 */
	public function register_filters(): void {

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
	 * @since 6.3.2
	 *
	 * @param array $subsections The existing array of site health subsections.
	 *
	 * @return array The modified array of subsections with the Event Tickets Plus subsection appended.
	 */
	public function site_health_additional_subsections( $subsections ) {

		$subsections[] = tribe( Subsection::class )->get_subsection();

		return $subsections;
	}
}
