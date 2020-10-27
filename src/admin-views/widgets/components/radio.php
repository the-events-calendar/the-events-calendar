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
 * @version TBD
 *
 * @var string $label Label for the radio.
 * @var string $value Value for the radio.
 * @var string $id    ID of the radio.
 * @var string $name  Name attribute for the radio.
 */

 foreach ( $options as $option ) : ?>
	<p
			class="tribe-widget-radio tribe-common-form-control-radio"
	>

		<label
				class="tribe-common-form-control-radio__label"
		>
			<input
					class="tribe-common-form-control-radio__input"
					name="<?php echo esc_attr( $name ); ?>"
					type="radio"
					value="<?php echo esc_attr( $option['value'] ); ?>"
					<?php checked( $option['value'], $value ); ?>
			/>
			<?php echo esc_html( $option['text'] ); ?>
		</label>
	</p>
<?php endforeach; ?>
