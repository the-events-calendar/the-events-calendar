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
 * @var object $public_view Data of the view currently being listed.
 *
 * @version TBD
 */

$list_item_classes = [ 'tribe-events-c-view-selector__list-item', "tribe-events-c-view-selector__list-item--$public_view->view_slug" ];
if ( $public_view->is_current_view ) {
	$list_item_classes[] = 'tribe-events-c-view-selector__list-item--active';
}
?>
<li class="<?php echo esc_attr( implode( ' ', $list_item_classes ) ); ?>">
	<a
		href="<?php echo esc_url( $public_view->view_url ); ?>"
		class="tribe-events-c-view-selector__list-item-link"
		data-js="tribe-events-view-link"
	>
		<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--<?php echo esc_attr( $public_view->view_slug ); ?>"></span>
		<span class="tribe-events-c-view-selector__list-item-text">
			<?php echo esc_html( $public_view->view_label ); ?>
		</span>
	</a>
</li>
