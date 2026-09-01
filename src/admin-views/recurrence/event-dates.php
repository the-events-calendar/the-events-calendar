<?php
/**
 * The Event Dates section: authors the additional, explicit dates of an Event.
 *
 * Rendered inside the Events datetime metabox section: the output is a table row
 * with two cells, per the `tribe_events_date_display` contract.
 *
 * @since TBD
 *
 * @var int                                                  $event_id             The Event post ID; `0` when creating a new Event.
 * @var array<int,array{date: string, start: string, end: string}> $rows           The authored additional dates.
 * @var bool                                                 $is_locked            Whether the Event recurrence is rule-based (Events Calendar Pro data).
 * @var bool                                                 $is_occurrence        Whether a single Occurrence is being edited.
 * @var string                                               $occurrence_edit_link The edit link of the recurring Event the Occurrence belongs to.
 * @var array{count: int, next_dates: array<int,string>}     $summary              The scheduled dates summary of a locked Event, formatted for display.
 */

use TEC\Events\Recurrence\Admin_Provider;

if ( $is_occurrence ) {
	?>
	<tr class="tec-events-recurrence-dates tec-events-recurrence-dates--occurrence">
		<td class="label"><?php esc_html_e( 'Event Dates', 'the-events-calendar' ); ?></td>
		<td>
			<p>
				<?php esc_html_e( 'This is a single occurrence.', 'the-events-calendar' ); ?>
				<?php if ( ! empty( $occurrence_edit_link ) ) : ?>
					<a href="<?php echo esc_url( $occurrence_edit_link ); ?>">
						<?php esc_html_e( 'Edit the recurring event to change its dates.', 'the-events-calendar' ); ?>
					</a>
				<?php endif; ?>
			</p>
		</td>
	</tr>
	<?php
	return;
}

