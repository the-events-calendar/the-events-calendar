<?php
/**
 * Filter bar upsell banner.
 *
 * @since 5.14.0
 * @since 6.7.0 Deprecated
 * @deprecated
 */
_deprecated_file( __FILE__, '6.7.0', 'src/admin-views/settings/upsells/filter_bar.php' );
$main = Tribe__Events__Main::instance();
?>
<div class="tec-settings-form__upsell">
	<div class="tec-settings-form__upsell-header">
		<img
			src="<?php echo esc_url( tribe_resource_url( 'icons/filterbar.svg', false, null, $main ) ); ?>"
			class="tec-settings-form__upsell-logo"
			role="presentation"
			alt=""
		>
		<h3 class="tec-settings-form__upsell-title">
			<?php esc_html_e( 'Filter Bar', 'the-events-calendar' ); ?>
		</h3>
	</div>
	<div class="tec-settings-form__upsell-content">
		<p>
			<?php esc_html_e( 'Looking for front-end Event Filters so that your website visitors can find exactly the event they are looking for?', 'the-events-calendar' ); ?>
		</p>
	</div>
	<a href="https://evnt.is/1b31" class="tec-settings-form__upsell-btn" target="_blank" rel="noopener noreferrer">
		<?php esc_html_e( 'Check out our Filter Bar add-on', 'the-events-calendar' ); ?>
	</a>
	<img
		class="tec-settings-form__upsell-image"
		src="<?php echo esc_url( tribe_resource_url( 'icons/filterbar-banner.png', false, null, $main ) ); ?>"
		alt="<?php esc_attr_e( 'Filter Bar Banner Icon', 'the-events-calendar' ); ?>"
	>
</div>
