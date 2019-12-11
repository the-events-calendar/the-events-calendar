<?php
/**
 * View: Events Bar Search Keyword Input
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/events-bar/search/keyword.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var array $bar The search bar contents.
 *
 * @version 4.9.10
 *
 */
?>
<div
	class="tribe-common-form-control-text tribe-events-c-search__input-control tribe-events-c-search__input-control--keyword"
	data-js="tribe-events-events-bar-input-control"
>
	<label class="tribe-common-form-control-text__label" for="tribe-events-events-bar-keyword">
		<?php printf( esc_html__( 'Enter Keyword. Search for %s by Keyword.', 'the-events-calendar' ), tribe_get_event_label_plural() ); ?>
	</label>
	<input
		class="tribe-common-form-control-text__input tribe-events-c-search__input tribe-events-c-search__input--icon"
		data-js="tribe-events-events-bar-input-control-input"
		type="text"
		id="tribe-events-events-bar-keyword"
		name="tribe-events-views[tribe-bar-search]"
		value="<?php echo esc_attr( tribe_events_template_var( [ 'bar', 'keyword' ], '' ) ); ?>"
		placeholder="<?php esc_attr_e( 'Search for events', 'the-events-calendar' ); ?>"
	/>
</div>
