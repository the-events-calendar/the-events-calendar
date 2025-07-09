<?php
/**
 * Partial: Primary Color field for category colors.
 *
 * Expects $value to be set in the parent template.
 *
 * @version 6.14.0
 *
 * @var string $value The value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<div class="tec-events-category-colors__field">
	<label for="tec-events-category-colors-primary"><?php esc_html_e( 'Primary Color', 'the-events-calendar' ); ?></label>
	<input
		type="text"
		id="tec-events-category-colors-primary"
		name="tec_events_category-color[primary]"
		value="<?php echo esc_attr( $value ); ?>"
		class="tec-events-category-colors__input wp-color-picker"
		placeholder="<?php esc_attr_e( 'None', 'the-events-calendar' ); ?>"
	>
</div>
