<?php
/**
 * The Event Dates metabox: authors the additional, explicit dates of an Event.
 *
 * @since TBD
 *
 * @var WP_Post                                                                              $post       The Event post being edited.
 * @var array<int,array{start: string, end: string, occurrence_id: int, provisional_id: ?int}> $additional The additional Occurrence dates, the first (Event) one excluded.
 * @var bool                                                                                 $has_rules  Whether the Event recurrence is rule-based (Events Calendar Pro data).
 */

use TEC\Events\Recurrence\Admin_Provider;

if ( $has_rules ) {
	?>
	<p>
		<?php esc_html_e( 'This event uses recurrence rules created with Events Calendar Pro. Activate Events Calendar Pro to edit them; the existing dates are preserved meanwhile.', 'the-events-calendar' ); ?>
	</p>
	<?php
	return;
}

wp_nonce_field( Admin_Provider::NONCE_ACTION, Admin_Provider::NONCE_ACTION . '_nonce' );
?>
<p class="description">
	<?php esc_html_e( 'Add more dates to this event, one by one. Each date becomes its own entry on the calendar, with its own link. The event date above is always the first one.', 'the-events-calendar' ); ?>
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
		<?php foreach ( $additional as $i => $date ) : ?>
			<tr>
				<td>
					<input
						type="date"
						name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[<?php echo (int) $i; ?>][date]"
						value="<?php echo esc_attr( substr( $date['start'], 0, 10 ) ); ?>"
					/>
				</td>
				<td>
					<input
						type="time"
						name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[<?php echo (int) $i; ?>][start]"
						value="<?php echo esc_attr( substr( $date['start'], 11, 5 ) ); ?>"
					/>
				</td>
				<td>
					<input
						type="time"
						name="<?php echo esc_attr( Admin_Provider::FIELD ); ?>[<?php echo (int) $i; ?>][end]"
						value="<?php echo esc_attr( substr( $date['end'], 11, 5 ) ); ?>"
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
		var nextIndex = <?php echo (int) count( $additional ); ?>;

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
