<?php
/**
 * Registers the Classic Editor authoring surface of the Recurrence feature.
 *
 * The Event Dates section lets an editor author the additional, explicit dates of an
 * Event one by one; rule-based recurrence stays an Events Calendar Pro feature. The
 * section renders inside the Events datetime metabox section, where Events Calendar
 * Pro mounts its recurrence UI, and its rows map to the AUTHORED `_EventRecurrence`
 * date rules, mirroring the Pro authoring model.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Recurrence\Updates\Rules_Conversion_Request;
use Tribe__Events__Main as TEC;

/**
 * Class Admin_Provider.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Admin_Provider extends Service_Provider {
	/**
	 * The name of the posted dates field.
	 *
	 * @since TBD
	 */
	public const FIELD = 'tec_events_recurrence_dates';

	/**
	 * The nonce action of the Event Dates section.
	 *
	 * @since TBD
	 */
	public const NONCE_ACTION = 'tec_events_recurrence_dates_save';

	/**
	 * The DOM ID of the paragraph explaining why the date controls of a rule-based Event are disabled.
	 *
	 * @since TBD
	 */
	public const LOCK_REASON_ID = 'tec-events-recurrence-dates-lock-reason';

	/**
	 * The ID of the rule-based Event whose conversion form the admin footer renders, `0` for none.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	private int $convert_form_event_id = 0;

	/**
	 * Registers the Event Dates section and its save handler.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( self::class, $this );

		if ( class_exists( 'Tribe__Events__Pro__Main', false ) ) {
			// Events Calendar Pro provides the full recurrence UI.
			return;
		}

		if ( ! has_action( 'tribe_events_date_display', [ $this, 'render_section' ] ) ) {
			// Below the date picker, above the Events Calendar Pro upsell (18); Pro renders at 10.
			add_action( 'tribe_events_date_display', [ $this, 'render_section' ], 15 );
		}

		if ( ! has_action( 'tribe_events_update_meta', [ $this, 'save_dates' ] ) ) {
			// The same hook and priority Events Calendar Pro consumes classic recurrence on.
			add_action( 'tribe_events_update_meta', [ $this, 'save_dates' ], 20 );
		}

		if ( ! has_filter( 'tribe_events_meta_box_vars', [ $this, 'lock_date_controls' ] ) ) {
			add_filter( 'tribe_events_meta_box_vars', [ $this, 'lock_date_controls' ] );
		}

		if ( ! has_action( 'admin_footer', [ $this, 'render_convert_form' ] ) ) {
			// Outside the post form: a form nested in it would be dropped by the parser.
			add_action( 'admin_footer', [ $this, 'render_convert_form' ] );
		}

		if ( ! $this->container->isBound( Rules_Conversion_Request::class ) ) {
			$this->container->singleton( Rules_Conversion_Request::class );
		}
		$this->container->make( Rules_Conversion_Request::class )->register();
	}

	/**
	 * Unregisters the hooks managed by the provider.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'tribe_events_date_display', [ $this, 'render_section' ], 15 );
		remove_action( 'tribe_events_update_meta', [ $this, 'save_dates' ], 20 );
		remove_filter( 'tribe_events_meta_box_vars', [ $this, 'lock_date_controls' ] );
		remove_action( 'admin_footer', [ $this, 'render_convert_form' ] );
		$this->convert_form_event_id = 0;

		if ( $this->container->isBound( Rules_Conversion_Request::class ) ) {
			$this->container->make( Rules_Conversion_Request::class )->unregister();
		}
	}

	/**
	 * Disables the date controls of the Events metabox for a rule-based Event.
	 *
	 * The dates of a rule-based Event, and of each of its Occurrences, are frozen
	 * server-side; the controls tell so.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed>|mixed $vars The metabox template variables.
	 *
	 * @return array<string,mixed>|mixed The variables, with the lock flag for a rule-based Event.
	 */
	public function lock_date_controls( $vars ) {
		if ( ! is_array( $vars ) ) {
			return $vars;
		}

		$event_id = (int) get_the_ID();

		if ( $event_id <= 0 ) {
			$event_id = absint( tribe_get_request_var( 'post', 0 ) );
		}

		if ( $event_id <= 0 ) {
			return $vars;
		}

		// A provisional Occurrence ID answers about its Event.
		if ( ! $this->container->make( Authoring_Guard::class )->is_rule_locked( $event_id ) ) {
			return $vars;
		}

		$vars['dates_locked']             = true;
		$vars['dates_locked_describedby'] = self::LOCK_REASON_ID;

		return $vars;
	}

	/**
	 * Renders the conversion form of the rule-based Event the edit screen rendered, if any.
	 *
	 * The controls inside the Event Dates section target this form through their `form`
	 * attribute: the form itself cannot live inside the post form.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function render_convert_form(): void {
		$event_id = $this->convert_form_event_id;

		if ( $event_id <= 0 ) {
			return;
		}

		$fields = Rules_Conversion_Request::get_form_fields( $event_id );
		?>
		<form
			id="<?php echo esc_attr( Rules_Conversion_Request::FORM_ID ); ?>"
			class="tec-events-recurrence-dates__convert-form"
			method="post"
			action="<?php echo esc_url( Rules_Conversion_Request::get_action_url() ); ?>"
		>
			<?php foreach ( $fields as $name => $value ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			<?php endforeach; ?>
		</form>
		<?php
	}

	/**
	 * Renders the Event Dates section inside the Events datetime metabox section.
	 *
	 * @since TBD
	 *
	 * @param int|mixed $event_id The Event post ID; `0` when a new Event is being created.
	 *
	 * @return void
	 */
	public function render_section( $event_id = 0 ): void {
		$event_id = (int) $event_id;
		$guard    = $this->container->make( Authoring_Guard::class );

		$is_occurrence        = $event_id > 0 && $guard->is_occurrence_edit( $event_id );
		$is_locked            = $event_id > 0 && $guard->is_rule_locked( $event_id );
		$parent_id            = $event_id > 0 ? Occurrence::normalize_id( $event_id ) : 0;
		$occurrence_edit_link = '';
		$rows                 = [];
		$chips                = [
			'count'    => 0,
			'upcoming' => [],
			'past'     => [],
		];
		$lock_enabled         = true;
		$can_convert          = false;
		$settings_url         = '';
		$lock_reason_id       = self::LOCK_REASON_ID;
		$convert_form_id      = Rules_Conversion_Request::FORM_ID;
		$ack_field            = Rules_Conversion_Request::ACK_FIELD;

		if ( $is_occurrence ) {
			// Built directly: link filters would rewrite the parent Event link back to the Occurrence.
			$occurrence_edit_link = admin_url( 'post.php?post=' . $parent_id . '&action=edit' );
		}

		if ( $is_locked ) {
			// An Occurrence of a rule-based Event is as locked as the Event: the lock is about the Event.
			$settings     = $this->container->make( Settings::class );
			$chips        = $this->get_chips( $parent_id );
			$lock_enabled = $settings->is_lock_enabled();
			$settings_url = $settings->get_settings_url();
			$can_convert  = $settings->can_convert( $parent_id ) && current_user_can( 'edit_post', $parent_id );

			// The admin footer renders the form the conversion controls target; its nonce is the Event's.
			$this->convert_form_event_id = $can_convert ? $parent_id : 0;
		} elseif ( ! $is_occurrence && $event_id > 0 ) {
			// The same display formats the Start/End pickers above the section use.
			$date_format = \Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );
			$event_all_day = tribe_event_is_all_day( $parent_id );
			$time_format = \Tribe__View_Helpers::is_24hr_format() ? 'H:i' : 'g:ia';

			$rows = array_map(
				static function ( array $period ) use ( $date_format, $time_format, $event_all_day ): array {
					// The authored meta stores times without seconds: an all-day date spans 00:00 to 23:59.
					$all_day = $event_all_day;

					return [
						'date'     => $period['start']->format( $date_format ),
						'end_date' => $period['end']->format( $date_format ),
						'start'  => $period['start']->format( $time_format ),
						'end'    => $period['end']->format( $time_format ),
						'allday' => $all_day,
					];
				},
				$guard->get_authored_periods( $event_id )
			);
		}

		include TEC::instance()->pluginPath . 'src/admin-views/recurrence/event-dates.php';
	}

	/**
	 * Builds the scheduled dates chips of a locked Event, split between upcoming and past.
	 *
	 * @since TBD
	 *
	 * @param int $event_id The Event post ID.
	 *
	 * @return array{
	 *     count: int,
	 *     upcoming: array<int,array{label: string, tooltip: array<int,string>, permalink: string, status: string}>,
	 *     past: array<int,array{label: string, tooltip: array<int,string>, permalink: string, status: string}>
	 * } The chips: the upcoming ones (the next one first) and the past ones, oldest first.
	 */
	private function get_chips( int $event_id ): array {
		$list  = $this->container->make( Occurrences_List::class );
		$chips = [
			'count'    => 0,
			'upcoming' => [],
			'past'     => [],
		];

		foreach ( $list->get_scheduled_dates( $event_id ) as $row ) {
			$chip = $list->format_chip( $row );

			$chips[ 'past' === $chip['status'] ? 'past' : 'upcoming' ][] = $chip;
			++$chips['count'];
		}

		return $chips;
	}

	/**
	 * Saves the additional dates posted from the Event Dates section.
	 *
	 * Runs on `tribe_events_update_meta`, after the Event date meta was saved. The
	 * Dates_Service might read the pre-save Event row dates within this request;
	 * the Custom Tables update re-derives the RSET from the canonical meta at
	 * commit time, so the state converges.
	 *
	 * @since TBD
	 *
	 * @param int|mixed $event_id The Event post ID.
	 *
	 * @return void
	 */
	public function save_dates( $event_id ): void {
		$nonce = tribe_get_request_var( self::NONCE_ACTION . '_nonce' );

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			// The Event Dates section was not rendered on this save.
			return;
		}

		$event_id = (int) $event_id;

		if (
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| wp_is_post_revision( $event_id )
			|| wp_is_post_autosave( $event_id )
			|| ! current_user_can( 'edit_post', $event_id )
		) {
			return;
		}

		$guard = $this->container->make( Authoring_Guard::class );

		if ( $guard->is_occurrence_edit( $event_id ) || $guard->is_rule_locked( $event_id ) ) {
			// A single Occurrence screen, or Pro rule data: not authored here.
			return;
		}

		$rows  = tribe_get_request_var( self::FIELD, [] );
		$dates = [];

		// The rows post the same formats the Start/End pickers do: parse them the way the API does.
		$datepicker_format = \Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );

		$invalid = false;
		foreach ( (array) $rows as $row ) {
			$all_day = is_array( $row ) && tribe_is_truthy( $row['allday'] ?? '' );
			if ( $all_day && ! tribe_event_is_all_day( $event_id ) ) {
				wp_die( esc_html__( 'All Day applies to every date of the event. Change the event All Day setting to use all-day dates.', 'the-events-calendar' ), 400 );
				return;
			}

			if ( ! is_array( $row ) || empty( $row['date'] ) || ( ! $all_day && ( empty( $row['start'] ) || empty( $row['end'] ) ) ) ) {
				$invalid = true;
				continue;
			}

			$date = \Tribe__Date_Utils::datetime_from_format( $datepicker_format, sanitize_text_field( (string) $row['date'] ) );

			if ( ! is_string( $date ) || '' === $date || false === strtotime( $date ) ) {
				$invalid = true;
				continue;
			}

			$end_date = ! empty( $row['end_date'] ) ? \Tribe__Date_Utils::datetime_from_format( $datepicker_format, sanitize_text_field( (string) $row['end_date'] ) ) : $date;
			if ( ! is_string( $end_date ) || false === strtotime( $end_date ) || $end_date < $date ) {
				$invalid = true;
				continue;
			}

			if ( $all_day ) {
				$dates[] = [
					'start' => "{$date} 00:00:00",
					'end'   => "{$end_date} 23:59:59",
				];

				continue;
			}

			$start = strtotime( $date . ' ' . sanitize_text_field( (string) $row['start'] ) );
			$end   = strtotime( $end_date . ' ' . sanitize_text_field( (string) $row['end'] ) );

			if ( false === $start || false === $end ) {
				$invalid = true;
				continue;
			}

			if ( $end <= $start ) {
				// Invalid periods must not be interpreted as deleted dates.
				$invalid = true;
				continue;
			}

			$dates[] = [
				// The strings were parsed as UTC: gmdate() round-trips the wall time exactly.
				'start' => gmdate( 'Y-m-d H:i:s', $start ),
				'end'   => gmdate( 'Y-m-d H:i:s', $end ),
			];
		}

		if ( $invalid ) {
			wp_die( esc_html__( 'One or more additional dates are invalid. Check that each end is after its start. The additional dates were not changed.', 'the-events-calendar' ), 400 );
			return;
		}

		$service = $this->container->make( Dates_Service::class );

		if ( count( $dates ) ) {
			$saved = $service->set_dates( $event_id, $dates );
		} else {
			$saved = $service->remove_dates( $event_id );
		}
		if ( ! $saved ) {
			wp_die( esc_html__( 'The additional dates could not be saved. Return to the editor, review the dates and try again. Other event changes may already have been saved.', 'the-events-calendar' ), 500 );
		}

	}
}
