<?php
/**
 * Component: iCal Link
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/ical-link.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.13
 *
 * @var object $ical Object containing iCal data
 */

if ( empty( $ical->display_link ) ) {
	return;
}

?>
<div class="tribe-events-c-ical tribe-common-b1">
	<a
		class="tribe-events-c-ical__link tribe-common-anchor-alt"
		title="<?php echo esc_attr( $ical->link->title ); ?>"
		href="<?php echo esc_url( $ical->link->url ); ?>"
	><?php echo esc_html( $ical->link->text ); ?></a>
</div>

