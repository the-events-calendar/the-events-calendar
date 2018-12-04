<?php
/**
 * Block: Event Website
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-website.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7
 *
 */

if ( ! $this->attr( 'href' ) || ! $this->attr( 'urlLabel' ) ) {
	return;
}

$target = apply_filters( 'tribe_get_event_website_link_target', '_self' );
?>
<div class="tribe-block tribe-block__event-website">
	<a
		href="<?php echo esc_url( $this->attr( 'href' ) ); ?>"
		target="<?php echo esc_attr( $target ); ?>"
		<?php if ( '_blank' === $target  ) : ?> rel="noopener noreferrer" <?php endif; ?>
	>
		<?php echo esc_html( $this->attr( 'urlLabel' ) ); ?>
	</a>
</div>
