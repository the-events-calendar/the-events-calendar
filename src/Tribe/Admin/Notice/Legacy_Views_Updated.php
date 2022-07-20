<?php

namespace Tribe\Events\Admin\Notice;

use Tribe\Events\Views\V2\Manager;
use Tribe__Date_Utils as Dates;

/**
 * Class Legacy_Views_Updated.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Admin\Notice
 */
class Legacy_Views_Updated {
	/**
	 * Register legacy views updated notice.
	 *
	 * @since   TBD
	 */
	public function hook(): void {
		tribe_notice(
			'events-legacy-views-updated',
			[ $this, 'notice' ],
			[
				'dismiss' => 1,
				'type'    => 'warning',
				'wrap'    => false,
			],
			[ $this, 'should_display' ]
		);
	}

	/**
	 * Checks if we are in a page we need to display.
	 *
	 * @since   TBD
	 *
	 * @return bool
	 */
	public function is_valid_screen(): bool {
		/** @var \Tribe__Admin__Helpers $admin_helpers */
		$admin_helpers = tribe( 'admin.helpers' );

		return $admin_helpers->is_screen() || $admin_helpers->is_post_type_screen();
	}

	/**
	 * Checks all methods required for display.
	 *
	 * @since   TBD
	 *
	 * @return bool
	 */
	public function should_display(): bool {
		return $this->is_valid_screen() && $this->has_views_v2_negative_value();
	}

	/**
	 * Determines that we have a negative value stored, which means this installation was forced into V2.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	protected function has_views_v2_negative_value(): bool {
		$enabled = tribe_get_option( Manager::$option_enabled, null );

		return null === $enabled || false === $enabled || 0 === $enabled || '0' === $enabled;
	}

	/**
	 * HTML for the notice for sites using V1.
	 *
	 * @since   TBD
	 *
	 * @return string
	 */
	public function notice(): string {

		$link_one = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( 'https://theeventscalendar.com/knowledgebase/k/v1-deprecation-faqs/' ),
			esc_html_x( 'read the FAQs', 'Read more about deprecation of legacy views.', 'the-events-calendar' )
		);
		$link_two = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( 'https://theeventscalendar.com/support/' ),
			esc_html_x( 'contact support', 'Our support page for TEC', 'the-events-calendar' )
		);

		$title = esc_html__( 'Your calendar’s design has changed', 'the-events-calendar' );

		$text  = __( 'We’ve detected that your site was still using our legacy calendar design. As part of the update to The Events Calendar 6.0, <strong>your calendar was automatically upgraded to the new designs.</strong>', 'the-events-calendar' );
		$text  .= '<br><br>';
		$text  .= sprintf( __( '<strong>Check out your calendar to see the improved designs live on your site.</strong> If you have a question or need help, %1$s or %2$s.', 'the-events-calendar' ), $link_one, $link_two );

		$links = sprintf(
			'<a href="%1$s" class="button">%2$s</a>',
			tribe_events_get_url(),
			esc_html__( 'View your calendar', 'the-events-calendar' )
		);

		return '<h3>' . $title . '</h3><p>' .  $text . '</p><p>' . $links . '</p>';
	}
}