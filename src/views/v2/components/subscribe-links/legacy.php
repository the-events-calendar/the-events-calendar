<?php
/**
 * Component: Legacy iCal Link
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/subscribe-links/legacy.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.3.0
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
		target="_blank"
		rel="noopener noreferrer nofollow"
	>
		<?php $this->template( 'components/icons/plus', [ 'classes' => [ 'tribe-events-c-ical__link-icon-svg' ] ] ); ?>
		<?php echo esc_html( $ical->link->text ); ?>
	</a>
</div>
