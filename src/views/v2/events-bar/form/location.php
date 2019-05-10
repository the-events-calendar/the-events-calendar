<?php
/**
 * View: Events Bar Form Location Input
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/form/location.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
?>
<div class="tribe-common-form-control-text">
	<label for="location"><?php esc_html_e( 'Location', 'the-events-calendar' ); ?></label>
	<input
		type="text"
		id="location"
		name="location"
		aria-label="<?php esc_attr_e( 'Search for Events by Location.', 'the-events-calendar' ); ?>"
		value=""
		placeholder="<?php esc_attr_e( 'Location', 'the-events-calendar' ); ?>"
	/>
</div>