<?php
/**
 * Events Calendar Pro upsell banner for recurrence.
 *
 * @since TBD
 */

$main = Tribe__Events__Main::instance();
?>
<tr>
	<td colspan="2">
		<div class="tec-settings-form">
			<div class="tec-settings-infobox is-dismissible recurrence-upsell-banner">
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


