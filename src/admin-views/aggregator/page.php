<?php
$datepicker_format = \Tribe__Date_Utils::get_datepicker_format_index();

$state_class = 'tribe-aggregator-inactive';
if ( tribe( 'events-aggregator.main' )->is_service_active() ) {
	$state_class = 'tribe-aggregator-active';
}
/**
 * Trigger the conditional content header notice.
 *
 * @since 6.14.0
 */
do_action( 'tec_conditional_content_header_notice' );
?>
<div class="tribe-ea wrap <?php echo esc_attr( $state_class ); ?>" data-datepicker_format="<?php echo esc_attr( $datepicker_format ); ?>">
	<?php $this->template( 'header' ); ?>
	<div class="tribe-message-container"></div>
	<?php $this->template( 'tab' ); ?>
</div>