if ( $is_locked ) {
	?>
	<tr class="tec-events-recurrence-dates tec-events-recurrence-dates--locked">
		<td class="label"><?php esc_html_e( 'Event Dates', 'the-events-calendar' ); ?></td>
		<td>
			<p>
				<?php esc_html_e( 'This event uses recurrence rules created with Events Calendar Pro. Activate Events Calendar Pro to edit them; the existing dates are preserved meanwhile.', 'the-events-calendar' ); ?>
			</p>
			<?php if ( ! empty( $summary['count'] ) ) : ?>
				<p class="description">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: the number of scheduled dates of the event. */
							_n( '%d date is scheduled:', '%d dates are scheduled:', $summary['count'], 'the-events-calendar' ),
							$summary['count']
						)
					);
					echo ' ' . esc_html( implode( ', ', $summary['next_dates'] ) );

					if ( $summary['count'] > count( $summary['next_dates'] ) ) {
						echo esc_html_x( ', …', 'The scheduled dates list of the event continues past the ones shown.', 'the-events-calendar' );
					}
					?>
				</p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
	return;
}
?>
<?php
$tec_dates_is_24hr       = Tribe__View_Helpers::is_24hr_format();
$tec_dates_default_start = $tec_dates_is_24hr ? '08:00' : '8:00am';
$tec_dates_default_end   = $tec_dates_is_24hr ? '17:00' : '5:00pm';
$tec_dates_render_row    = static function ( $index, array $row ) use ( $tec_dates_is_24hr ) {
	?>
	<div class="tec-events-recurrence-dates-row">
		<input
			autocomplete="off"
			type="text"
			class="tribe-datepicker"
			name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[<?php echo esc_attr( $index ); ?>][date]"
			value="<?php echo esc_attr( $row['date'] ); ?>"
		/>
		<input
			autocomplete="off"
			type="text"
			class="tribe-timepicker"
			<?php echo $tec_dates_is_24hr ? 'data-format="H:i"' : ''; ?>
			data-step="30"
			name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[<?php echo esc_attr( $index ); ?>][start]"
			value="<?php echo esc_attr( $row['start'] ); ?>"
		/>
		<span class="tribe-datetime-separator"> <?php echo esc_html_x( 'to', 'Start Date Time "to" End Date Time', 'the-events-calendar' ); ?> </span>
		<input
			autocomplete="off"
			type="text"
			class="tribe-timepicker"
			<?php echo $tec_dates_is_24hr ? 'data-format="H:i"' : ''; ?>
			data-step="30"
			name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[<?php echo esc_attr( $index ); ?>][end]"
			value="<?php echo esc_attr( $row['end'] ); ?>"
		/>
		<button type="button" class="button tec-events-recurrence-dates-remove" aria-label="<?php esc_attr_e( 'Remove this date', 'the-events-calendar' ); ?>">
			<svg height="20" width="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M4 10h12"/></svg>
		</button>
		<button type="button" class="button tec-events-recurrence-dates-add" aria-label="<?php esc_attr_e( 'Add another date', 'the-events-calendar' ); ?>">
			<svg height="20" width="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M10 4v12M4 10h12"/></svg>
		</button>
	</div>
	<?php
};
?>
<tr class="tec-events-recurrence-dates">
	<td class="label"><?php esc_html_e( 'Event Dates', 'the-events-calendar' ); ?></td>
	<td class="tribe-datetime-block">
		<?php wp_nonce_field( Admin_Provider::NONCE_ACTION, Admin_Provider::NONCE_ACTION . '_nonce' ); ?>

		<p>
			<input
				type="checkbox"
				id="tec-events-recurrence-dates-toggle"
				<?php checked( count( $rows ) > 0 ); ?>
			/>
			<label for="tec-events-recurrence-dates-toggle">
				<?php esc_html_e( 'Schedule this event on more dates', 'the-events-calendar' ); ?>
			</label>
		</p>

		<div id="tec-events-recurrence-dates-rows" <?php echo count( $rows ) ? '' : 'style="display:none"'; ?>>
			<p class="description">
				<?php esc_html_e( 'Each date becomes its own entry on the calendar, with its own link. The event date above is always included.', 'the-events-calendar' ); ?>
			</p>

			<div class="tec-events-recurrence-dates-list" id="tec-events-recurrence-dates-list">
				<?php foreach ( $rows as $i => $row ) : ?>
					<?php $tec_dates_render_row( $i, $row ); ?>
				<?php endforeach; ?>
			</div>
		</div>

		<script type="text/template" id="tec-events-recurrence-dates-row">
			<?php
			$tec_dates_render_row(
				'__index__',
				[
					'date'  => '',
					'start' => $tec_dates_default_start,
					'end'   => $tec_dates_default_end,
				]
			);
			?>
		</script>

		<style>
			/* Every control in a row shares one fixed height: the buttons stay square. */
			.tec-events-recurrence-dates-row { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; }
			.tec-events-recurrence-dates-row .tribe-datepicker { box-sizing: border-box; height: 40px; width: 8em; }
			.tec-events-recurrence-dates-row .tribe-timepicker { box-sizing: border-box; height: 40px; width: 6.5em; }
			.tec-events-recurrence-dates-row .tribe-datetime-separator { align-self: center; }
			.tec-events-recurrence-dates-row .tec-events-recurrence-dates-remove,
			.tec-events-recurrence-dates-row .tec-events-recurrence-dates-add { align-items: center; display: inline-flex; height: 40px; justify-content: center; padding: 0; width: 40px; }
			.tec-events-recurrence-dates-row .tec-events-recurrence-dates-remove svg,
			.tec-events-recurrence-dates-row .tec-events-recurrence-dates-add svg { display: block; }
			.tec-events-recurrence-dates-row:not(:last-child) .tec-events-recurrence-dates-add { display: none; }
			#tec-events-recurrence-dates-rows > .description { margin: 12px 0 16px; }
		</style>

		<script>
			( function () {
				var toggle = document.getElementById( 'tec-events-recurrence-dates-toggle' );
				var wrapper = document.getElementById( 'tec-events-recurrence-dates-rows' );
				var list = document.getElementById( 'tec-events-recurrence-dates-list' );
				var rowTemplate = document.getElementById( 'tec-events-recurrence-dates-row' );
				var nextIndex = <?php echo (int) count( $rows ); ?>;

				if ( ! toggle || ! wrapper || ! list || ! rowTemplate ) {
					return;
				}

				function initPickers( row ) {
					if ( ! window.jQuery ) {
						return;
					}

					var $ = window.jQuery;

					if ( $.fn.datepicker && window.tribe_datepicker_opts ) {
						$( row ).find( '.tribe-datepicker' ).datepicker( window.tribe_datepicker_opts );
					}

					if ( window.tribe_timepickers && window.tribe_timepickers.setup_timepickers ) {
						window.tribe_timepickers.setup_timepickers( $( row ).find( '.tribe-timepicker' ) );
					}
				}

				function addRow() {
					var container = document.createElement( 'div' );
					container.innerHTML = rowTemplate.innerHTML.replace( /__index__/g, String( nextIndex ) );
					nextIndex++;

					var row = container.querySelector( '.tec-events-recurrence-dates-row' );
					list.appendChild( row );
					initPickers( row );
				}

				function syncDisabled() {
					var active = toggle.checked;
					wrapper.style.display = active ? '' : 'none';
					list.querySelectorAll( 'input' ).forEach( function ( input ) {
						// Disabled inputs are not posted: toggling off and saving removes the dates.
						input.disabled = ! active;
					} );
				}

				toggle.addEventListener( 'change', function () {
					if ( toggle.checked && ! list.querySelector( '.tec-events-recurrence-dates-row' ) ) {
						addRow();
					}

					syncDisabled();
				} );

				wrapper.addEventListener( 'click', function ( event ) {
					var target = event.target;

					if ( target.classList.contains( 'tec-events-recurrence-dates-add' ) ) {
						addRow();
						return;
					}

					if ( ! target.classList.contains( 'tec-events-recurrence-dates-remove' ) ) {
						return;
					}

					var row = target.closest( '.tec-events-recurrence-dates-row' );

					if ( row ) {
						row.parentNode.removeChild( row );
					}
				} );

				syncDisabled();
			}() );
		</script>
	</td>
</tr>
