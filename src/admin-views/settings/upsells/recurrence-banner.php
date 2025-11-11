<?php
/**
 * Events Calendar Pro upsell banner for recurrence.
 *
 * @var string $slug The slug of the upsell banner.
 * @var string $nonce The nonce of the upsell banner.
 *
 * @since 6.15.9
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
				<h4 class="tec-settings-infobox-title">Need Recurring Events?</h4>
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
			<p class="tec-settings-infobox__button_wrap">
				<a href="https://evnt.is/ecp" class="button" target="_blank" rel="noopener noreferrer">
					<?php
					printf(
						/* translators: %1$s: opening span tag, contents not show on smaller screens %2$s: closing span tag */
						esc_html__( 'Find out more %1$s about Events Calendar Pro%2$s', 'the-events-calendar' ),
						'<span class="tec-visually-hidden-md">',
						'</span>'
					);
					?>
				</a>
			</p>
			</div>
		</div>
	</td>
</tr>
