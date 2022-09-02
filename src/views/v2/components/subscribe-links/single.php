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
 * @version 5.12.0
 *
 * @var Link_Abstract $item Object containing subscribe/export label and url.
 *
 */

use Tribe\Events\Views\V2\iCalendar\Links\Link_Abstract;


if ( ! $item instanceof Link_Abstract ) {
	return;
}

$view = $this->get_view();

if( ! $item->is_visible( $view ) ) {
	return;
}
?>
<div class="tribe-events-c-ical tribe-common-b2 tribe-common-b3--min-medium">
	<a
		class="tribe-events-c-ical__link"
		title="<?php echo esc_attr( $item->get_single_label( $view ) ); ?>"
		href="<?php echo esc_url( $item->get_uri( $view ) ); ?>"
		target="_blank"
		rel="noopener noreferrer nofollow noindex"
	>
		<?php $this->template( 'components/icons/plus', [ 'classes' => [ 'tribe-events-c-ical__link-icon-svg' ] ] ); ?>
		<?php echo esc_html( $item->get_single_label( $view ) ); ?>
	</a>
</div>
