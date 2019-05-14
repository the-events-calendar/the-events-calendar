<?php
/**
 * View: Events Bar Views
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/views.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
?>
<div class="tribe-events__events-bar-views">
	<h3 class="tribe-common-a11y-visual-hide"><?php printf( esc_html__( '%s Views Navigation', 'the-events-calendar' ), tribe_get_event_label_singular() ); ?></h3>
	<div class="tribe-common-form-control-tabs">
		<button id="tribe-views-button" aria-haspopup="listbox" aria-labelledby="tribe-views-button" aria-expanded="true"><?php esc_html_e( 'Views', 'the-events-calendar' ); ?></button>
		<ul tabindex="-1" role="listbox" aria-activedescendant="tribe-views-list-label">
			<li role="presentation">
				<input id="tribe-views-list" name="tribe-views" type="radio" value="tribe-views-list" checked="checked" />
				<label id="tribe-views-list-label" for="tribe-views-list" role="option" aria-selected="true"><?php esc_html_e( 'List', 'the-events-calendar' ); ?></label>
			</li>
			<li role="presentation">
				<input id="tribe-views-month" name="tribe-views" type="radio" value="tribe-views-month" />
				<label id="tribe-views-month-label" for="tribe-views-month" role="option"><?php esc_html_e( 'Month', 'the-events-calendar' ); ?></label>
			</li>
			<li role="presentation">
				<input id="tribe-views-week" name="tribe-views" type="radio" value="tribe-views-week" />
				<label id="tribe-views-week-label" for="tribe-views-week" role="option"><?php esc_html_e( 'Week', 'the-events-calendar' ); ?></label>
			</li>
		</ul>
	</div>
</div>