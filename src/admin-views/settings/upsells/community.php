<?php
/**
 * Community upsell banner.
 *
 * @since 6.7.0
 * @since 6.15.6 Updated Community Addon link to go to the proper upsell page. [TEC-5586]
 */

$main = Tribe__Events__Main::instance();
?>
<div class="tec-settings-form__upsell">
	<div class="tec-settings-form__upsell-content">
		<div class="tec-settings-form__upsell-header">
			<img
				src="<?php echo esc_url( tribe_resource_url( 'icons/community.svg', false, null, $main ) ); ?>"
				class="tec-settings-form__upsell-logo"
				role="presentation"
				alt=""
			>
			<h3 class="tec-settings-form__upsell-title">
				<?php esc_html_e( 'Community', 'the-events-calendar' ); ?>
			</h3>
		</div>
		<p>
			<?php esc_html_e( 'Offer visitors the ability to contribute to your event listings without needing backend access.', 'the-events-calendar' ); ?>
		</p>
		<a href="https://evnt.is/community" class="tec-settings-form__upsell-btn" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Check out our Community add-on', 'the-events-calendar' ); ?>
		</a>
	</div>
	<img
		class="tec-settings-form__upsell-image"
		src="<?php echo esc_url( tribe_resource_url( 'images/community-banner.png', false, null, $main ) ); ?>"
		role="presentation"
		alt=""
	>
</div>
