<?php
/**
 * View: Events Bar Search Keyword Input
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/search/keyword.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var array $bar The search bar contents.
 *
 * @version TBD
 *
 */
?>
<div class="tribe-common-form-control-text tribe-common-c-search__input-control--keyword">
	<label class="tribe-common-form-control-text__label" for="keyword"><?php esc_html_e( 'Enter Keyword. Search for Events by Keyword.', 'the-events-calendar' ); ?></label>
	<input
		class="tribe-common-form-control-text__input tribe-common-c-search__input tribe-common-c-search__input--icon"
		type="text"
		id="keyword"
		name="tribe-events-views[tribe-bar-search]"
		value="<?php echo esc_attr( tribe_events_template_var( [ 'bar', 'keyword' ], '' ) ); ?>"
		placeholder="<?php esc_attr_e( 'Search for events', 'the-events-calendar' ); ?>"
	/>
</div>
