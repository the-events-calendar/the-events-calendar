<?php
/**
 * Partial: Preview area for category colors.
 *
 * Displays a live preview of the selected colors and provides a link to accessibility documentation.
 *
 * @version 6.14.0
 *
 * @var string $value The value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<div class="tec-events-category-colors__field--preview">
	<label><?php esc_html_e( 'Preview', 'the-events-calendar' ); ?></label>
	<div class="tec-events-category-colors__preview-box">
		<span class="tec-events-category-colors__preview-text" data-default-text="<?php esc_attr_e( 'Example', 'the-events-calendar' ); ?>"></span>
	</div>
	<p class="tec-events-category-colors__description">
		<?php esc_html_e( 'Select a primary color of your choice. You can further customize your color choices afterwards.', 'the-events-calendar' ); ?>
		<a href="https://evnt.is/accessibility">
			<?php esc_html_e( 'Learn more about color selection and accessibility', 'the-events-calendar' ); ?>
		</a>
	</p>
</div>
