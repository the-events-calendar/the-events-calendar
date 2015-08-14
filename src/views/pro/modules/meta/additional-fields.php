<?php
/**
 * Single Event Meta (Additional Fields) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/pro/modules/meta/additional-fields.php
 *
 * @package TribeEventsCalendarPro
 */

if ( ! isset( $fields ) || empty( $fields ) || ! is_array( $fields ) ) {
	return;
}

?>

<div class="tribe-events-meta-group tribe-events-meta-group-other">
	<h3 class="tribe-events-single-section-title"> <?php esc_html_e( 'Other', 'tribe-events-calendar-pro' ) ?> </h3>
	<dl>
		<?php foreach ( $fields as $name => $value ): ?>
			<dt> <?php echo esc_html( $name );  ?> </dt>
			<dd class="tribe-meta-value"> <?php echo esc_html( $value ); ?> </dd>
		<?php endforeach ?>
	</dl>
</div>
