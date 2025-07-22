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

use TEC\Common\Admin\Help_Hub\Hub;
use TEC\Common\Admin\Help_Hub\Resource_Data\Help_Hub_Data_Interface;
use TEC\Common\Admin\Help_Hub\Section_Builder\Link_Section_Builder;
use TEC\Common\Admin\Help_Hub\Section_Builder\FAQ_Section_Builder;
use TEC\Common\Telemetry\Telemetry;
use Tribe__Main;
use Tribe__PUE__Checker;
use Tribe\Events\Admin\Settings;

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
	 * The ID of the help hub page.
	 *
	 * @since 6.13.2
	 * @var string
	 */
	const HELP_HUB_PAGE_ID = 'tribe_events_page_tec-events-help-hub';

	/**
	 * Holds the URLs for the necessary icons.
	 *
	 * @since 6.8.2
	 * @var array<string,string>
	 */
	protected array $icons = [];

	/**
	 * The body class array that styles the admin page.
	 *
	 * @since 6.8.2
	 * @var array<string>
	 */
	protected array $admin_page_body_classes = [ 'tribe_events_page_tec-events-settings' ];

	/**
	 * Whether the class has been initialized.
	 *
	 * @since 6.13.2
	 * @var bool
	 */
	protected bool $initialized = false;

	/**
	 * Constructor.
	 *
	 * Sets up the initialization hooks.
	 *
	 * @since 6.8.2
	 * @since 6.13.2 Moved logic into initialize() method.
	 */
	public function __construct() {
		add_action( 'load-' . self::HELP_HUB_PAGE_ID, [ $this, 'initialize' ] );
		add_action( 'tec_help_hub_before_iframe_render', [ $this, 'register_with_hub' ] );
	}

	/**
	 * Registers this data instance with the Help Hub.
	 *
	 * @since 6.13.2
	 *
	 * @param Hub $help_hub The current Help Hub instance to register with.
	 *
	 * @return void
	 */
	public function register_with_hub( Hub $help_hub ): void {
		$page = tec_get_request_var( 'page' );
		if ( self::HELP_HUB_PAGE_ID !== $page ) {
			return;
		}
		$this->initialize();
		$help_hub->set_data( $this );
	}

	/**
	 * Initializes the Help Hub Resource Data.
	 *
	 * @since 6.13.2
	 *
	 * @return void
	 */
	public function initialize(): void {

		if ( $this->initialized ) {
			return;
		}

		$origin = Tribe__Main::instance();

		$this->icons = [
			'tec_icon'     => tribe_resource_url( 'images/logo/the-events-calendar.svg', false, null, $origin ),
			'ea_icon'      => tribe_resource_url( 'images/logo/event-aggregator.svg', false, null, $origin ),
			'fbar_icon'    => tribe_resource_url( 'images/logo/filterbar.svg', false, null, $origin ),
			'article_icon' => tribe_resource_url( 'images/icons/file-text1.svg', false, null, $origin ),
			'stars_icon'   => tribe_resource_url( 'images/icons/stars.svg', false, null, $origin ),
			'chat_icon'    => tribe_resource_url( 'images/icons/chat-bubble.svg', false, null, $origin ),
		];

		$this->add_hooks();
		$this->initialized = true;
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
	 * @since 6.13.2 Refactored using the Link_Section_Builder and FAQ_Section_Builder classes.
	 *
	 * @return array The filtered resource sections array.
	 */
	public function create_resource_sections(): array {
		/** @var Link_Section_Builder $link_builder */
		$link_builder = tribe( Link_Section_Builder::class );

		/** @var FAQ_Section_Builder $faq_builder */
		$faq_builder = tribe( FAQ_Section_Builder::class );

		// Build all sections.
		$this->build_getting_started_section( $link_builder );
		$this->build_customizations_section( $link_builder );
		$this->build_common_issues_section( $link_builder );
		$this->build_faq_section( $faq_builder );

		// Get all built sections.
		return array_merge(
			$link_builder::get_all_sections(),
			$faq_builder::get_all_sections()
		);
	}

	/**
	 * Builds the Getting Started section.
	 *
	 * @since 6.13.2
	 *
	 * @param Link_Section_Builder $builder The section builder instance.
	 *
	 * @return void
	 */
	protected function build_getting_started_section( Link_Section_Builder $builder ): void {
		$builder::make(
			_x( 'Getting Started', 'Section title', 'the-events-calendar' ),
			'getting_started'
		)
			->set_description( _x( 'Easy to follow step-by-step instructions to make the most out of your calendar.', 'Section description', 'the-events-calendar' ) )
			->add_link(
				_x( 'The Events Calendar', 'The Events Calendar title', 'the-events-calendar' ),
				'https://evnt.is/1ap9',
				$this->get_icon_url( 'tec_icon' )
			)
			->add_link(
				_x( 'Event Aggregator', 'Event Aggregator title', 'the-events-calendar' ),
				'https://evnt.is/1apc',
				$this->get_icon_url( 'ea_icon' )
			)
			->add_link(
				_x( 'Filter Bar', 'Filter Bar title', 'the-events-calendar' ),
				'https://evnt.is/1apd',
				$this->get_icon_url( 'fbar_icon' )
			)
			->build();
	}

	/**
	 * Builds the Customizations section.
	 *
	 * @since 6.13.2
	 *
	 * @param Link_Section_Builder $builder The section builder instance.
	 *
	 * @return void
	 */
	protected function build_customizations_section( Link_Section_Builder $builder ): void {
		$builder::make(
			_x( 'Customizations', 'Section title', 'the-events-calendar' ),
			'customizations'
		)
			->set_description( _x( 'Tips and tricks on making your calendar just the way you want it.', 'Section description', 'the-events-calendar' ) )
			->add_link(
				_x( 'Getting started with customization', 'Customization article', 'the-events-calendar' ),
				'https://evnt.is/1apf',
				$this->get_icon_url( 'article_icon' )
			)
			->add_link(
				_x( 'Highlighting events', 'Highlighting events article', 'the-events-calendar' ),
				'https://evnt.is/1apg',
				$this->get_icon_url( 'article_icon' )
			)
			->add_link(
				_x( 'Customizing template files', 'Customizing templates article', 'the-events-calendar' ),
				'https://evnt.is/1aph',
				$this->get_icon_url( 'article_icon' )
			)
			->add_link(
				_x( 'Customizing CSS', 'Customizing CSS article', 'the-events-calendar' ),
				'https://evnt.is/1api',
				$this->get_icon_url( 'article_icon' )
			)
			->build();
	}

	/**
	 * Builds the Common Issues section.
	 *
	 * @since 6.13.2
	 *
	 * @param Link_Section_Builder $builder The section builder instance.
	 *
	 * @return void
	 */
	protected function build_common_issues_section( Link_Section_Builder $builder ): void {
		$builder::make(
			_x( 'Common Issues', 'Section title', 'the-events-calendar' ),
			'common_issues'
		)
			->set_description(
				sprintf(
				/* translators: %s is the link to the AI Chatbot */
					_x( 'Having trouble? Find solutions to common issues or ask our %s.', 'Common issues section description', 'the-events-calendar' ),
					'<a href="javascript:void(0)" data-tab-target="tec-help-tab">' . _x( 'AI Chatbot', 'AI Chatbot link text', 'the-events-calendar' ) . '</a>'
				)
			)
			->add_link(
				_x( 'Known issues', 'Known issues article', 'the-events-calendar' ),
				'https://evnt.is/1apj',
				$this->get_icon_url( 'article_icon' )
			)
			->add_link(
				_x( 'Release notes', 'Release notes article', 'the-events-calendar' ),
				'https://evnt.is/1apk',
				$this->get_icon_url( 'article_icon' )
			)
			->add_link(
				_x( 'Integrations', 'Integrations article', 'the-events-calendar' ),
				'https://evnt.is/1apl',
				$this->get_icon_url( 'article_icon' )
			)
			->add_link(
				_x( 'Shortcodes', 'Shortcodes article', 'the-events-calendar' ),
				'https://evnt.is/1apm',
				$this->get_icon_url( 'article_icon' )
			)
			->build();
	}

	/**
	 * Builds the FAQ section.
	 *
	 * @since 6.13.2
	 *
	 * @param FAQ_Section_Builder $builder The section builder instance.
	 *
	 * @return void
	 */
	protected function build_faq_section( FAQ_Section_Builder $builder ): void {
		$builder::make( 'FAQ', 'faq' )
			->set_description( _x( 'Frequently Asked Questions', 'FAQ section description', 'the-events-calendar' ) )
			->add_faq(
				_x( 'Can I have more than one calendar?', 'FAQ more than one calendar question', 'the-events-calendar' ),
				_x( 'No, but you can use event categories or tags to display certain events.', 'FAQ more than one calendar answer', 'the-events-calendar' ),
				_x( 'Learn More', 'Link to more than one calendar article', 'the-events-calendar' ),
				'https://evnt.is/1arh'
			)
			->add_faq(
				_x( 'What do I get with Events Calendar Pro?', 'FAQ what is in Calendar Pro question', 'the-events-calendar' ),
				_x( 'Events Calendar Pro enhances The Events Calendar with additional views, powerful shortcodes, and a host of premium features.', 'FAQ what is in Calendar Pro answer', 'the-events-calendar' ),
				_x( 'Learn More', 'Link to what is in Calendar Pro article', 'the-events-calendar' ),
				'https://evnt.is/1arj'
			)
			->add_faq(
				_x( 'How do I sell event tickets?', 'FAQ how to sell event tickets question', 'the-events-calendar' ),
				_x( 'Get started with tickets and RSVPs using our free Event Tickets plugin.', 'FAQ how to sell event tickets answer', 'the-events-calendar' ),
				_x( 'Learn More', 'Link to what is in Event Tickets article', 'the-events-calendar' ),
				'https://evnt.is/1ark'
			)
			->add_faq(
				_x( 'Where can I find a list of available shortcodes?', 'FAQ where are the shortcodes question', 'the-events-calendar' ),
				_x( 'Our plugins offer a variety of shortcodes, allowing you to easily embed the calendar, display an event countdown clock, show attendee details, and much more.', 'FAQ where are the shortcodes answer', 'the-events-calendar' ),
				_x( 'Learn More', 'Link to the shortcodes article', 'the-events-calendar' ),
				'https://evnt.is/1arl'
			)
			->build();
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

	/**
	 * Determines if the current admin page is the Help Hub page.
	 *
	 * Checks the 'page' request variable against the Help Hub settings slug to confirm
	 * if the user is currently viewing the Help Hub admin page.
	 *
	 * @since 6.13.2
	 *
	 * @return bool True if the current page is the Help Hub, false otherwise.
	 */
	public function is_help_hub_page(): bool {
		$page = tec_get_request_var( 'page' );

		return $this->get_help_hub_slug() === $page;
	}

	/**
	 * Get the Help Hub id.
	 *
	 * @return string
	 */
	public function get_help_hub_id(): string {
		return self::HELP_HUB_PAGE_ID;
	}

	/**
	 * Retrieve the Help Hub slug.
	 *
	 * @return string The slug for the Help Hub.
	 */
	public function get_help_hub_slug(): string {
		return Settings::$help_hub_slug;
	}
}
