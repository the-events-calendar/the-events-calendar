<?php
/**
 * View: Elementor Single Event Venue widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events-pro/integrations/elementor/widgets/venue/single-venue.php
 *
 * @since TBD
 *
 *
 * Show toggles, default true.
 * @var bool   $link_name             Whether to link the venue name.
 * @var bool   $show_name             Whether to show the venue name.
 * @var bool   $show_address          Whether to show the venue address.
 * @var bool   $show_address_map_link Whether to show the venue address map link.
 * @var bool   $show_map              Whether to show the venue map.
 * @var bool   $show_phone            Whether to show the venue phone.
 * @var bool   $show_website          Whether to show the venue website.
 *
 * Show toggles, default false.
 * @var bool   $show_address_header  Whether to show the venue address header.
 * @var bool   $show_phone_header    Whether to show the venue phone header.
 * @var bool   $show_website_header  Whether to show the venue website header.
 *
 * HTML tags.
 * @var string $address_header_tag      The HTML tag for the venue address header.
 * @var string $phone_header_tag        The HTML tag for the venue phone header.
 * @var string $website_header_tag      The HTML tag for the venue website header.
 *
 * Translated strings.
 * @var string $address_header_text   The address header text.
 * @var string $phone_header_text     The phone header text.
 * @var string $website_header_text   The website header text.
 *
 * Misc.
 * @var int    $event_id              The event ID.
 * @var array  $settings              The widget settings.
 * @var array  $venue_ids             The venue IDs.
 * @var Tribe\Events\Pro\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $venue_ids ) ) {
	return;
}

foreach ( $venue_ids as $venue_id ) : ?>
	<div <?php tribe_classes( $widget->get_widget_class() . '-details' ); ?>>
		<?php
		$this->template(
			'views/integrations/elementor/widgets/event-venue/name',
			[
				'show'     => $show_name,
				'link'     => $link_name,
				'venue_id' => $venue_id,
				'settings' => $settings,
				'event_id' => $event_id,
				'widget'   => $widget,
			]
		);

		$this->template(
			'views/integrations/elementor/widgets/event-venue/address',
			[
				'show'          => $show_address,
				'show_map_link' => $show_address_map_link,
				'show_header'   => $show_address_header,
				'header_tag'    => $address_header_tag,
				'header_text'   => $address_header_text,
				'venue_id'      => $venue_id,
				'settings'      => $settings,
				'event_id'      => $event_id,
				'widget'        => $widget,
			]
		);

		$this->template(
			'views/integrations/elementor/widgets/event-venue/phone',
			[
				'show'        => $show_phone,
				'show_header' => $show_phone_header,
				'header_tag'  => $phone_header_tag,
				'header_text' => $phone_header_text,
				'venue_id'    => $venue_id,
				'settings'    => $settings,
				'event_id'    => $event_id,
				'widget'      => $widget,
			]
		);

		$this->template(
			'views/integrations/elementor/widgets/event-venue/website',
			[
				'show'        => $show_website,
				'show_header' => $show_website_header,
				'header_tag'  => $website_header_tag,
				'header_text' => $website_header_text,
				'venue_id'    => $venue_id,
				'settings'    => $settings,
				'event_id'    => $event_id,
				'widget'      => $widget,
			]
		);
		?>
	</div>
	<?php
	$this->template(
		'views/integrations/elementor/widgets/event-venue/map',
		[
			'show'     => $show_map,
			'venue_id' => $venue_id,
			'settings' => $settings,
			'event_id' => $event_id,
			'widget'   => $widget,
		]
	);

endforeach;
