<?php
/**
 * View: Elementor Single Event Venue widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/venue/single-venue.php
 *
 * @since 6.4.0
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
 * @var array  $venues             The venue IDs.
 * @var Tribe\Events\Integrations\Elementor\Widgets\Event_Venue $widget The widget instance.
 */

if ( empty( $venue ) ) {
	return;
}

?>
<div <?php tribe_classes( $widget->get_widget_class() . '-details' ); ?>>
	<?php
	$this->template( 'views/integrations/elementor/widgets/event-venue/name' );

	$this->template( 'views/integrations/elementor/widgets/event-venue/address' );

	$this->template( 'views/integrations/elementor/widgets/event-venue/phone' );

	$this->template( 'views/integrations/elementor/widgets/event-venue/website' );
	?>
</div>
<?php
$this->template( 'views/integrations/elementor/widgets/event-venue/map' );
