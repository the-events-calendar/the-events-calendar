<?php
/**
 * TEC Hub Resource Data Class
 *
 * This file defines the TEC_Hub_Resource_Data class, which implements
 * the Help_Hub_Data_Interface and provides The Events Calendar-specific
 * resources, FAQs, and settings for the Help Hub functionality.
 *
 * @since   6.8.2
 * @package TEC\Events\Admin\Help_Hub
 */

namespace TEC\Events\Admin\Help_Hub;

use TEC\Common\Admin\Help_Hub\Resource_Data\Help_Hub_Data_Interface;
use TEC\Common\Telemetry\Telemetry;
use Tribe__Main;
use Tribe__PUE__Checker;

/**
 * Class TEC_Hub_Resource_Data
 *
 * Implements the Help_Hub_Data_Interface, offering resources specific
 * to The Events Calendar, including FAQs, common issues, and customization guides.
 *
 * @since   6.8.2
 * @package TEC\Events\Admin\Help_Hub
 */
class TEC_Hub_Resource_Data implements Help_Hub_Data_Interface {

	/**
	 * Holds the URLs for the necessary icons.
	 *
	 * @since 6.8.2
	 * @var array
	 */
	protected array $icons = [];

	/**
	 * The body class array that styles the admin page.
	 *
	 * @var array
	 */
	protected array $admin_page_body_classes = [ 'tribe_events_page_tec-events-settings' ];

	/**
	 * Constructor.
	 *
	 * Initializes the icons array with URLs.
	 *
	 * @since 6.8.2
	 */
	public function __construct() {
		$origin ??= Tribe__Main::instance();

		$this->icons = [
			'tec_icon'     => tribe_resource_url( 'images/logo/the-events-calendar.svg', false, null, $origin ),
			'ea_icon'      => tribe_resource_url( 'images/logo/event-aggregator.svg', false, null, $origin ),
			'fbar_icon'    => tribe_resource_url( 'images/logo/filterbar.svg', false, null, $origin ),
			'article_icon' => tribe_resource_url( 'images/icons/file-text1.svg', false, null, $origin ),
			'stars_icon'   => tribe_resource_url( 'images/icons/stars.svg', false, null, $origin ),
			'chat_icon'    => tribe_resource_url( 'images/icons/chat-bubble.svg', false, null, $origin ),
		];

		$this->add_hooks();
	}

	/**
	 * Registers hooks for the Help Hub Resource Data class.
	 *
	 * This method registers filters and actions required for the Help Hub,
	 * such as adding custom body classes to the Help Hub page.
	 *
	 * @since 6.8.2
	 *
	 * @return void
	 */
	public function add_hooks(): void {
		add_filter( 'tec_help_hub_body_classes', [ $this, 'add_admin_body_classes' ] );
	}

	/**
	 * Adds custom body classes for the Help Hub page.
	 *
	 * This method allows the addition of `$admin_page_body_classes` to
	 * the list of body classes for the Help Hub page.
	 *
	 * @since 6.8.2
	 *
	 * @param array $classes The current array of body classes.
	 *
	 * @return array Modified array of body classes.
	 */
	public function add_admin_body_classes( array $classes ): array {
		return array_merge( $classes, $this->admin_page_body_classes );
	}

