<?php

$link_one = sprintf(
	'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
	esc_url( 'https://theeventscalendar.com/knowledgebase/k/v1-deprecation-faqs/' ),
	esc_html_x( 'read the FAQs', 'Read more about deprecation of legacy views.', 'the-events-calendar' )
);
$link_two = sprintf(
	'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
	esc_url( 'https://theeventscalendar.com/support/' ),
	esc_html_x( 'contact support', 'Our support page for TEC', 'the-events-calendar' )
);


$links = sprintf(
	'<a href="%1$s" class="button">%2$s</a>',
	tribe_events_get_url(),
	esc_html__( 'View your calendar', 'the-events-calendar' )
);

?>
<div class="tec-update-notice">
    <h3 class="tec-update-notice__title">
		<?php esc_html_e( 'Your calendar’s design has changed', 'the-events-calendar' ); ?>
	</h3>
	<p>
		<?php _e( 'We’ve detected that your site was still using our legacy calendar design. As part of the update to The Events Calendar 6.0, <strong>your calendar was automatically upgraded to the new designs.</strong>', 'the-events-calendar' ) ?>
		<br><br>
		<?php echo sprintf( __( '<strong>Check out your calendar to see the improved designs live on your site.</strong> If you have a question or need help, %1$s or %2$s.', 'the-events-calendar' ), $link_one, $link_two ); ?>
	</p>

    <div class="tec-update-notice__actions">
		<?php echo $links; ?>
	</div>
</div>
