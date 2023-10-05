<?php

namespace TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus;

/**
 * Class Pdf
 * 
 * @since TBD
 * 
 * @package TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus
 */
class Pdf {

	/**
	 * Filter template vars.
	 *
	 * @since TBD
	 * 
	 * @param array $vars
	 * 
	 * @return array
	 */
	public function filter_template_context( $ctx ): array {
		if ( empty( $ctx['post']->ID ) ) {
			return $ctx;
		}

		$post_id = intval( $ctx['post']->ID );
		if ( ! tribe_is_event( $post_id ) ) {
			return $ctx;
		}

		$event = tribe_get_event( $post_id );
		if ( empty( $event ) ) {
			return $ctx;
		}

		$ctx['event'] = $event;
		$ctx['venues'] = $event->venues->all();

		return $ctx;
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

		tribe( Template::class )->template( 'pdf/pass/tec-styles', $template->get_local_values(), true );
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

		tribe( Template::class )->template( 'pdf/pass/body/venue', $template->get_local_values(), true );
	}
}