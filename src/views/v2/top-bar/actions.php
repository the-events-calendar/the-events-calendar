<?php
/**
 * View: Top Bar - Actions
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/top-bar/actions.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */
?>
<div class="tribe-events-c-top-bar__actions">
	<div class="tribe-common-form-control-toggle">
		<input class="tribe-common-form-control-toggle__input" id="hide-recurring" name="hide-recurring" type="checkbox" value="false" />
		<label class="tribe-common-form-control-toggle__label" for="hide-recurring"><?php esc_html_e( 'Hide Recurring Events', 'the-events-calendar' ); ?></label>
	</div>
</div>
