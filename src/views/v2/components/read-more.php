<?php
/**
 * View: Read More
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/read-more.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.11
 *
 * @var WP_Post $event The event post object, with an added `permalink` property.
 */
?>
<span class="tribe-events-c-read-more-hellip"> &hellip; </span>
<div class="tribe-events-c-small-cta tribe-common-b3 tribe-events-c-read-more">
	<a
		href="<?php echo esc_url( $event->permalink ); ?>"
		class="tribe-events-c-small-cta__link tribe-common-cta tribe-common-cta--thin-alt"
	><?php esc_html_e( 'Continue Reading', 'the-events-calendar' ); ?></a>
</div>
