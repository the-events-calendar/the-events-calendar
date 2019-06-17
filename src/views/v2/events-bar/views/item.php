<?php
/**
 * View: Events Bar Views List Item
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/views/item.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
?>
<li class="tribe-common-form-control-tabs__list-item" role="presentation">
	<input
		class="tribe-common-form-control-tabs__input"
		id="tribe-views-list"
		name="tribe-views"
		type="radio"
		value="tribe-views-list"
		checked="checked"
	/>
	<label
		class="tribe-common-form-control-tabs__label"
		id="tribe-views-list-label"
		for="tribe-views-list"
		role="option"
		aria-selected="true"
	>
		<?php esc_html_e( 'List', 'the-events-calendar' ); ?>
	</label>
</li>
