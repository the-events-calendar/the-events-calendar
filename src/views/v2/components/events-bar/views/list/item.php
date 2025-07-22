<?php
/**
 * View: Events Bar Views List Item
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/events-bar/views/list/item.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $view_slug        Slug of the current view.
 * @var string $public_view_slug Slug of the view currently being listed.
 * @var object $public_view_data Data of the view currently being listed.
 *
 * @since 5.3.0
 * @since 6.12.0 Add aria-label to the view selector list item link.
 * @since 6.14.1 Add aria-current to active list item.
 *
 * @version 6.14.1
 */

$list_item_classes = [ 'tribe-events-c-view-selector__list-item', "tribe-events-c-view-selector__list-item--$public_view_slug" ];
$is_active         = false;

if ( $view_slug === $public_view_slug ) {
	$list_item_classes[] = 'tribe-events-c-view-selector__list-item--active';
	$is_active           = true;
}
?>
<li
	<?php tec_classes( $list_item_classes ); ?>
>
	<a
		href="<?php echo esc_url( $public_view_data->view_url ); ?>"
		class="tribe-events-c-view-selector__list-item-link"
		data-js="tribe-events-view-link"
		aria-label="<?php echo esc_attr( $public_view_data->aria_label ); ?>"
		<?php if ( $is_active ) : ?>
		aria-current="true"
		<?php endif; ?>
	>
		<span class="tribe-events-c-view-selector__list-item-icon">
			<?php $this->template( 'components/icons/' . esc_attr( $public_view_slug ), [ 'classes' => [ 'tribe-events-c-view-selector__list-item-icon-svg' ] ] ); ?>
		</span>
		<span class="tribe-events-c-view-selector__list-item-text">
			<?php echo esc_html( $public_view_data->view_label ); ?>
		</span>
	</a>
</li>
