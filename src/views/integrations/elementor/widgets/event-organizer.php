<?php
/**
 * View: Elementor Event Organizer widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-organizer.php
 *
 * @since 6.4.0
 *
 * @var bool            $show_header         Whether to show the organizer header.
 * @var bool            $link_name           Whether to link the organizer name.
 * @var bool            $show_name           Whether to show the organizer name.
 * @var bool            $show_phone          Whether to show the organizer phone.
 * @var bool            $show_email          Whether to show the organizer email.
 * @var bool            $show_website        Whether to show the organizer website.
 * @var bool            $show_phone_header   Whether to show the organizer phone header.
 * @var bool            $show_email_header   Whether to show the organizer email header.
 * @var bool            $show_website_header Whether to show the organizer website header.
 * @var bool            $multiple            If there are multiple organizers.
 * @var string          $header_tag          The widget header tag.
 * @var string          $organizer_name_tag  The widget header text.
 * @var string          $phone_header_tag    The phone header tag.
 * @var string          $email_header_tag    The email header tag.
 * @var string          $email_header_text   The text for the email header.
 * @var string          $phone_header_text   The text for the phone header.
 * @var string          $website_header_text The text for the website header.
 * @var string          $website_header_tag  The website header tag.
 * @var array           $organizers          The organizer data arrays.
 * @var int             $event_id            The event ID.
 * @var array           $settings            The widget settings.
 * @var Event_Organizer $widget              The widget instance.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Organizer;

// No organizers, no render.
if ( empty( $organizers ) ) {
	return;
}
?>
<div <?php tribe_classes( $widget->get_container_classes() ); ?>>
	<?php
	$this->template( 'views/integrations/elementor/widgets/event-organizer/header' );

	foreach ( $organizers as $organizer ) {
		$this->template(
			'views/integrations/elementor/widgets/event-organizer/names',
			[ 'organizer' => $organizer ]
		);
	}

	$this->template(
		'views/integrations/elementor/widgets/event-organizer/details',
		[ 'organizer' => $organizer ]
	);
	?>
</div>
