<?php
$datepicker_format = tribe_get_option( 'datepickerFormat' );

$state_class = 'tribe-aggregator-inactive';
if ( Tribe__Events__Aggregator::instance()->is_service_active() ) {
	$state_class = 'tribe-aggregator-active';
}
?>
<div class="tribe-ea wrap <?php echo esc_attr( $state_class ); ?>" data-datepicker_format="<?php echo esc_attr( $datepicker_format ); ?>">
	<?php $this->template( 'header' ); ?>
	<div class="tribe-message-container"></div>
	<?php $this->template( 'tab' ); ?>
</div>
