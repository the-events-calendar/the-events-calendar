<?php
/**
 * Service Provider for Event Aggregator upsell/settings.
 *
 * @since   TBD
 *
 * @package TEC\Events\Admin\Settings
 */

declare( strict_types=1 );

namespace TEC\Events\Admin\Settings;

use TEC\Common\Contracts\Service_Provider;
use Tribe__Events__Main as Main;
use Tribe__Template as Template;

/**
 * Class Event_Aggregator_Upsell
 *
 * @since TBD
 */
class Event_Aggregator_Upsell extends Service_Provider {

	/**
	 * Stores the instance of the template engine that we will use for rendering the elements.
	 *
	 * @since TBD
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * Registers the service provider bindings.
	 *
	 * @return void The method does not return any value.
	 */
	public function register() {
		if ( tec_should_hide_upsell() ) {
			return;
		}

		// Bail if Event Aggregator is already installed/registered.
		if ( tribe( 'events-aggregator.main' )->is_service_active() ) {
			return;
		}

		$this->add_actions();
	}

	/**
	 * Add actions.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function add_actions() {
		add_action(
			'tec_events_settings_tab_imports_fields',
			function ( array $fields ) {
				return $this->add_infobox_to_fields( $fields );
			}
		);
	}

	/**
	 * Add the Event Aggregator upsell infobox to the fields.
	 *
	 * @since TBD
	 *
	 * @param array $fields The fields to add the infobox to.
	 *
	 * @return array The fields with the infobox added.
	 */
	protected function add_infobox_to_fields( array $fields ) {
		$html = $this->get_upsell_html();
		if ( empty( $html ) ) {
			return $fields;
		}

		return array_merge(
			[
				'event-aggregator-upsell-info-box' => [
					'type' => 'html',
					'html' => $html,
				],
			],
			$fields
		);
	}

	/**
	 * Get the HTML for the Event Aggregator upsell.
	 *
	 * @since TBD
	 *
	 * @param array $context   The context to pass to the template.
	 * @param bool  $echo_html Whether to echo the HTML or return it.
	 *
	 * @return string
	 */
	protected function get_upsell_html( array $context = [], bool $echo_html = false ): string {
		$html = $this->get_template()->template( 'event-aggregator', $context, $echo_html );

		return $html ?: '';
	}

	/**
	 * Get the template engine instance.
	 *
	 * @since TBD
	 *
	 * @return Template
	 */
	protected function get_template(): Template {
		if ( empty( $this->template ) ) {
			$template = new Template();
			$template->set_template_origin( Main::instance() );
			$template->set_template_folder( 'src/admin-views/settings/upsells/' );
			$template->set_template_context_extract( true );
			$template->set_template_folder_lookup( false );

			$this->template = $template;
		}

		return $this->template;
	}
}
