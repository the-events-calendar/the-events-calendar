<?php
/**
 * View: Events Bar Views List Item
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/views/item.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
use Tribe\Events\Views\V2\View;


// Bail on invalid name of class
if ( ! $this->get( 'view_class_name' ) ) {
	return;
}

$view_instance = View::make( $this->get( 'view_class_name' ) );
$view_slug = $view_instance->get_slug();
$is_current_view = $view->get_slug() === $view_instance->get_slug();
$view_url = tribe_events_get_url( [ 'eventDisplay' => $view_slug ], $this->get( 'view' )->get_url() );

$link_classes = [ 'tribe-events-c-view-selector__list-item-link' ];
if ( $is_current_view ) {
	$link_classes[] = 'tribe-events-c-view-selector__list-item-link--active';
}
?>
<li class="tribe-events-c-view-selector__list-item">
	<a
		href="<?php echo esc_url( $view_url ); ?>"
		class="<?php echo esc_attr( implode( ' ', $link_classes ) ); ?>"
		data-js="tribe-events-view-link"
	>
		<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--<?php echo esc_attr( $view_slug ); ?>"></span>
		<span class="tribe-events-c-view-selector__list-item-text">
			<?php echo esc_html( $view_instance->get_label() ); ?>
		</span>
	</a>
</li>
