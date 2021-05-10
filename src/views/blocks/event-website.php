<?php
/**
 * Block: Event Website
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-website.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7
 *
 */
$href = $this->attr( 'href' );

if ( ! $href || ! $this->attr( 'urlLabel' ) ) {
	return;
}

/**
 * Filter the target attribute for the event website link
 *
 * @since 5.1.0
 *
 * @param string $target The target attribute string. Defaults to "_self".
 * @param string $href   The link URL.
 */
$target = apply_filters( 'tribe_get_event_website_link_target', '_self', $href, $this->context['post_id'] );
?>
<div class="tribe-block tribe-block__event-website">
	<a
		href="<?php echo esc_url( $href ); ?>"
		target="<?php echo esc_attr( $target ); ?>"
		<?php if ( '_blank' === $target  ) : ?> rel="noopener noreferrer" <?php endif; ?>
	>
		<?php echo esc_html( $this->attr( 'urlLabel' ) ); ?>
	</a>
</div>
