<?php
/**
 * View: Day View - Single Event Featured Icon.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/event/date/featured.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTICLE_LINK_HERE}
 *
 * @since TBD
 */

?>
<em
	class="tribe-events-calendar-day__event-datetime-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
	aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
	title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
>
</em>
<span class="tribe-events-calendar-day__event-datetime-featured-text tribe-common-a11y-visual-hide">
	<?php esc_html_e( 'Featured', 'the-events-calendar' ); ?>
</span>
