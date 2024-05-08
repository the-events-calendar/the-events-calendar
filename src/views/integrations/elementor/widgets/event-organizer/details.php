<?php
/**
 * View: Elementor Event Organizer widget header.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer/details.php
 *
 * @since 6.4.0
 *
 * @var bool   $multiple If there are multiple organizers.
 * @var string $phone_header_tag The phone header tag.
 * @var string $email_header_tag The email header tag.
 * @var string $website_header_tag The website header tag.
 * @var bool   $show_phone Whether to show the organizer phone.
 * @var bool   $show_email Whether to show the organizer email.
 * @var bool   $show_website Whether to show the organizer website.
 * @var string $show_phone_header Whether to show the organizer phone header.
 * @var string $show_email_header Whether to show the organizer email header.
 * @var string $show_website_header Whether to show the organizer website header.
 * @var string $phone_header_tag The phone header tag.
 * @var string $email_header_tag The email header tag.
 * @var string $website_header_tag The website header tag.
 * @var string $email_header_text The text for the email header.
 * @var string $phone_header_text The text for the phone header.
 * @var string $website_header_text The text for the website header.
 * @var array  $organizer_ids The organizer IDs.
 * @var int    $event_id The event ID.
 * @var array  $settings The widget settings.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Organizer $widget The widget instance.
 */

// Only show organizer details if there's just one.
if ( $multiple ) {
	return;
}
?>
<div <?php tribe_classes( $widget->get_widget_class() . '-details' ); ?>>
	<?php
	$this->template( 'integrations/elementor/widgets/event-organizer/details/phone' );

	$this->template( 'views/integrations/elementor/widgets/event-organizer/details/website' );

	$this->template( 'views/integrations/elementor/widgets/event-organizer/details/email' );
	?>
</div>
