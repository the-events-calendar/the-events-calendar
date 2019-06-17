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
 * @version 4.9.3
 *
 */
?>
<div class="tribe-events-c-events-bar__views">
	<h3 class="tribe-common-a11y-visual-hide"><?php printf( esc_html__( '%s Views Navigation', 'the-events-calendar' ), tribe_get_event_label_singular() ); ?></h3>
	<div class="tribe-common-form-control-tabs tribe-events-c-events-bar__views-tabs">
		<button class="tribe-common-form-control-tabs__button tribe-events-c-events-bar__views-tabs-button" id="tribe-views-button" aria-haspopup="listbox" aria-labelledby="tribe-views-button" aria-expanded="true"><?php esc_html_e( 'Views', 'the-events-calendar' ); ?></button>
		<ul class="tribe-common-form-control-tabs__list tribe-events-c-events-bar__views-tabs-list" tabindex="-1" role="listbox" aria-activedescendant="tribe-views-list-label">
			<li class="tribe-common-form-control-tabs__list-item" role="presentation">
				<input class="tribe-common-form-control-tabs__input" id="tribe-views-list" name="tribe-views" type="radio" value="tribe-views-list" checked="checked" />
				<label class="tribe-common-form-control-tabs__label" id="tribe-views-list-label" for="tribe-views-list" role="option" aria-selected="true"><?php esc_html_e( 'List', 'the-events-calendar' ); ?></label>
			</li>
			<li class="tribe-common-form-control-tabs__list-item" role="presentation">
				<input class="tribe-common-form-control-tabs__input" id="tribe-views-month" name="tribe-views" type="radio" value="tribe-views-month" />
				<label class="tribe-common-form-control-tabs__label" id="tribe-views-month-label" for="tribe-views-month" role="option"><?php esc_html_e( 'Month', 'the-events-calendar' ); ?></label>
			</li>
			<li class="tribe-common-form-control-tabs__list-item" role="presentation">
				<input class="tribe-common-form-control-tabs__input" id="tribe-views-week" name="tribe-views" type="radio" value="tribe-views-week" />
				<label class="tribe-common-form-control-tabs__label" id="tribe-views-week-label" for="tribe-views-week" role="option"><?php esc_html_e( 'Week', 'the-events-calendar' ); ?></label>
			</li>
		</ul>
	</div>
</div>
