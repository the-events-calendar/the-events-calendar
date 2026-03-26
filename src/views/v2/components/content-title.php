<?php
/**
 * View Component: Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/content-title.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 6.15.12
 * @since 6.2.0
 * @since 6.15.12 Changed `span` to `h2`. [TEC-5636]
 * @since 6.15.14 Added the ability to change the heading tag. [TEC-5617]
 * @since 6.15.16 Use the variable `$show_content_title` to display the title. [TEC-5733]
 * @since 6.15.16 Downgrade to h2 when header-title exists to maintain proper heading hierarchy. [TEC-5733]
 *
 * @var \Tribe\Events\Views\V2\Template $this Template Engine instance rendering.
 * @var string                          $content_title The title to display.
 */

// Get heading tag from View helper method.
if ( $this->get( 'view' ) instanceof Tribe\Events\Views\V2\View ) {
	$heading_tag = $this->get( 'view' )->get_content_title_heading_tag( 'h1' );
} else {
	$heading_tag = 'h1';
}

// If header_title exists, this title should be h2 (header-title is the primary h1).
if ( ! empty( $header_title ) && 'h1' === $heading_tag ) {
	$heading_tag = 'h2';
}
$heading_text = $content_title ?: tribe_get_event_label_plural();

// Choose visual class based on whether to show the title.
$visual_class = ! empty( $show_content_title )
	? 'tribe-events-header__content-title-text tribe-common-h7 tribe-common-h3--min-medium tribe-common-h--alt'
	: 'screen-reader-text tec-a11y-title-hidden';
?>
<div class="tribe-events-header__content-title">
	<?php

	printf(
		'<%1$s class="%2$s">%3$s</%1$s>',
		$heading_tag, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped
		esc_attr( $visual_class ),
		esc_html( $heading_text )
	);
	?>
</div>
