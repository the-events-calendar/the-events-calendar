<?php
/**
 * View: Top Bar - Date Picker
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/top-bar/datepicker.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var string $grid_date The current calendar grid date in the `Y-m-d` format.
 * @var string $formatted_grid_date The current calendar grid date in the format specified by the "Month and year
 *                                  format" option.
 */
?>
<div class="tribe-events-c-top-bar__datepicker">
	<button
		class="tribe-common-h3 tribe-common-h--alt tribe-events-c-top-bar__datepicker-button"
		data-js="tribe-events-top-bar-datepicker-button"
	>
		<?php // @todo @fe the datepicker width seems to depend on the width of the grid title, it should not. ?>
		<time
			datetime="<?php echo esc_attr( $grid_date ); ?>"
			class="tribe-events-c-top-bar__datepicker-time"
		>
			<?php echo esc_html( $formatted_grid_date ); ?>
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
