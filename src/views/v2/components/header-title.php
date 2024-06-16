<?php
/**
 * View Component: Header Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/header-title.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 6.2.0
 * @since   6.2.0
 *
 * @var \Tribe\Events\Views\V2\Template $this         Template Engine instance rendering.
 * @var string                          $header_title The title to display.
 */

if ( empty( $header_title ) ) {
	return;
}

$header_title_element = $header_title_element ?? 'h1';
?>

<div class="tribe-events-header__title">
	<<?php echo esc_attr( $header_title_element ); ?> class="tribe-events-header__title-text">
		<?php echo esc_html( $header_title ); ?>
	</<?php echo esc_attr( $header_title_element ); ?>>
</div>
