<?php
/**
 * View: Day View Nav Disabled Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 5.0.1
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<button
		class="tribe-events-c-nav__prev tribe-common-b2 tribe-common-b1--min-medium"
		aria-label="<?php esc_attr_e( 'Previous Day', 'the-events-calendar' ); ?>"
		title="<?php esc_attr_e( 'Previous Day', 'the-events-calendar' ); ?>"
		disabled
	>
		<?php esc_html_e( 'Previous Day', 'the-events-calendar' ); ?>
	</button>
</li>
