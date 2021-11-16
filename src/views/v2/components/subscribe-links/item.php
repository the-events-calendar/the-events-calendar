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
 * @version TBD
 *
 * @var array $item Array containing subscribe/export label and url.
 *
 */

if( empty( $item['display'] ) ) {
	return;
}
?>

<li class="tec-subscribe-dropdown__selector-list-item">
	<a
		href="<?php echo esc_url( $item['uri'] ); ?>"
		class="tec-subscribe-dropdown__selector-list-item-link"
		tabindex="0"
	>
		<span class="tec-subscribe-dropdown__selector-list-item-text">
			<?php echo esc_html( $item['label'] ); ?>
		</span>
	</a>
</li>
