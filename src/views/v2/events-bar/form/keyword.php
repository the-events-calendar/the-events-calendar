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
 * @version 4.9.3
 *
 */
?>
<div class="tribe-common-form-control-text">
	<label class="tribe-common-form-control-text__label" for="keyword"><?php esc_html_e( 'Enter Keyword. Search for Events by Keyword.', 'the-events-calendar' ); ?></label>
	<input
		class="tribe-common-form-control-text__input tribe-common-c-search__input"
		type="text"
		id="keyword"
		name="keyword"
		placeholder="<?php esc_attr_e( 'Keyword', 'the-events-calendar' ); ?>"
	/>
</div>
