<?php
/**
 * Component: Subscribe To Calendar
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/ical-link.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 1.0.0
 *
 * @var array $subscribe_links Array containing subscribe/export labels and links
 *
 * @package Tribe\Extensions\Subscribe_To_Calendar
 */

?>
<div class="tec-subscribe-dropdown">
	<div class="tec-subscribe-dropdown__selector">
		<button class="tribe-common-c-btn tec-subscribe-dropdown__selector-button">
			<span class="tec-subscribe-dropdown__selector-button-text">
				<?php echo esc_html__( 'Subscribe to calendar', 'the-events-calendar' ); ?>
			</span>
			<?php $this->template( 'components/icons/caret-down', [ 'classes' => [ 'tec-subscribe-dropdown__button-icon-caret-svg' ] ] ); ?>
			</svg>
		</button>
		<div class="tec-subscribe-dropdown__selector-content">
			<ul class="tec-subscribe-dropdown__selector-list">
				<?php foreach ( $subscribe_links as $subscribe_link ) : ?>
					<li class="tec-subscribe-dropdown__selector-list-item">
						<a href="<?php echo esc_url( $subscribe_link['uri'] ); ?>"class="tec-subscribe-dropdown__selector-list-item-link">
							<span class="tec-subscribe-dropdown__selector-list-item-text">
								<?php echo esc_html( $subscribe_link['label'] ); ?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>
