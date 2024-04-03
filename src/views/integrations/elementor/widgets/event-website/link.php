<?php
/**
 * View: Elementor Event Website widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-website/header.php
 *
 * @since TBD
 *
 * @var string $link_class The class for the link.
 * @var string $website    The event website link.
 */

if ( ! $this->has_event() ) {
	return;
}
?>
<div <?php tribe_classes( $link_class ); ?>>
	<?php echo tribe_get_event_website_link( $this->get_event() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?>
</div>
