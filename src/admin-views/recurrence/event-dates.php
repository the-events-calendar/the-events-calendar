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
<tr class="tec-events-recurrence-dates">
	<td class="label"><?php esc_html_e( 'Event Dates', 'the-events-calendar' ); ?></td>
	<td>
		<?php wp_nonce_field( Admin_Provider::NONCE_ACTION, Admin_Provider::NONCE_ACTION . '_nonce' ); ?>
		<p class="description">
			<?php esc_html_e( 'Add more dates to this event, one by one. Each date becomes its own entry on the calendar, with its own link. The event date above is always included.', 'the-events-calendar' ); ?>
		</p>

		<table class="widefat striped" id="tec-events-recurrence-dates-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'the-events-calendar' ); ?></th>
					<th><?php esc_html_e( 'Start time', 'the-events-calendar' ); ?></th>
					<th><?php esc_html_e( 'End time', 'the-events-calendar' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $i => $row ) : ?>
					<tr>
						<td>
							<input
								type="date"
								name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[<?php echo (int) $i; ?>][date]"
								value="<?php echo esc_attr( $row['date'] ); ?>"
							/>
						</td>
						<td>
							<input
								type="time"
								name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[<?php echo (int) $i; ?>][start]"
								value="<?php echo esc_attr( $row['start'] ); ?>"
							/>
						</td>
						<td>
							<input
								type="time"
								name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[<?php echo (int) $i; ?>][end]"
								value="<?php echo esc_attr( $row['end'] ); ?>"
							/>
						</td>
						<td>
							<button type="button" class="button-link-delete tec-events-recurrence-dates-remove">
								<?php esc_html_e( 'Remove', 'the-events-calendar' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p>
			<button type="button" class="button" id="tec-events-recurrence-dates-add">
				<?php esc_html_e( 'Add another date', 'the-events-calendar' ); ?>
			</button>
		</p>

		<script type="text/template" id="tec-events-recurrence-dates-row">
			<tr>
				<td><input type="date" name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[__index__][date]" value="" /></td>
				<td><input type="time" name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[__index__][start]" value="" /></td>
				<td><input type="time" name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[__index__][end]" value="" /></td>
				<td>
					<button type="button" class="button-link-delete tec-events-recurrence-dates-remove">
						<?php esc_html_e( 'Remove', 'the-events-calendar' ); ?>
					</button>
				</td>
			</tr>
		</script>

		<script>
			( function () {
				var table = document.getElementById( 'tec-events-recurrence-dates-table' );
				var addButton = document.getElementById( 'tec-events-recurrence-dates-add' );
				var rowTemplate = document.getElementById( 'tec-events-recurrence-dates-row' );
				var nextIndex = <?php echo (int) count( $rows ); ?>;

				if ( ! table || ! addButton || ! rowTemplate ) {
					return;
				}

				addButton.addEventListener( 'click', function () {
					var container = document.createElement( 'tbody' );
					container.innerHTML = rowTemplate.innerHTML.replace( /__index__/g, String( nextIndex ) );
					nextIndex++;
					table.querySelector( 'tbody' ).appendChild( container.querySelector( 'tr' ) );
				} );

				table.addEventListener( 'click', function ( event ) {
					if ( ! event.target.classList.contains( 'tec-events-recurrence-dates-remove' ) ) {
						return;
					}

					var row = event.target.closest( 'tr' );

					if ( row ) {
						row.parentNode.removeChild( row );
					}
				} );
			}() );
		</script>
	</td>
</tr>
