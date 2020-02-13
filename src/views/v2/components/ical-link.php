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
 * @version 5.0.1
 *
 * @var object $ical Object containing iCal data
 */

if ( empty( $ical->display_link ) ) {
	return;
}

?>
<div class="tribe-events-c-ical tribe-common-b2 tribe-common-b3--min-medium">
	<a
		class="tribe-events-c-ical__link"
		title="<?php echo esc_attr( $ical->link->title ); ?>"
		href="<?php echo esc_url( $ical->link->url ); ?>"
	><?php echo esc_html( $ical->link->text ); ?></a>
</div>

