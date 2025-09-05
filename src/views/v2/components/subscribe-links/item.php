<?php
/**
 * Component: Subscribe To Calendar Dropdown Item
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/subscribe-links/item.php
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

<li class="tribe-events-c-subscribe-dropdown__list-item tribe-events-c-subscribe-dropdown__list-item--<?php echo esc_attr( $item->get_slug() ); ?>">
	<a
		href="<?php echo esc_url( $item->get_uri( $view ) ); ?>"
		class="tribe-events-c-subscribe-dropdown__list-item-link"
		target="_blank"
		rel="noopener noreferrer nofollow noindex"
	>
		<?php echo esc_html( $item->get_label( $view ) ); ?>
	</a>
</li>
