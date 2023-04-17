<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Venue > Address.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/emails/template-parts/body/event/venue/address.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/tickets-emails-tpl Help article for Tickets Emails template files.
 *
 * @version TBD
 *
 * @since TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event ) && ! $event->venues->count() ) {
	return;
}

$separator            = '<br />';
$append_after_address = array_filter( array_map( 'trim', [ $venue->state_province, $venue->state, $venue->province ] ) );

?>
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
	<tr>
		<td style="text-align:center;vertical-align:top;display:inline-block;" valign="top" align="center">
			<img
				width="20"
				height="28"
				style="width:20px;height:28px;display:block;"
				src="<?php echo plugins_url( '/the-events-calendar/src/resources/icons/map-pin.svg' ) ?>"
			/>
		</td>
		<td style="padding:0;text-align:left">
		<?php
			echo esc_html( $venue->address );

			echo '<br />';

			if ( ! empty( $venue->city ) ) :
				echo esc_html( $venue->city );
				if ( $append_after_address ) :
					echo $separator;
				endif;
			endif;

			if ( $append_after_address ) :
				echo esc_html( reset( $append_after_address ) );
			endif;

			if ( ! empty( $venue->country ) ):
				echo $separator . esc_html( $venue->country );
			endif;

			if ( ! empty( $venue->directions_link ) ) :
				?>
				<br />
				<a href="<?php echo esc_url( $venue->directions_link ); ?>"><?php esc_html_e( 'Get Directions', 'the-events-calendar' ); ?></a>
			<?php
			endif;
			?>
		</td>
	</tr>
</table>
