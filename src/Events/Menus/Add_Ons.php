<?php

/**
 * Admin Add ons menu/page for TEC plugins.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */

namespace TEC\Events\Menus;

use TEC\Common\Menus\Abstract_Menu;
use TEC\Common\Menus\Traits\Submenu;
use TEC\Common\Menus\Traits\With_Admin_Bar;
use Tribe__Events__Main;
use WP_Error;

/**
 * Class Add_Ons admin/menu.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */
class Add_Ons extends Abstract_Menu {
	use Submenu, With_Admin_Bar;

	/**
	 * {@inheritDoc}
	 */
	protected $capability = 'install_plugins';

	/**
	 * {@inheritDoc}
	 */
	public $menu_slug = 'tec-add-ons';

	/**
	 * {@inheritDoc}
	 */
	protected $position = 65;

	protected $common;

	/**
	 * {@inheritDoc}
	 */
	public function init() : void {
		$this->menu_title   = _x( 'Event Add-Ons', 'The title for the admin menu link', 'the-events-calendar');
		$this->page_title   = _x( 'Event Add-Ons', 'The title for the admin page', 'the-events-calendar');
		$this->parent_file  = 'tec-events';
		$this->parent_slug  = 'tec-events';

		$this->common = \Tribe__Main::instance();

		parent::init();
	}

	/**
	 * {@inheritDoc}
	 */
	public function render() : void {
		$main = \Tribe__Main::instance();
		$products = $this->get_all_products();
		$bundles = $this->get_bundles();
		$extensions = $this->get_extensions();
		$stellar_brands = $this->get_stellar_brands();

		include_once $this->common->plugin_path . 'src/admin-views/app-shop.php';
	}

	/**
	 * Gets all products from the API
	 *
	 * @return array|WP_Error
	 */
	private function get_all_products() : array|WP_Error {
		$all_products = tribe( 'plugins.api' )->get_products();

		$products = [
			'the-events-calendar' =>      (object) $all_products['the-events-calendar'],
			'events-calendar-pro' =>      (object) $all_products['events-calendar-pro'],
			'events-virtual' =>           (object) $all_products['events-virtual'],
			'event-aggregator' =>         (object) $all_products['event-aggregator'],
			'event-tickets' =>            (object) $all_products['event-tickets'],
			'event-tickets-plus' =>       (object) $all_products['event-tickets-plus'],
			'promoter' =>                 (object) $all_products['promoter'],
			'tribe-filterbar' =>          (object) $all_products['tribe-filterbar'],
			'events-community' =>         (object) $all_products['events-community'],
			'events-community-tickets' => (object) $all_products['events-community-tickets'],
			'tribe-eventbrite' =>         (object) $all_products['tribe-eventbrite'],
			'image-widget-plus' =>        (object) $all_products['image-widget-plus'],
		];

		return $products;
	}

