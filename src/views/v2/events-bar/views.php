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
<div class="tribe-events-calendar-events-bar--views">
	<h3 class="tribe-common-a11y-visual-hide"><?php printf( esc_html__( '%s Views Navigation', 'the-events-calendar' ), tribe_get_event_label_singular() ); ?></h3>
	<div class="tribe-common-form-control-tabs">
		<button id="tabButton" aria-haspopup="listbox" aria-labelledby="tabButton" aria-expanded="true"><?php esc_html_e( 'Views', 'the-events-calendar' ); ?></button>
		<ul tabindex="-1" role="listbox" aria-activedescendant="tabOneLabel">
			<li role="presentation">
				<input id="tabList" name="tabGroup" type="radio" value="tabList" checked="checked" />
				<label id="tabListLabel" for="tabList" role="option" aria-selected="true"><?php esc_html_e( 'List', 'the-events-calendar' ); ?></label>
			</li>
			<li role="presentation">
				<input id="tabMonth" name="tabGroup" type="radio" value="tabMonth" />
				<label id="tabMonthLabel" for="tabMonth" role="option"><?php esc_html_e( 'Month', 'the-events-calendar' ); ?></label>
			</li>
			<li role="presentation">
				<input id="tabWeek" name="tabGroup" type="radio" value="tabWeek" />
				<label id="tabWeekLabel" for="tabWeek" role="option"><?php esc_html_e( 'Week', 'the-events-calendar' ); ?></label>
			</li>
		</ul>
	</div>
</div>