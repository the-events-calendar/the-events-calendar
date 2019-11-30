<?php
/**
 * View: Events Bar Views List Item
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/events-bar/views/list/item.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var View_Interface $view           The View that is currently rendering.
 * @var string         $url_event_date The value, `Y-m-d` format, of the `eventDate` request variable to append to the
 *                                     view URL, if any.
 *
 * @version 4.9.10
 */

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\View_Interface;

// Bail on invalid name of class
if ( ! $this->get( 'view_class_name' ) ) {
	return;
}

$view_instance   = View::make( $this->get( 'view_class_name' ) );
$view_slug       = $view_instance->get_slug();
$is_current_view = $view->get_slug() === $view_instance->get_slug();

if ( ! empty( $url_event_date ) ) {
	// Each View expects the event date in a specific format, here we account for it.
	$query_args = wp_parse_url( $view->get_url( false ), PHP_URL_QUERY );
	$view_url   = $view_instance->url_for_query_args( $url_event_date, $query_args );
} else {
	$view_url = tribe_events_get_url( array_filter( [ 'eventDisplay' => $view_slug ] ) );
}

$list_item_classes = [ 'tribe-events-c-view-selector__list-item', "tribe-events-c-view-selector__list-item--$view_slug" ];
if ( $is_current_view ) {
	$list_item_classes[] = 'tribe-events-c-view-selector__list-item--active';
}
?>
<li class="<?php echo esc_attr( implode( ' ', $list_item_classes ) ); ?>">
	<a
		href="<?php echo esc_url( $view_url ); ?>"
		class="tribe-events-c-view-selector__list-item-link"
		data-js="tribe-events-view-link"
	>
		<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--<?php echo esc_attr( $view_slug ); ?>"></span>
		<span class="tribe-events-c-view-selector__list-item-text">
			<?php echo esc_html( $view_instance->get_label() ); ?>
		</span>
	</a>
</li>
