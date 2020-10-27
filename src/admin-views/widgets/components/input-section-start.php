<?php
/**
 * Admin View: Widget Input Section Start.
 * The beginning of a container for sectioned inputs.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/widgets/components/input-section-start.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @version TBD
 *
 * @var string $title The (optional) section title.
 */

 $section_classes = [
	 'tribe-events-widget-admin-form__input-section',
 ];

 if ( ! empty( $classes ) ) {
	 if ( ! is_array( $classes ) ) {
		$classes = explode( ' ', $classes );
	 }

	$section_classes = array_merge( $section_classes, $classes );
 }

?>
<div <?php tribe_classes( $section_classes ); ?>>
	<?php if ( ! empty( $title ) ) : ?>
		<h4><?php echo esc_html( $title ); ?></h4>
	<?php endif; ?>
