<?php
/**
 * Events post main metabox
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$events_label_singular           = tribe_get_event_label_singular();
$events_label_plural             = tribe_get_event_label_plural();
$events_label_singular_lowercase = tribe_get_event_label_singular_lowercase();
$events_label_plural_lowercase   = tribe_get_event_label_plural_lowercase();
?>
<div id="eventIntro">
	<div id="tribe-events-post-error" class="tribe-events-error error"></div>
	<?php
	/**
	 * Fires inside the top of "The Events Calendar" meta box
	 *
	 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
	 * @param boolean
	 */
	do_action( 'tribe_events_post_errors', $event->ID, true );
	?>
</div>
<div id='eventDetails' class="inside eventForm" data-datepicker_format="<?php echo esc_attr( \Tribe__Date_Utils::get_datepicker_format_index() ); ?>">
	<?php
	/**
	 * Fires inside the opening #eventDetails div of The Events Calendar meta box
	 *
	 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
	 * @param boolean
	 */
	do_action( 'tribe_events_detail_top', $event->ID, true );

	wp_nonce_field( Tribe__Events__Main::POSTTYPE, 'ecp_nonce' );

	/**
	 * Fires after the nonce field inside The Events Calendar meta box
	 *
	 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
	 * @param boolean
	 */
	do_action( 'tribe_events_eventform_top', $event->ID );
	?>
	<table cellspacing="0" cellpadding="0" id="EventInfo">
		<?php if ( tribe( 'tec.gutenberg' )->should_display() || tribe( 'tec.gutenberg' )->is_classic_editor_page() ) : ?>
		<tr>
			<td colspan="2" class="tribe_sectionheader">
				<div class="tribe_sectionheader" style="">
					<h4><?php esc_html_e( 'Time &amp; Date', 'the-events-calendar' ); ?></h4></div>
			</td>
		</tr>
		<?php endif; ?>

		<tr>
			<td colspan="2">
				<table class="eventtable">
					<?php
					/**
					 * Don't Remove the <colgroup> it's important to avoid
					 * recurrence meta changing it's width when the Description changes
					 */
					?>
					<colgroup>
						<col style="width:15%">
						<col style="width:85%">
					</colgroup>
					<tr id="recurrence-changed-row">
						<td colspan='2'><?php printf( esc_html__( 'You have changed the recurrence rules of this %1$s.  Saving the %1$s will update all future %2$s.  If you did not mean to change all %2$s, then please refresh the page.', 'the-events-calendar' ), $events_label_singular_lowercase, $events_label_plural_lowercase ); ?></td>
					</tr>

					<?php if ( tribe( 'tec.gutenberg' )->should_display() || tribe( 'tec.gutenberg' )->is_classic_editor_page()  ) : ?>

					<tr>
						<td class="tribe-datetime-label"><?php esc_html_e( 'Start/End:', 'the-events-calendar' ); ?></td>
						<td class="tribe-datetime-block">
							<input
								autocomplete="off"
								tabindex="<?php tribe_events_tab_index(); ?>"
								type="text"
								class="tribe-datepicker tribe-field-start_date"
								name="EventStartDate"
								id="EventStartDate"
								value="<?php echo esc_attr( $EventStartDate ) ?>"
							/>
							<span class="helper-text hide-if-js"><?php esc_html_e( 'YYYY-MM-DD', 'the-events-calendar' ) ?></span>

							<input
								autocomplete="off"
								tabindex="<?php tribe_events_tab_index(); ?>"
								type="text"
								class="tribe-timepicker tribe-field-start_time"
								name="EventStartTime"
								id="EventStartTime"
								<?php echo Tribe__View_Helpers::is_24hr_format() ? 'data-format="H:i"' : '' ?>
								data-step="<?php echo esc_attr( $start_timepicker_step ); ?>"
								data-round="<?php echo esc_attr( $timepicker_round ); ?>"
								value="<?php echo esc_attr( $metabox->is_auto_draft() ? $start_timepicker_default : $EventStartTime ) ?>"
							/>
							<span class="helper-text hide-if-js"><?php esc_html_e( 'HH:MM', 'the-events-calendar' ) ?></span>

							<span class="tribe-datetime-separator"> <?php echo esc_html_x( 'to', 'Start Date Time "to" End Date Time', 'the-events-calendar' ); ?> </span>

							<input
								autocomplete="off"
								type="text"
								class="tribe-timepicker tribe-field-end_time"
								name="EventEndTime"
								id="EventEndTime"
								<?php echo Tribe__View_Helpers::is_24hr_format() ? 'data-format="H:i"' : '' ?>
								data-step="<?php echo esc_attr( $end_timepicker_step ); ?>"
								data-round="<?php echo esc_attr( $timepicker_round ); ?>"
								value="<?php echo esc_attr( $metabox->is_auto_draft() ? $end_timepicker_default : $EventEndTime ); ?>"
							/>
							<span class="helper-text hide-if-js"><?php esc_html_e( 'HH:MM', 'the-events-calendar' ) ?></span>

							<input
								autocomplete="off"
								type="text"
								class="tribe-datepicker tribe-field-end_date"
								name="EventEndDate"
								id="EventEndDate"
								value="<?php echo esc_attr( $EventEndDate ); ?>"
							/>
							<span class="helper-text hide-if-js"><?php esc_html_e( 'YYYY-MM-DD', 'the-events-calendar' ) ?></span>

							<select
								tabindex="<?php tribe_events_tab_index(); ?>"
								name="EventTimezone"
								id="event-timezone"
								class="tribe-field-timezone tribe-dropdown hide-if-js"
								data-timezone-label="<?php esc_attr_e( 'Time Zone:', 'the-events-calendar' ) ?>"
								data-timezone-value="<?php echo esc_attr( Tribe__Events__Timezones::get_event_timezone_string() ) ?>"
								data-prevent-clear
							>
								<?php echo tribe_events_timezone_choice( Tribe__Events__Timezones::get_event_timezone_string() ); ?>
							</select>

							<p class="tribe-allday">
								<input
									tabindex="<?php tribe_events_tab_index(); ?>"
									type="checkbox"
									id="allDayCheckbox"
									name="EventAllDay"
									value="yes"
									<?php echo esc_html( $isEventAllDay ); ?>
								/>
								<label for="allDayCheckbox"><?php esc_html_e( 'All Day Event', 'the-events-calendar' ); ?></label>
							</p>
						</td>
					</tr>

					<?php endif; ?>

					<tr class="event-dynamic-helper">
						<td class="label">
						</td>
						<td>
							<div class="event-dynamic-helper-text"></div>
						</td>
					</tr>
					<?php
					/**
					 * Fires after the event end date field in The Events Calendar meta box
					 * HTML outputted here should be wrapped in a table row (<tr>) that contains 2 cells (<td>s)
					 *
					 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
					 * @param boolean
					 */
					do_action( 'tribe_events_date_display', $event->ID, true );
					?>
				</table>
			</td>
		</tr>
	</table>

	<?php if ( tribe( 'tec.gutenberg' )->should_display() || tribe( 'tec.gutenberg' )->is_classic_editor_page()  ) : ?>

	<?php Tribe__Events__Linked_Posts::instance()->render_meta_box_sections( $event ); ?>

	<table id="event_url" class="eventtable">
		<tr>
			<td colspan="2" class="tribe_sectionheader">
				<h4><?php printf( esc_html__( '%s Website', 'the-events-calendar' ), $events_label_singular ); ?></h4></td>
		</tr>
		<tr>
			<td style="width:172px;"><?php esc_html_e( 'URL:', 'the-events-calendar' ); ?></td>
			<td>
				<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='EventURL' name='EventURL' size='25' value='<?php echo ( isset( $_EventURL ) ) ? esc_attr( $_EventURL ) : ''; ?>' placeholder='example.com' />
			</td>
		</tr>
		<?php
		/**
		 * Fires just after the "URL" field that appears below the Event Website header in The Events Calendar meta box
		 * HTML outputted here should be wrapped in a table row (<tr>) that contains 2 cells (<td>s)
		 *
		 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
		 * @param boolean
		 */
		do_action( 'tribe_events_url_table', $event->ID, true );
		?>
	</table>

	<?php
	/**
	 * Fires just after closing table tag after Event Website in The Events Calendar meta box
	 *
	 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
	 * @param boolean
	 */
	do_action( 'tribe_events_details_table_bottom', $event->ID, true );
	?>

	<table id="event_cost" class="eventtable">
		<?php if ( tribe_events_admin_show_cost_field() ) : ?>
			<tr>
				<td colspan="2" class="tribe_sectionheader">
					<h4><?php printf( esc_html__( '%s Cost', 'the-events-calendar' ), $events_label_singular ); ?></h4></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Currency Symbol:', 'the-events-calendar' ); ?></td>
				<td>
					<input
						tabindex="<?php tribe_events_tab_index(); ?>"
						type='text'
						id='EventCurrencySymbol'
						name='EventCurrencySymbol'
						size='2'
						value='<?php echo isset( $_EventCurrencySymbol ) ? esc_attr( $_EventCurrencySymbol ) : tribe_get_option( 'defaultCurrencySymbol', '$' ); ?>'
						class='alignleft'
					/>
					<select
						tabindex="<?php tribe_events_tab_index(); ?>"
						id="EventCurrencyPosition"
						name="EventCurrencyPosition"
						class="tribe-dropdown"
						data-prevent-clear
					>
						<?php
						if ( isset( $_EventCurrencyPosition ) && 'suffix' === $_EventCurrencyPosition ) {
							$suffix = true;
						} elseif ( isset( $_EventCurrencyPosition ) && 'prefix' === $_EventCurrencyPosition ) {
							$suffix = false;
						} elseif ( true === tribe_get_option( 'reverseCurrencyPosition', false ) ) {
							$suffix = true;
						} else {
							$suffix = false;
						}
						?>
						<option value="prefix"> <?php _ex( 'Before cost', 'Currency symbol position', 'the-events-calendar' ) ?> </option>
						<option value="suffix"<?php if ( $suffix ) {
							echo ' selected="selected"';
						} ?>><?php _ex( 'After cost', 'Currency symbol position', 'the-events-calendar' ) ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Cost:', 'the-events-calendar' ); ?></td>
				<td>
					<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='EventCost' name='EventCost' size='6' value='<?php echo ( isset( $_EventCost ) ) ? esc_attr( $_EventCost ) : ''; ?>' />
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<small><?php printf( esc_html__( 'Enter a 0 for %s that are free or leave blank to hide the field.', 'the-events-calendar' ), $events_label_plural_lowercase ); ?></small>
				</td>
			</tr>
		<?php endif; ?>
		<?php
		/**
		 * Fires just after the "Cost" field that appears below the Event Cost header in The Events Calendar meta box
		 * HTML outputted here should be wrapped in a table row (<tr>) that contains 2 cells (<td>s)
		 *
		 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
		 * @param boolean
		 */
		do_action( 'tribe_events_cost_table', $event->ID, true );
		?>
	</table>
	<?php endif; ?>
</div>
<?php
/**
 * Fires at the bottom of The Events Calendar meta box
 *
 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
 * @param boolean
 */
do_action( 'tribe_events_above_donate', $event->ID, true );

/**
 * Fires at the bottom of The Events Calendar meta box
 *
 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
 * @param boolean
 */
do_action( 'tribe_events_details_bottom', $event->ID, true );
