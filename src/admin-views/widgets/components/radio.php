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
 * @var string       $label   Label for the radio group.
 * @var string       $value   Value for the radio.
 * @var string       $name    Name attribute for the radio.
 * @var array<array> $options Data for the individual radio buttons.
 */

if ( ! empty( $label ) ) : ?>
	<p><?php echo esc_html( $label ); ?></p>
	<?php
endif;

$i = 1;

foreach ( $options as $option ) :
	$radio_id = $id . $i;
	?>
	<p class="tribe-widget-radio tribe-common-form-control-radio">
		<input
			class="tribe-common-form-control-radio__input"
			id="<?php echo esc_attr( $radio_id ); ?>"
			name="<?php echo esc_attr( $name ); ?>"
			type="radio"
			value="<?php echo esc_html( $option['value'] ); ?>"
			<?php checked( $option['value'], $value ); ?>
		/>
		<label
				class="tribe-common-form-control-radio__label"
				for="<?php echo esc_attr( $radio_id ); ?>"
		>
			<?php echo esc_html( $option['text'] ); ?>
		</label>
	</p>
	<?php
	$i++;
endforeach;
?>
