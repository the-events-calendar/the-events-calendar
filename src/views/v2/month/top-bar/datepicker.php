<?php
/**
 * View: Top Bar - Date Picker
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/top-bar/datepicker.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.5
 *
 */
?>
<div class="tribe-events-c-top-bar__datepicker" data-js="tribe-events-top-bar-datepicker">
	<button
		class="tribe-common-h2 tribe-common-h3--min-medium tribe-common-h--alt tribe-events-c-top-bar__datepicker-button"
		data-js="tribe-events-top-bar-datepicker-button"
	>
		<?php esc_html_e( 'Now', 'the-events-calendar' ); ?> &mdash; <time datetime="<?php echo esc_attr( date( 'Y-m-d', time() ) ); ?>"><?php echo date( 'F jS, Y', time() ); ?></time>
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
	/>
</div>