	/**
	 * Creates an array of resource sections with relevant content for each section.
	 *
	 * Each section can be filtered independently or as a complete set.
	 *
	 * @since 6.8.2
	 *
	 * @return array The filtered resource sections array.
	 */
	public function create_resource_sections(): array {
		// Initial data structure for resource sections.
		return [
			'getting_started' => [
				[
					'icon'  => $this->get_icon_url( 'tec_icon' ),
					'title' => _x( 'The Events Calendar', 'The Events Calendar title', 'the-events-calendar' ),
					'link'  => 'https://evnt.is/1ap9',
				],
				[
					'icon'  => $this->get_icon_url( 'ea_icon' ),
					'title' => _x( 'Event Aggregator', 'Event Aggregator title', 'the-events-calendar' ),
					'link'  => 'https://evnt.is/1apc',
				],
				[
					'icon'  => $this->get_icon_url( 'fbar_icon' ),
					'title' => _x( 'Filter Bar', 'Filter Bar title', 'the-events-calendar' ),
					'link'  => 'https://evnt.is/1apd',
				],
			],
			'customizations'  => [
				[
					'title' => _x( 'Getting started with customization', 'Customization article', 'the-events-calendar' ),
					'link'  => 'https://evnt.is/1apf',
					'icon'  => $this->get_icon_url( 'article_icon' ),
				],
				[
					'title' => _x( 'Highlighting events', 'Highlighting events article', 'the-events-calendar' ),
					'link'  => 'https://evnt.is/1apg',
					'icon'  => $this->get_icon_url( 'article_icon' ),
				],
				[
					'title' => _x( 'Customizing template files', 'Customizing templates article', 'the-events-calendar' ),
					'link'  => 'https://evnt.is/1aph',
					'icon'  => $this->get_icon_url( 'article_icon' ),
				],
				[
					'title' => _x( 'Customizing CSS', 'Customizing CSS article', 'the-events-calendar' ),
					'link'  => 'https://evnt.is/1api',
					'icon'  => $this->get_icon_url( 'article_icon' ),
				],
			],
			'common_issues'   => [
				[
					'title' => _x( 'Known issues', 'Known issues article', 'the-events-calendar' ),
					'link'  => 'https://evnt.is/1apj',
					'icon'  => $this->get_icon_url( 'article_icon' ),
				],
				[
					'title' => _x( 'Release notes', 'Release notes article', 'the-events-calendar' ),
					'link'  => 'https://evnt.is/1apk',
					'icon'  => $this->get_icon_url( 'article_icon' ),
				],
				[
					'title' => _x( 'Integrations', 'Integrations article', 'the-events-calendar' ),
					'link'  => 'https://evnt.is/1apl',
					'icon'  => $this->get_icon_url( 'article_icon' ),
				],
				[
					'title' => _x( 'Shortcodes', 'Shortcodes article', 'the-events-calendar' ),
					'link'  => 'https://evnt.is/1apm',
					'icon'  => $this->get_icon_url( 'article_icon' ),
				],
			],
			'faqs'            => [
				[
					'question'  => _x( 'Can I have more than one calendar?', 'FAQ more than one calendar question', 'the-events-calendar' ),
					'answer'    => _x( 'No, but you can use event categories or tags to display certain events.', 'FAQ more than one calendar answer', 'the-events-calendar' ),
					'link_text' => _x( 'Learn More', 'Link to more than one calendar article', 'the-events-calendar' ),
					'link_url'  => 'https://evnt.is/1arh',
				],
				[
					'question'  => _x( 'What do I get with Events Calendar Pro?', 'FAQ what is in Calendar Pro question', 'the-events-calendar' ),
					'answer'    => _x( 'Events Calendar Pro enhances The Events Calendar with additional views, powerful shortcodes, and a host of premium features.', 'FAQ what is in Calendar Pro answer', 'the-events-calendar' ),
					'link_text' => _x( 'Learn More', 'Link to what is in Calendar Pro article', 'the-events-calendar' ),
					'link_url'  => 'https://evnt.is/1arj',
				],
				[
					'question'  => _x( 'How do I sell event tickets?', 'FAQ how to sell event tickets question', 'the-events-calendar' ),
					'answer'    => _x( 'Get started with tickets and RSVPs using our free Event Tickets plugin.', 'FAQ how to sell event tickets answer', 'the-events-calendar' ),
					'link_text' => _x( 'Learn More', 'Link to what is in Event Tickets article', 'the-events-calendar' ),
					'link_url'  => 'https://evnt.is/1ark',
				],
				[
					'question'  => _x( 'Where can I find a list of available shortcodes?', 'FAQ where are the shortcodes question', 'the-events-calendar' ),
					'answer'    => _x( 'Our plugins offer a variety of shortcodes, allowing you to easily embed the calendar, display an event countdown clock, show attendee details, and much more.', 'FAQ where are the shortcodes answer', 'the-events-calendar' ),
					'link_text' => _x( 'Learn More', 'Link to the shortcodes article', 'the-events-calendar' ),
					'link_url'  => 'https://evnt.is/1arl',
				],
			],
		];
	}

	/**
	 * Retrieves the URL for a specified icon.
	 *
	 * @since 6.8.2
	 *
	 * @param string $icon_name The name of the icon to retrieve.
	 *
	 * @return string The URL of the specified icon, or an empty string if the icon does not exist.
	 */
	public function get_icon_url( string $icon_name ): string {
		return $this->icons[ $icon_name ] ?? '';
	}

	/**
	 * Get the license validity and telemetry opt-in status.
	 *
	 * @since 6.8.2
	 *
	 * @return array Contains 'has_valid_license' and 'is_opted_in' status.
	 */
	public function get_license_and_opt_in_status(): array {
		$has_valid_license = Tribe__PUE__Checker::is_any_license_valid();
		$common_telemetry  = tribe( Telemetry::class );
		$is_opted_in       = $common_telemetry->calculate_optin_status();

		return [
			'has_valid_license' => $has_valid_license,
			'is_opted_in'       => $is_opted_in,
		];
	}
}