	/**
	 * Gets product bundles
	 *
	 * @return array|WP_Error
	 */
	private function get_bundles() : array|WP_Error {
		$bundles = [
			(object) [
				'title' => __( 'Events Marketing Bundle', 'tribe-common' ),
				'logo' => 'images/logo/bundle-event-marketing.svg',
				'link' => 'https://evnt.is/1aj3',
				'discount' => __( 'Save over 20%', 'tribe-common' ),
				'description' => __( 'Ticket sales, attendee management, and email marketing for your events', 'tribe-common' ),
				'includes' => [
					'events-calendar-pro',
					'event-tickets-plus',
					'promoter',
				],
			],
			(object) [
				'title' => __( 'Event Importer Bundle', 'tribe-common' ),
				'logo' => 'images/logo/bundle-event-importer.svg',
				'link' => 'https://evnt.is/1aj2',
				'discount' => __( 'Save over 25%', 'tribe-common' ),
				'description' => __( 'Fill your calendar with events from across the web, including Google Calendar, Meetup, and more.', 'tribe-common' ),
				'includes' => [
					'events-calendar-pro',
					'tribe-filterbar',
					'event-aggregator'
				],
			],
			(object) [
				'title' => __( 'Virtual Events Marketing Bundle', 'tribe-common' ),
				'logo' => 'images/logo/bundle-virtual-events.svg',
				'link' => 'http://evnt.is/ve-bundle',
				'discount' => __( 'Save over 20%', 'tribe-common' ),
				'description' => __( 'Streamline your online events and increase revenue.', 'tribe-common' ),
				'includes' => [
					'events-calendar-pro',
					'event-tickets-plus',
					'events-virtual',
					'promoter',
				],
				'features' => [
					__( 'Sell tickets and earn revenue for online events', 'tribe-common' ),
					__( 'Zoom integration', 'tribe-common' ),
					__( 'Automated emails optimized for virtual events', 'tribe-common' ),
					__( 'Add recurring events', 'tribe-common' ),
				],
			],
			(object) [
				'title' => __( 'Community Manager Bundle', 'tribe-common' ),
				'logo' => 'images/logo/bundle-community-manager.svg',
				'link' => 'https://evnt.is/1aj4',
				'discount' => __( 'Save over 20%', 'tribe-common' ), /* code review: fix this */
				'description' => __( 'Handle event submissions with ticket sales and everything you need to build a robust community.', 'tribe-common' ),
				'includes' => [
					'event-tickets-plus',
					'events-community',
					'events-community-tickets',
					'tribe-filterbar',
				],
			],
			(object) [
				'title' => __( 'Ultimate Bundle', 'tribe-common' ),
				'logo' => 'images/logo/bundle-ultimate.svg',
				'link' => 'https://evnt.is/1aj5',
				'discount' => __( 'Save over 20%', 'tribe-common' ), /* code review: fix this */
				'description' => __( 'All of our premium events management plugins at a deep discount.', 'tribe-common' ),
				'includes' => [
					'events-calendar-pro',
					'event-tickets-plus',
					//'events-virtual', // not yet added to the bundle
					'events-community',
					'events-community-tickets',
					'tribe-filterbar',
					'event-aggregator',
					'tribe-eventbrite',
					//'promoter', // not yet added to the bundle
				],
			],

		];

		return $bundles;
	}

	/**
	 * Gets product extensions
	 *
	 * @return array|WP_Error
	 */
	private function get_extensions() : array|WP_Error {
		$extensions = [
			(object) [
				'title' => __( 'Website URL CTA', 'tribe-common' ),
				'link' => 'https://evnt.is/1aj6',
				'image' => 'images/shop/extension-web-url-cta.jpg',
				'description' => __( 'Create a strong call-to-action for attendees to "Join Webinar" instead of only sharing a website address.', 'tribe-common' ),
			],
			(object) [
				'title' => __( 'Link Directly to Webinar', 'tribe-common' ),
				'link' => 'https://evnt.is/1aj7',
				'image' => 'images/shop/extension-link-to-webinar.jpg',
				'description' => __( 'When users click on the event title, they’ll be taken right to the source of your event, offering a direct route to join.', 'tribe-common' ),
			],
			(object) [
				'title' => __( 'Events Happening Now', 'tribe-common' ),
				'link' => 'https://evnt.is/1aj8',
				'image' => 'images/shop/extension-events-happening-now.jpg',
				'description' => __( 'Use this shortcode to display events that are currently in progress, like webinars and livestreams.', 'tribe-common' ),
			],
			(object) [
				'title' => __( 'Custom Venue Links', 'tribe-common' ),
				'link' => 'https://evnt.is/1aj9',
				'image' => 'images/shop/extension-custom-venue-links.jpg',
				'description' => __( 'Turn the venue name for your event into a clickable URL — a great way to link directly to a venue’s website or a virtual meeting.', 'tribe-common' ),
			],
			(object) [
				'title' => __( 'Adjust Label', 'tribe-common' ),
				'link' => 'https://evnt.is/1aja',
				'image' => 'images/shop/extension-change-label.jpg',
				'description' => __( 'Change "Events" to "Webinars," or "Venues" to "Livestream," or "Organizers" to "Hosts." Tailor your calendar for virtual events and meetings.', 'tribe-common' ),
			],
			(object) [
				'title' => __( 'Reach Attendees', 'tribe-common' ),
				'link' => 'https://evnt.is/1ajc',
				'image' => 'images/shop/extension-advanced-options.jpg',
				'description' => __( 'From registration to attendance history, view every step of the event lifecycle with this HubSpot integration.', 'tribe-common' ),
			],
		];

		return $extensions;
	}

