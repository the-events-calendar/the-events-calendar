<?php
/**
 * Component: Subscribe To Calendar Single Link
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/subscribe-links/single.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version TBD
 *
 * @var array $item Array containing subscribe/export label and url.
 *
 */
?>
<div class="tribe-events-c-ical tribe-common-b2 tribe-common-b3--min-medium">
	<a
		class="tribe-events-c-ical__link"
		title="<?php echo esc_attr( $item['label'] ); ?>"
		href="<?php echo esc_url( $item['uri'] ); ?>"
	>
		<?php $this->template( 'components/icons/plus', [ 'classes' => [ 'tribe-events-c-ical__link-icon-svg' ] ] ); ?>
		<?php echo esc_html( $item['label'] ); ?>
	</a>
</div>
