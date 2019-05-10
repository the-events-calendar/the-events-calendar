<?php
/**
 * View: Events Bar Form Keyword Input
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/form/keyword.php
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
	<label for="keyword"><?php esc_html_e( 'Keyword', 'the-events-calendar' ); ?></label>
	<input
		type="text"
		id="keyword"
		name="keyword"
		aria-label="<?php esc_attr_e( 'Search for Events by Keyword.', 'the-events-calendar' ); ?>"
		value=""
		placeholder="<?php esc_attr_e( 'Keyword', 'the-events-calendar' ); ?>"
	/>
</div>
