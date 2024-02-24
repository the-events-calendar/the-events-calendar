<?php
/**
 * PDF Pass: Body - Venue Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets-wallet-plus/pdf/pass/body/event/venue/title.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/event-tickets-wallet-plus-tpl Help article for Wallet Plus template files.
 *
 * @since 6.2.8
 *
 * @version 6.2.8
 */

?>
<div class="tec-tickets__wallet-plus-pdf-event-venue-title">
	<?php echo esc_html( $venue->post_title ); ?>
</div>
