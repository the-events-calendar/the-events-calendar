<?php
/**
 * Registers the settings of the Recurrence feature.
 *
 * One setting so far, shown on sites that have used Events Calendar Pro: whether the
 * rule-based recurring Events Pro authored stay locked while Pro is inactive, or can be
 * converted to individual dates from their edit screen.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Admin\Settings as Admin_Settings;

/**
 * Class Settings.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Settings extends Service_Provider {
	/**
	 * The option keeping rule-based recurrence locked while Events Calendar Pro is inactive.
	 *
	 * @since TBD
	 */
	public const LOCK_OPTION = 'tec_events_recurrence_lock_pro_rules';

	/**
	 * The DOM ID of the lock setting field, the anchor the edit screen links to.
	 *
	 * @since TBD
	 */
	public const FIELD_ID = 'tec-events-recurrence-lock-pro-rules';

	/**
	 * The slug of the settings tab the setting lives in.
	 *
	 * @since TBD
	 */
	public const TAB_SLUG = 'general-editing-tab';

	/**
	 * The Events Calendar Pro page the upsell links to; the same link the recurrence banner uses.
	 *
	 * @since TBD
	 */
	public const PRO_URL = 'https://evnt.is/ecp';

	/**
	 * Registers the settings fields and the Pro history signal.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( self::class, $this );

		if ( ! has_filter( 'tribe_general_settings_editing_section', [ $this, 'add_fields' ] ) ) {
			add_filter( 'tribe_general_settings_editing_section', [ $this, 'add_fields' ] );
		}

		if ( ! has_action( 'tec_events_recurrence_rules_frozen', [ $this, 'mark_pro_history' ] ) ) {
			// Rules no rule engine claimed were authored by Events Calendar Pro.
			add_action( 'tec_events_recurrence_rules_frozen', [ $this, 'mark_pro_history' ] );
		}
	}

	/**
	 * Unregisters the hooks managed by the provider.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_filter( 'tribe_general_settings_editing_section', [ $this, 'add_fields' ] );
		remove_action( 'tec_events_recurrence_rules_frozen', [ $this, 'mark_pro_history' ] );
	}

	/**
	 * Returns whether rule-based (Events Calendar Pro) recurrence stays locked while Pro is inactive.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the lock is enabled.
	 */
	public function is_lock_enabled(): bool {
		$enabled = tribe_is_truthy( tribe_get_option( self::LOCK_OPTION, true ) );

		/**
		 * Filters whether rule-based (Events Calendar Pro) recurrence stays locked while Pro is inactive.
		 *
		 * @since TBD
		 *
		 * @param bool $enabled Whether the lock is enabled; defaults to the value of the setting, `true` when unset.
		 */
		return tribe_is_truthy( apply_filters( 'tec_events_recurrence_pro_rules_locked', $enabled ) );
	}

	/**
	 * Returns whether an Event can be converted from rule-based recurrence to individual dates.
	 *
	 * The Event must be rule-locked, the lock setting off and Events Calendar Pro not
	 * loaded. Capabilities are not checked here: the request handler owns that.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return bool Whether the Event can be converted.
	 */
	public function can_convert( int $post_id ): bool {
		$post_id = Occurrence::normalize_id( $post_id );
		$can     = $post_id > 0
			&& ! class_exists( 'Tribe__Events__Pro__Main', false )
			&& ! $this->is_lock_enabled()
			&& $this->container->make( Authoring_Guard::class )->is_rule_locked( $post_id );

		/**
		 * Filters whether a rule-based Event can be converted to individual dates.
		 *
		 * @since TBD
		 *
		 * @param bool $can     Whether the Event can be converted.
		 * @param int  $post_id The Event post ID.
		 */
		return tribe_is_truthy( apply_filters( 'tec_events_recurrence_can_convert', $can, $post_id ) );
	}

	/**
	 * Returns the URL of the lock setting in the Events settings.
	 *
	 * @since TBD
	 *
	 * @return string The settings URL, anchored to the field.
	 */
	public function get_settings_url(): string {
		return tribe( Admin_Settings::class )->get_url( [ 'tab' => self::TAB_SLUG ] ) . '#' . self::FIELD_ID;
	}

	/**
	 * Memoizes the Pro history when rule-based recurrence is frozen.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function mark_pro_history(): void {
		$this->container->make( Pro_History::class )->mark_detected( 'frozen_rules' );
	}

	/**
	 * Adds the lock setting to the General > Editing settings section.
	 *
	 * The field only exists on sites that have used Events Calendar Pro: a site that
	 * never did has no rule-based Events to protect. It is one more checkbox in the
	 * section, laid out like the ones before it.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed>|mixed $fields The section fields.
	 *
	 * @return array<string,mixed>|mixed The section fields, with the lock setting.
	 */
	public function add_fields( $fields ) {
		if ( ! is_array( $fields ) || ! $this->container->make( Pro_History::class )->has_pro_history() ) {
			return $fields;
		}

		$tooltip = esc_html__( 'Recurring events created with Events Calendar Pro keep their dates and recurrence rules frozen while Events Calendar Pro is not active; their other details stay editable. Uncheck to allow converting such an event to individual dates from its edit screen: this removes its recurrence rules and its Series link, and reactivating Events Calendar Pro will not restore them.', 'the-events-calendar' );

		if ( ! tec_should_hide_upsell() ) {
			// The way out that keeps the rules: the same link as the recurrence banner in the editor.
			$tooltip .= ' ' . sprintf(
				/* translators: %1$s: opening link tag to the Events Calendar Pro page, %2$s: closing link tag. */
				esc_html__( '%1$sGet Events Calendar Pro%2$s to edit recurrence rules again.', 'the-events-calendar' ),
				'<a href="' . esc_url( self::PRO_URL ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);
		}

		$fields[ self::LOCK_OPTION ] = [
			'type'            => 'checkbox_bool',
			'label'           => esc_html__( 'Keep Events Calendar Pro recurrence rules locked', 'the-events-calendar' ),
			'tooltip'         => $tooltip,
			'default'         => true,
			'validation_type' => 'boolean',
			'attributes'      => [ 'id' => self::FIELD_ID ],
		];

		return $fields;
	}
}