	/**
	 * Gets Stellar brands
	 *
	 * @return array|WP_Error
	 */
	private function get_stellar_brands() : array|WP_Error {
		$stellar_brands = [
			(object) [
				'image' => 'images/shop/stellar-learndash-cta.jpg',
				'logo' => 'images/shop/stellar-learndash-logo.png',
				'title' => __( 'The online course platform created by e-learning experts.', 'tribe-common' ),
				'link' => 'https://evnt.is/learndash',
				'linktext' => __( 'Add Courses', 'tribe-common' ),
				'description' => __( 'Trusted to power learning programs for major universities, startups, entrepreneurs, and bloggers worldwide.', 'tribe-common' ),
			],
			(object) [
				'image' => 'images/shop/stellar-ithemes-cta.jpg',
				'logo' => 'images/shop/stellar-ithemes-logo.png',
				'title' => __( 'Foundational favorites: iThemes Security and Developer Toolkit.', 'tribe-common' ),
				'link' => 'https://evnt.is/ithemes',
				'linktext' => __( 'Add Security', 'tribe-common' ),
				'description' => __( 'iThemes Security, the WordPress security plugin that’s easy to use. Built with performance in mind.', 'tribe-common' ),
			],
			(object) [
				'image' => 'images/shop/stellar-rcp-cta.jpg',
				'logo' => 'images/shop/stellar-rcp-logo.png',
				'title' => __( 'Built with developers in mind.', 'tribe-common' ),
				'link' => 'https://evnt.is/rcp',
				'linktext' => __( 'Add Content Restriction', 'tribe-common' ),
				'description' => __( 'Restrict Content Pro is flexible, easy to extend, and chock full of action hooks and filters, making it easy to modify and tweak to your specific needs.', 'tribe-common' ),
			],
			(object) [
				'image' => 'images/shop/stellar-kadence-cta.jpg',
				'logo' => 'images/shop/stellar-kadence-logo.png',
				'title' => __( 'Build better WordPress websites with Kadence.', 'tribe-common' ),
				'link' => 'https://evnt.is/kadencewp',
				'linktext' => __( 'Add Starter Templates', 'tribe-common' ),
				'description' => __( 'Kadence lets you unlock your creativity in the WordPress Block Editor with expertly designed blocks, a robust theme, and a massive library of starter templates.', 'tribe-common' ),
			],
			(object) [
				'image' => 'images/shop/stellar-iconic-cta.jpg',
				'logo' => 'images/shop/stellar-iconic-logo.png',
				'title' => __( 'Sales-boosting WooCommerce plugins.', 'tribe-common' ),
				'link' => 'https://evnt.is/iconic',
				'linktext' => __( 'Add Commerce Tools', 'tribe-common' ),
				'description' => __( 'Easy-to-use WooCommerce plugins work perfectly together, with any theme. Create a fast and profitable eCommerce store without any technical knowledge.
				', 'tribe-common' ),
			],
			(object) [
				'image' => 'images/shop/stellar-give-cta.jpg',
				'logo' => 'images/shop/stellar-give-logo.png',
				'title' => __( 'The best WordPress donation plugin.', 'tribe-common' ),
				'link' => 'https://evnt.is/givewp',
				'linktext' => __( 'Add Donations', 'tribe-common' ),
				'description' => __( 'GiveWP makes it easy to raise money online with donation forms, donor databases, and fundraising reporting.', 'tribe-common' ),
			],
		];

		return $stellar_brands;
	}

	/**
	 * Registers the plugin assets
	 */
	public function register_assets() : void {
		tribe_assets(
			$this->common,
			[
				[ 'tribe-app-shop-css', 'app-shop.css' ],
				[ 'tribe-app-shop-js', 'app-shop.js', [ 'jquery' ] ],
			],
			'admin_enqueue_scripts',
			[ 'conditionals' => [ $this, 'is_current_page' ] ]
		);
	}
}
