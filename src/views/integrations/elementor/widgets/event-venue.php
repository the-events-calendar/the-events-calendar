<?php
/**
 * View: Elementor Event Venue widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/event-venue.php
 *
 * @since 6.4.0
 *
 * Show toggles, default true.
 * @var bool        $link_name             Whether to link the venue name.
 * @var bool        $show_name             Whether to show the venue name.
 * @var bool        $show_widget_header    Whether to show the widget header.
 * @var bool        $show_address          Whether to show the venue address.
 * @var bool        $show_address_map_link Whether to show the venue address map link.
 * @var bool        $show_map              Whether to show the venue map.
 * @var bool        $show_phone            Whether to show the venue phone.
 * @var bool        $show_website          Whether to show the venue website.
 *
 * Show toggles, default false.
 * @var bool        $show_address_header   Whether to show the venue address header.
 * @var bool        $show_phone_header     Whether to show the venue phone header.
 * @var bool        $show_website_header   Whether to show the venue website header.
 *
 * HTML tags.
 * @var string      $header_tag            The HTML tag for the widget header.
 * @var string      $name_tag              The HTML tag for the venue name.
 * @var string      $address_header_tag    The HTML tag for the venue address header.
 * @var string      $phone_header_tag      The HTML tag for the venue phone header.
 * @var string      $website_header_tag    The HTML tag for the venue website header.
 *
 * Translated strings.
 * @var string      $header_text           The widget header text.
 * @var string      $address_header_text   The address header text.
 * @var string      $phone_header_text     The phone header text.
 * @var string      $website_header_text   The website header text.
 *
 * Misc.
 * @var int         $event_id              The event ID.
 * @var array       $settings              The widget settings.
 * @var array       $venues                The venue data arrays.
 * @var Event_Venue $widget                The widget instance.
 */

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Event_Venue;

// No venues, no render.
if ( empty( $venues ) ) {
	return;
}
?>
<div <?php tec_classes( $widget->get_element_classes() ); ?>>
	<?php
	$this->template( 'views/integrations/elementor/widgets/event-venue/header' );
	?>
	<?php foreach ( $venues as $venue ) : ?>
		<div <?php tec_classes( $widget->get_container_classes() ); ?>>
				<?php $this->template( 'views/integrations/elementor/widgets/event-venue/single-venue', [ 'venue' => $venue ] ); ?>
		</div>
	<?php endforeach; ?>
</div>
