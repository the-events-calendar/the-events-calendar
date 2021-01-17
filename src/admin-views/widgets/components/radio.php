<?php
/**
 * Admin View: Widget Radio Component
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/widgets/components/radio.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @version 5.3.0
 *
 * @var string $label        Label for the radio group.
 * @var string $value        Value for the radio group.
 * @var string $button_value Value for the individual button.
 * @var string $name         Name attribute for the radio.
 * @var string $id           ID attribute for the radio.
 * @var string $dependency   The dependency attributes for the control wrapper.
 */

?>
<div
	class="tribe-widget-form-control tribe-widget-form-control--radio"
	<?php
	// Not escaped - contains html (data-attr="value").
	echo $dependency; // phpcs:ignore
	?>
>
	<input
		class="tribe-widget-form-control__input"
		id="<?php echo esc_attr( $id . '-' . strtolower( $button_value ) ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		type="radio"
		value="<?php echo esc_attr( $button_value ); ?>"
		<?php checked( $button_value, $value ); ?>
	/>
	<label
		class="tribe-widget-form-control__label"
		for="<?php echo esc_attr( $id . '-' . strtolower( $button_value ) ); ?>"
	>
		<?php echo esc_html( $label ); ?>
	</label>
</div>
