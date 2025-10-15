<?php
/**
 * Events Calendar Pro upsell banner for recurrence.
 *
 * @var string $slug The slug of the upsell banner.
 * @var string $nonce The nonce of the upsell banner.
 *
 * @since TBD
 */

$main = Tribe__Events__Main::instance();
?>
<tr
	data-tec-conditional-content-dismiss-container
	data-tec-conditional-content-dismiss-slug="<?php echo esc_attr( $slug ); ?>"
	data-tec-conditional-content-dismiss-nonce="<?php echo esc_attr( $nonce ); ?>"
>
	<td colspan="2">
		<div class="tec-settings-form">
			<div class="tec-settings-infobox is-dismissible recurrence-upsell-banner">
				<button
					class="dismiss-button"
					data-tec-conditional-content-dismiss-button
					data-tec-conditional-content-dismiss-slug="<?php echo esc_attr( $slug ); ?>"
					data-tec-conditional-content-dismiss-nonce="<?php echo esc_attr( $nonce ); ?>"
					type="button"
					aria-label="<?php esc_attr_e( 'Dismiss this notice.', 'the-events-calendar' ); ?>"
				>
					<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
				</button>
			<img
				src="<?php echo esc_url( tribe_resource_url( 'icons/recurrence.svg', false, null, $main ) ); ?>"
				class="tec-settings-infobox-logo"
				role="presentation"
				alt=""
			>
			<p>
				<?php esc_html_e( 'Schedule multiple events in daily, weekly, monthly, or custom patterns with Events Calendar Pro.', 'the-events-calendar' ); ?>
			</p>
			<p>
				<a href="https://evnt.is/ecp" class="button" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Find out more about Events Calendar Pro', 'the-events-calendar' ); ?>
				</a>
			</p>
			</div>
		</div>
	</td>
</tr>


