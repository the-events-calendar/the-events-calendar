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

if ( ! $website ) {
	return;
}
?>
<div <?php tribe_classes( $link_class ); ?>>
	<?php echo wp_kses_post( $website ); ?>
</div>