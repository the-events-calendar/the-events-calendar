<?php
/**
 * View: Elementor Event Organizer widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events-pro/integrations/elementor/widgets/event-organizer.php
 *
 * @since TBD
 *
 * @var bool            $show_header         Whether to show the organizer heading.
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
 * @var string          $phone_header_tag    The phone header tag.
 * @var string          $email_header_tag    The email header tag.
 * @var string          $email_header_text   The text for the email header.
 * @var string          $phone_header_text   The text for the phone header.
 * @var string          $website_header_text The text for the website header.
 * @var string          $website_header_tag  The website header tag.
 * @var array           $organizer_ids       The organizer IDs.
 * @var int             $event_id            The event ID.
 * @var array           $settings            The widget settings.
 * @var Event_Organizer $widget              The widget instance.
 */

use TEC\Events_Pro\Integrations\Plugins\Elementor\Widgets\Event_Organizer;

// No organizers, no render.
if ( empty( $organizer_ids ) ) {
	return;
}
?>
<div <?php tribe_classes( $widget->get_container_classes() ); ?>>
	<?php
	$this->template(
		'views/integrations/elementor/widgets/event-organizer/header',
		[
			'show'       => $show_header,
			'header_tag' => $header_tag,
			'multiple'   => $multiple,
			'settings'   => $settings,
			'event_id'   => $event_id,
			'widget'     => $widget,
		]
	);

	foreach ( $organizer_ids as $organizer ) {
		$this->template(
			'views/integrations/elementor/widgets/event-organizer/names',
			[
				'link'      => $link_name,
				'show'      => $show_name,
				'organizer' => $organizer,
				'multiple'  => $multiple,
				'settings'  => $settings,
				'event_id'  => $event_id,
				'widget'    => $widget,
			]
		);
	}

	$this->template(
		'views/integrations/elementor/widgets/event-organizer/details',
		[
			'organizer'           => $organizer,
			'show_phone'          => $show_phone,
			'show_email'          => $show_email,
			'show_website'        => $show_website,
			'show_phone_header'   => $show_phone_header,
			'show_email_header'   => $show_email_header,
			'show_website_header' => $show_website_header,
			'phone_header_tag'    => $phone_header_tag,
			'email_header_tag'    => $email_header_tag,
			'website_header_tag'  => $website_header_tag,
			'multiple'            => $multiple,
			'settings'            => $settings,
			'event_id'            => $event_id,
			'widget'              => $widget,
		]
	);
	?>
</div>
