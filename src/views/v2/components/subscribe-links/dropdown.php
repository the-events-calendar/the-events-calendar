<?php
/**
 * Component: Subscribe To Calendar Dropdown
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/subscribe-links/dropdown.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 1.0.0
 *
 * @var array $subscribe_links Array containing subscribe/export labels and links
 *
 */

?>
<div class="tec-subscribe-dropdown">
	<div class="tec-subscribe-dropdown__selector">
		<div class="tribe-common-c-btn-border tec-subscribe-dropdown__selector-button" tabindex="0">
			<span class="tec-subscribe-dropdown__selector-button-text">
				<?php echo esc_html__( 'Subscribe to calendar', 'the-events-calendar' ); ?>
			</span>
			<?php $this->template( 'components/icons/caret-down', [ 'classes' => [ 'tec-subscribe-dropdown__button-icon' ] ] ); ?>
			<div class="tec-subscribe-dropdown__selector-content" tabindex="0">
				<ul class="tec-subscribe-dropdown__selector-list">
					<?php foreach ( $items as $item ) : ?>
						<?php $this->template( 'components/subscribe-links/item', [ 'item' => $item ] ); ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</button>
	</div>
</div>
