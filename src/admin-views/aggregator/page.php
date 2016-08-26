<?php
$datepicker_format = tribe_get_option( 'datepickerFormat' );
?>
<div class="tribe-ea wrap" data-datepicker_format="<?php echo esc_attr( $datepicker_format ); ?>">
	<?php $this->template( 'header' ); ?>
	<div class="tribe-message-container"></div>
	<?php $this->template( 'tab' ); ?>
</div>
