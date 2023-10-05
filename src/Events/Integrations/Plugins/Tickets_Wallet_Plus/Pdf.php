<?php

namespace TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus;

use Tribe__Template;
use Tribe__Events__Main as TEC;
/**
 * Class Pdf
 * 
 * @since TBD
 * 
 * @package TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus
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
				$template->set_template_folder( 'src/views/integrations/tickets-wallet-plus' );
				$template->set_template_folder_lookup( true );
				$template->set_template_context_extract( true );
				$this->template = $template;
			}
			return $this->template;
		}

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

			$this->get_template()->template( 'pdf/pass/tec-styles', $template->get_local_values(), true );
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

			$this->get_template()->template( 'pdf/pass/body/venue', $template->get_local_values(), true );
		}

		/**
		 * Add date.
		 * 
		 * @since TBD
		 * 
		 * @param string           $file     Path to the file.
		 * @param string           $name     Name of the file.
		 * @param \Tribe__Template $template Template instance.
		 * 
		 * @return void
		 */
		public function add_date( $file, $name, $template ): void {
			if ( ! $template instanceof \Tribe__Template ) {
				return;
			}

			$this->get_template()->template( 'pdf/pass/body/date', $template->get_local_values(), true );
		}
}

