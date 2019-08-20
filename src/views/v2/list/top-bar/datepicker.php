<?php
/**
 * View: Top Bar - Date Picker
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/top-bar/datepicker.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.7
 *
 * @var string $today Today date in the `Y-m-d` format.
 *
 */
use Tribe__Date_Utils as Dates;

/**
 * @todo @bordoni @lucatume Abstract the handling of these dates into a method/function
 */
$default_start_date = 'now';
$selected_start_date_value = $this->get( [ 'bar', 'date' ], $default_start_date );
if ( empty( $selected_start_date_value ) ) {
	$first_event = $this->get( 'view' )->get_repository()->first();

	if ( $first_event instanceof WP_Post ) {
		$selected_start_date_value = $first_event->dates->start->format( Dates::DBDATETIMEFORMAT );
	} else {
		$selected_start_date_value = $default_start_date;
	}
}

$selected_start_datetime = strtotime( $selected_start_date_value );
$is_now = date( Dates::DBDATEFORMAT, $selected_start_datetime ) === date( Dates::DBDATEFORMAT, strtotime( $default_start_date ) );

$selected_end_date_value = $today;
$last_event = $this->get( 'view' )->get_repository()->last();

if ( $last_event instanceof WP_Post ) {
	$selected_end_date_value = $last_event->dates->start->format( Dates::DBDATEFORMAT );
}
$selected_end_datetime = strtotime( $selected_end_date_value );

$selected_start_date_label_format = tribe_get_date_format( date( 'Y' ) !== date( 'Y', $selected_start_datetime ) );
$selected_end_date_label_format = tribe_get_date_format( date( 'Y' ) !== date( 'Y', $selected_end_datetime ) );

$selected_start_date_label = date_i18n( $selected_start_date_label_format, $selected_start_datetime );
$selected_end_date_label = date_i18n( $selected_end_date_label_format, $selected_end_datetime );

?>
<div class="tribe-events-c-top-bar__datepicker">
	<button
		class="tribe-common-h2 tribe-common-h3--min-medium tribe-common-h--alt tribe-events-c-top-bar__datepicker-button"
		data-js="tribe-events-top-bar-datepicker-button"
	>
		<?php if ( $is_now ) : ?>
			<?php esc_html_e( 'Now', 'the-events-calendar' ); ?>
		<?php else: ?>
			<time datetime="<?php echo esc_attr( date_i18n( 'Y-m-d', $selected_start_datetime ) ); ?>">
				<?php echo esc_html( $selected_start_date_label ); ?>
			</time>
		<?php endif; ?>
		&mdash;
		<time datetime="<?php echo esc_attr( date_i18n( 'Y-m-d', $selected_end_datetime ) ); ?>">
			<?php echo esc_html( $selected_end_date_label ); ?>
		</time>
	</button>
	<label
		class="tribe-events-c-top-bar__datepicker-label tribe-common-a11y-visual-hide"
		for="tribe-events-top-bar-date"
	>
		<?php esc_html_e( 'Select date.', 'the-events-calendar' ); ?>
	</label>
	<input
		type="text"
		class="tribe-events-c-top-bar__datepicker-input tribe-common-a11y-visual-hide"
		data-js="tribe-events-top-bar-date"
		id="tribe-events-top-bar-date"
		name="tribe-events-views[tribe-bar-search]"
		value="<?php echo esc_attr( tribe_events_template_var( [ 'bar', 'date' ], '' ) ); ?>"
		tabindex="-1"
		autocomplete="off"
	/>
	<div class="tribe-events-c-top-bar__datepicker-container" data-js="tribe-events-top-bar-datepicker-container"></div>
</div>
