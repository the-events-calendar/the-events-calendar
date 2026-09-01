<?php
/**
 * The Scheduled Dates metabox: lists every Occurrence generated for the Event.
 *
 * Display-only: each date offers a View link; editing a single Occurrence requires
 * the scoped-updates machinery and is not offered here.
 *
 * @since TBD
 *
 * @var int $event_id The Event post ID the list is rendered for.
 * @var array{
 *     view: string,
 *     page: int,
 *     per_page: int,
 *     total: int,
 *     total_pages: int,
 *     rows: array<int,array{provisional_id: int, start: DateTimeImmutable, end: DateTimeImmutable}>
 * } $data The current page of the list.
 */

use TEC\Events\Recurrence\Occurrences_List;

$tec_dates_datetime_format = tribe_get_datetime_format( true );
$tec_dates_time_format     = tribe_get_time_format();
$tec_dates_upcoming_url    = remove_query_arg( Occurrences_List::PAGE_VAR, remove_query_arg( Occurrences_List::VIEW_VAR ) );
$tec_dates_all_url         = add_query_arg( Occurrences_List::VIEW_VAR, 'all', $tec_dates_upcoming_url );
?>
<div class="tec-events-recurrence-occurrences">
	<p class="description">
		<?php esc_html_e( 'Every date this event is scheduled on. Each date is its own entry on the calendar, with its own link.', 'the-events-calendar' ); ?>
	</p>

	<p>
		<?php if ( 'upcoming' === $data['view'] ) : ?>
			<strong><?php esc_html_e( 'Upcoming', 'the-events-calendar' ); ?></strong>
		<?php else : ?>
			<a href="<?php echo esc_url( $tec_dates_upcoming_url ); ?>"><?php esc_html_e( 'Upcoming', 'the-events-calendar' ); ?></a>
		<?php endif; ?>
		|
		<?php if ( 'all' === $data['view'] ) : ?>
			<strong><?php esc_html_e( 'All', 'the-events-calendar' ); ?></strong>
		<?php else : ?>
			<a href="<?php echo esc_url( $tec_dates_all_url ); ?>"><?php esc_html_e( 'All', 'the-events-calendar' ); ?></a>
		<?php endif; ?>
	</p>

	<?php if ( ! count( $data['rows'] ) ) : ?>
		<p>
			<?php
			if ( 'upcoming' === $data['view'] ) {
				esc_html_e( 'No upcoming dates. Switch to "All" to see past ones.', 'the-events-calendar' );
			} else {
				esc_html_e( 'No dates are scheduled.', 'the-events-calendar' );
			}
			?>
		</p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'the-events-calendar' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'the-events-calendar' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data['rows'] as $tec_dates_row ) : ?>
					<?php
					// `wp_date()` over `date_i18n()`: the rows carry real timestamps in the site timezone.
					$tec_dates_timezone = $tec_dates_row['start']->getTimezone();
					$tec_dates_start    = wp_date( $tec_dates_datetime_format, (int) $tec_dates_row['start']->format( 'U' ), $tec_dates_timezone );
					$tec_dates_end      = $tec_dates_row['start']->format( 'Y-m-d' ) === $tec_dates_row['end']->format( 'Y-m-d' )
						? wp_date( $tec_dates_time_format, (int) $tec_dates_row['end']->format( 'U' ), $tec_dates_timezone )
						: wp_date( $tec_dates_datetime_format, (int) $tec_dates_row['end']->format( 'U' ), $tec_dates_timezone );
					?>
					<tr>
						<td>
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: the start date and time of the occurrence. 2: its end time (or end date and time when it spans days). */
									_x( '%1$s – %2$s', 'The scheduled date row of the event, as a start and end range.', 'the-events-calendar' ),
									$tec_dates_start,
									$tec_dates_end
								)
							);
							?>
						</td>
						<td>
							<a href="<?php echo esc_url( get_permalink( $tec_dates_row['provisional_id'] ) ); ?>" target="_blank" rel="noreferrer noopener">
								<?php esc_html_e( 'View', 'the-events-calendar' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $data['total_pages'] > 1 ) : ?>
			<p>
				<?php if ( $data['page'] > 1 ) : ?>
					<a href="<?php echo esc_url( add_query_arg( Occurrences_List::PAGE_VAR, $data['page'] - 1 ) ); ?>">&lsaquo; <?php esc_html_e( 'Previous', 'the-events-calendar' ); ?></a>
				<?php endif; ?>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: the current page of the scheduled dates list. 2: its total number of pages. */
						__( 'Page %1$d of %2$d', 'the-events-calendar' ),
						$data['page'],
						$data['total_pages']
					)
				);
				?>
				<?php if ( $data['page'] < $data['total_pages'] ) : ?>
					<a href="<?php echo esc_url( add_query_arg( Occurrences_List::PAGE_VAR, $data['page'] + 1 ) ); ?>">
						<?php esc_html_e( 'Next', 'the-events-calendar' ); ?> &rsaquo;</a>
				<?php endif; ?>
			</p>
		<?php endif; ?>
	<?php endif; ?>
</div>
