<?php
/**
 * View: Events Bar Form Date Input
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/form/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */
?>
<div class="tribe-common-form-control-text">
	<label class="tribe-common-form-control-text__label" for="tribe-bar-date"><?php esc_html_e( 'Enter date. Please use the format 4 digit year hyphen 2 digit month hyphen 2 digit day.', 'the-events-calendar' ); ?></label>
	<input
		class="tribe-common-form-control-text__input tribe-common-c-search__input"
		type="text"
		id="tribe-bar-date"
		name="tribe-bar-date"
		placeholder="<?php esc_attr_e( 'Enter date', 'the-events-calendar' ); ?>"
	/>
</div>
