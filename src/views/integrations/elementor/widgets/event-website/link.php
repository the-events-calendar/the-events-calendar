<?php
/**
 * View: Elementor Event Website widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-website/header.php
 *
 * @since 6.4.0
 *
 * @var string $link_class The class for the link.
 * @var string $website    The event website link.
 */

?>
<div <?php tec_classes( $link_class ); ?>>
	<?php echo $website; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?>
</div>
