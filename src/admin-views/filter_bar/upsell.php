<?php
_deprecated_file( __FILE__, 'TBD', 'src/admin-views/settings/upsells/filter_bar.php' );
/**
 * Filter bar upsell banner.
 *
 * @since 5.14.0
 * @since TBD Drastically simplified the HTML, changed classes to use new admin styles
 */
$main = Tribe__Events__Main::instance();
?>
<div class="tec-settings__upsell">
	<div class="tec-settings__upsell-content">
		<div class="tec-settings__upsell-header">
			<img
				src="<?php echo esc_url( tribe_resource_url( 'icons/filterbar.svg', false, null, $main ) ); ?>"
				class="tec-settings__upsell-logo"
				role="presentation
				alt=""
			>
			<h3 class="tec-settings__upsell-title">
				<?php esc_html_e( 'Filter Bar', 'the-events-calendar' ); ?>
			</h3>
		</div>
		<p>
			<?php esc_html_e( 'Looking for front-end Event Filters so that your website visitors can find exactly the event they are looking for?', 'the-events-calendar' ); ?>
		</p>
		<a href="https://evnt.is/1b31" class="tec-settings__upsell-btn" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Check out our Filter Bar add-on', 'the-events-calendar' ); ?>
		</a>
	</div>
	<img
		class="tec-settings__upsell-image"
		src="<?php echo esc_url( tribe_resource_url( 'icons/filterbar-banner.png', false, null, $main ) ); ?>"
		alt="<?php esc_attr_e( 'Filter Bar Banner Icon', 'the-events-calendar' ); ?>"
	>
</div>
