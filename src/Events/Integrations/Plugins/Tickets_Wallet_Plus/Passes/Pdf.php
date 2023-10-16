<?php

namespace TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus\Passes;

use Tribe\Utils\Lazy_String;
use Tribe__Template;
use Tribe__Events__Main as TEC;
use Tribe__Events__Main;

/**
 * Class Pdf
 * 
 * @since TBD
 * 
 * @package TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus\Passes
 */
class Pdf {

	/**
	 * Template instance.
	 * 
	 * @since TBD
	 * 
	 * @var \Tribe__Template
	 */
	private $template;

	/**
	 * Get the template.
	 *
	 * @since TBD
	 *
	 * @return \Tribe__Template
	 */
	public function get_template(): Tribe__Template {
		if ( empty( $this->template ) ) {
			$template = new Tribe__Template();
			$template->set_template_origin( TEC::instance() );
			$template->set_template_folder( 'src/views/integrations/event-tickets-wallet-plus' );
			$template->set_template_folder_lookup( true );
			$template->set_template_context_extract( true );
			$this->template = $template;
		}
		return $this->template;
	}

	/**
	 * Filter template context.
	 *
	 * @since TBD
	 * 
	 * @param array $context
	 * 
	 * @return array
	 */
	public function filter_template_context( $context ): array {
		if ( empty( $context['post']->ID ) ) {
			return $context;
		}

		$post_id = intval( $context['post']->ID );
		if ( ! tribe_is_event( $post_id ) ) {
			return $context;
		}

		$event = tribe_get_event( $post_id );
		if ( empty( $event ) ) {
			return $context;
		}

		$context['event'] = $event;
		$context['venues'] = $event->venues->all();

		return $context;
	}

	/**
	 * Add styles.
	 * 
	 * @since TBD
	 * 
	 * @param string           $file     Path to the file.
	 * @param string           $name     Name of the file.
	 * @param \Tribe__Template $template Template instance.
	 * 
	 * @return void
	 */
	public function add_tec_styles( $file, $name, $template ): void {
		if ( ! $template instanceof \Tribe__Template ) {
			return;
		}

		$this->get_template()->template( 'pdf/pass/tec-events-styles', $template->get_local_values(), true );
	}

	/**
	 * Add venue.
	 * 
	 * @since TBD
	 * 
	 * @param string           $file     Path to the file.
	 * @param string           $name     Name of the file.
	 * @param \Tribe__Template $template Template instance.
	 * 
	 * @return void
	 */
	public function add_venue( $file, $name, $template ): void {
		if ( ! $template instanceof \Tribe__Template ) {
			return;
		}

		$this->get_template()->template( 'pdf/pass/body/event/venue', $template->get_local_values(), true );
	}

	/**
	 * Add event date.
	 * 
	 * @since TBD
	 * 
	 * @param string           $file     Path to the file.
	 * @param string           $name     Name of the file.
	 * @param \Tribe__Template $template Template instance.
	 * 
	 * @return void
	 */
	public function add_event_date( $file, $name, $template ): void {
		if ( ! $template instanceof \Tribe__Template ) {
			return;
		}

		$this->get_template()->template( 'pdf/pass/body/event/date', $template->get_local_values(), true );
	}

	/**
	 * Add attendee fields.
	 * 
	 * @since TBD
	 * 
	 * @param array $context Path to the file.
	 * 
	 * @return array
	 */
	public function add_event_data_to_sample( $context ): array {
		
		$sample_event = (object) [
			'ID'               => 213123123,
			'permalink'        => '#',
			'schedule_details' => new Lazy_String(
				static function () {
					return esc_html__( 'September 22 @ 7:00 pm - 11:00 pm', 'the-events-calendar' );
				}
			),
			'dates'            => (object) [],
			'thumbnail'        => (object) [
				'exists'    => true,
				'full'      => (object) [
					'url' => esc_url( tribe_resource_url( 'images/event-example-image.jpg', false, null, Tribe__Events__Main::instance() ) ),
				],
				'thumbnail' => (object) [
					'alt'   => esc_html__( 'Arts in the Park', 'the-events-calendar' ),
					'title' => esc_html__( 'Arts in the Park', 'the-events-calendar' ),
				],
			],
		];
		$sample_venues = [
			(object) [
				'post_title'      => esc_html__( 'Central Park', 'the-events-calendar' ),
				'address'         => esc_html__( '41st Street', 'the-events-calendar' ),
				'city'            => esc_html__( 'New York', 'the-events-calendar' ),
				'state'           => esc_html__( 'NY 10001', 'the-events-calendar' ),
				'country'         => esc_html__( 'United States', 'the-events-calendar' ),
				'phone'           => esc_html__( '(555) 555-5555', 'the-events-calendar' ),
				'website_url'     => esc_url( get_site_url() ),
				'directions_link' => '#',
			],
		];

		$context['event']  = $sample_event;
		$context['venues'] = $sample_venues;

		return $context;
	}
}

