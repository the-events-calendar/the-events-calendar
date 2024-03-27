<?php
/**
 * Event Venue Elementor Widget.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Event_Venue
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Venue extends Abstract_Widget {
	use Traits\With_Shared_Controls;

	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_venue';

	/**
	 * Whether the widget has styles to register/enqueue.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected static bool $has_styles = true;

	/**
	 * Create the widget title.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Event Venue', 'the-events-calendar' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since TBD
	 *
	 * @return array The template args.
	 */
	protected function template_args(): array {
		$event_id  = $this->get_event_id();
		$venue_ids = tec_get_venue_ids( $event_id );
		$settings  = $this->get_settings_for_display();

		if ( isset( $settings['venue_website_link_target'] ) ) {
			$this->set_template_filter(
				'tribe_get_event_venue_website_link_target',
				function () use ( $settings ) {
					return $settings['venue_website_link_target'];
				}
			);
		}

		return [
			// Show toggles.
			// Boolean conversion of yes/no strings. Default true.
			'link_name'             => tribe_is_truthy( $settings['link_venue_name'] ?? true ),
			'show_name'             => tribe_is_truthy( $settings['show_venue_name'] ?? true ),
			'show_widget_header'    => tribe_is_truthy( $settings['show_venue_header'] ?? true ),
			'show_address'          => tribe_is_truthy( $settings['show_venue_address'] ?? true ),
			'show_address_map_link' => tribe_is_truthy( $settings['show_venue_address_map_link'] ?? true ),
			'show_map'              => tribe_is_truthy( $settings['show_venue_map'] ?? true ),
			'show_phone'            => tribe_is_truthy( $settings['show_venue_phone'] ?? true ),
			'show_website'          => tribe_is_truthy( $settings['show_venue_website'] ?? true ),
			// Boolean conversion of yes/no strings. Default false.
			'show_address_header'   => tribe_is_truthy( $settings['show_venue_address_header'] ?? false ),
			'show_phone_header'     => tribe_is_truthy( $settings['show_venue_phone_header'] ?? false ),
			'link_venue_phone'      => tribe_is_truthy( $settings['link_venue_phone'] ?? false ),
			'show_website_header'   => tribe_is_truthy( $settings['show_venue_website_header'] ?? false ),
			// HTML tags.
			'header_tag'            => $settings['venue_header_tag'] ?? 'h2',
			'name_tag'              => $settings['venue_name_html_tag'] ?? 'h3',
			'address_header_tag'    => $settings['venue_address_header_tag'] ?? 'h3',
			'phone_header_tag'      => $settings['venue_phone_header_tag'] ?? 'h3',
			'website_header_tag'    => $settings['venue_website_header_tag'] ?? 'h3',
			// Translated strings.
			'header_text'           => $this->get_header_text(),
			'address_header_text'   => $this->get_address_header_text(),
			'phone_header_text'     => $this->get_phone_header_text(),
			'website_header_text'   => $this->get_website_header_text(),
			// Misc.
			'event_id'              => $event_id,
			'settings'              => $settings,
			'venue_ids'             => $venue_ids,
		];
	}

	/**
	 * Modify the target for the event website link.
	 *
	 * @since TBD
	 *
	 * @param string          $link_target The target attribute string. Defaults to "_self".
	 * @param string          $unused_url  The link URL.
	 * @param null|object|int $post_id     The event the url is attached to.
	 *
	 * @return string The modified target attribute string.
	 */
	public function modify_link_target( $link_target, $unused_url, $post_id ): string {
		$event_id = $this->get_event_id();
		// Not the same event, bail.
		if ( $event_id !== $post_id ) {
			return $link_target;
		}

		$settings        = $this->get_settings_for_display();
		$target_override = $settings['venue_website_link_target'];

		if ( ! $target_override ) {
			return $link_target;
		}

		return $target_override;
	}

	/**
	 * Checks whether the event being previewed has multiple venues assigned.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the event has multiple venues.
	 */
	protected function has_multiple_venues() {
		$event_id  = $this->get_event_id();
		$venue_ids = tec_get_venue_ids( $event_id );

		return count( $venue_ids ) > 1;
	}

	/**
	 * Get the main header text for the widget.
	 *
	 * @since TBD
	 *
	 * @return string The header text.
	 */
	protected function get_header_text(): string {
		$event_id    = $this->get_event_id();
		$venue_ids   = tec_get_venue_ids( $event_id );
		$header_text = _nx(
			'Venue',
			'Venues',
			count( $venue_ids ),
			'The main header string for the Elementor event venue widget.',
			'the-events-calendar'
		);

		/**
		 * Filters the header text for the event venue widget.
		 *
		 * @since TBD
		 *
		 * @param string $header_text The header text.
		 * @param Event_Venue $this The event venue widget instance.
		 *
		 * @return string The filtered header text.
		 */
		return apply_filters( 'tec_events_pro_elementor_event_venue_widget_header_text', $header_text, $this );
	}

	/**
	 * Get the website header text for the widget.
	 *
	 * @since TBD
	 *
	 * @return string The website header text.
	 */
	protected function get_website_header_text(): string {
		$header_text = _x(
			'Website',
			'The header string for the Elementor event venue widget website section.',
			'the-events-calendar'
		);

		/**
		 * Filters the website header text for the event venue widget.
		 *
		 * @since TBD
		 *
		 * @param string $header_text The header text.
		 * @param Event_Venue $this The event venue widget instance.
		 *
		 * @return string The filtered header text.
		 */
		return apply_filters( 'tec_events_pro_elementor_event_venue_widget_website_header_text', $header_text, $this );
	}

	/**
	 * Get the phone header text for the widget.
	 *
	 * @since TBD
	 *
	 * @return string The phone header text.
	 */
	protected function get_phone_header_text(): string {
		$header_text = _x(
			'Phone',
			'The header string for the Elementor event venue widget phone section.',
			'the-events-calendar'
		);

		/**
		 * Filters the phone header text for the event venue widget.
		 *
		 * @since TBD
		 *
		 * @param string $header_text The header text.
		 * @param Event_Venue $this The event venue widget instance.
		 *
		 * @return string The filtered header text.
		 */
		return apply_filters( 'tec_events_pro_elementor_event_venue_widget_phone_header_text', $header_text, $this );
	}

	/**
	 * Get the address header text for the widget.
	 *
	 * @since TBD
	 *
	 * @return string The address header text.
	 */
	protected function get_address_header_text(): string {
		$header_text = _x(
			'Address',
			'The header string for the Elementor event venue widget address section.',
			'the-events-calendar'
		);

		/**
		 * Filters the address header text for the event venue widget.
		 *
		 * @since TBD
		 *
		 * @param string $header_text The header text.
		 * @param Event_Venue $this The event venue widget instance.
		 *
		 * @return string The filtered header text.
		 */
		return apply_filters( 'tec_events_pro_elementor_event_venue_widget_address_header_text', $header_text, $this );
	}

	/**
	 * Get the classes for the widget container.
	 *
	 * @since TBD
	 *
	 * @return array The container classes.
	 */
	public function get_container_classes() {
		$classes = [
			$this->get_widget_class() . '-container',
		];

		if ( $this->has_multiple_venues() ) {
			$classes[] = $this->get_widget_class() . '-multiple';
		}

		return $classes;
	}

	/**
	 * Get the class for the widget header.
	 *
	 * @since TBD
	 *
	 * @return array The header class.
	 */
	public function get_header_class() {
		$class = $this->get_widget_class() . '-header';

		/**
		 * Filters the classes for the event venue widget header.
		 *
		 * @since TBD
		 *
		 * @param array $classes The widget header classes.
		 * @param Event_Venue $this The event venue widget instance.
		 */
		return apply_filters( 'tec_events_pro_elementor_event_venue_header_class', $class, $this );
	}

	/**
	 * Get the base class for the event venue name section.
	 *
	 * @since TBD
	 *
	 * @return array The name header classes.
	 */
	public function get_name_base_class() {
		$class = $this->get_widget_class() . '-name';

		/**
		 * Filters the base class for the event venue name section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The name base class.
		 * @param Event_Venue $this The event venue widget instance.
		 */
		return apply_filters( 'tec_events_pro_elementor_event_venue_name_class', $class, $this );
	}

	/**
	 * Get the base class for the event venue address section.
	 *
	 * @since TBD
	 *
	 * @return array The address header classes.
	 */
	public function get_address_base_class() {
		$class = $this->get_widget_class() . '-address';

		/**
		 * Filters the base class for the event venue address section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The address base class.
		 * @param Event_Venue $this The event venue widget instance.
		 */
		return apply_filters( 'tec_events_pro_elementor_event_venue_address_class', $class, $this );
	}

	/**
	 * Get the base class for the event venue phone section.
	 *
	 * @since TBD
	 *
	 * @return array The phone header classes.
	 */
	public function get_phone_base_class() {
		$class = $this->get_widget_class() . '-phone';

		/**
		 * Filters the base class for the event venue phone section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The phone base class.
		 * @param Event_Venue $this The event venue widget instance.
		 */
		return apply_filters( 'tec_events_pro_elementor_event_venue_phone_class', $class, $this );
	}

	/**
	 * Get the base class for the event venue website section.
	 *
	 * @since TBD
	 *
	 * @return array The website header classes.
	 */
	public function get_website_base_class() {
		$class = $this->get_widget_class() . '-website';

		/**
		 * Filters the base class for the event venue website section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The website base class.
		 * @param Event_Venue $this The event venue widget instance.
		 */
		return apply_filters( 'tec_events_pro_elementor_event_venue_website_class', $class, $this );
	}

	/**
	 * Get the base class for the event venue map section.
	 *
	 * @since TBD
	 *
	 * @return array The map header classes.
	 */
	public function get_map_base_class() {
		$class = $this->get_widget_class() . '-map';

		/**
		 * Filters the base class for the event venue map section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The map base class.
		 * @param Event_Venue $this The event venue widget instance.
		 */
		return apply_filters( 'tec_events_pro_elementor_event_venue_map_class', $class, $this );
	}

	/**
	 * Register controls for the widget.
	 *
	 * @since TBD
	 */
	protected function register_controls() {
		$this->content_panel();
		$this->style_panel();
	}

	/**
	 * Add content controls for the widget.
	 *
	 * @since TBD
	 */
	protected function content_panel() {
		$this->content_options();

		$this->venue_name_content_options();

		$this->venue_address_content_options();

		$this->venue_phone_content_options();

		$this->venue_website_content_options();

		$this->venue_map_content_options();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since TBD
	 */
	protected function style_panel() {
		$this->style_venue_header();

		$this->style_venue_name();

		$this->style_venue_address();

		$this->style_venue_phone();

		$this->style_venue_website();

		$this->style_venue_map();
	}

	/**
	 * Add controls for text content of the event venue.
	 *
	 * @since TBD
	 */
	protected function content_options() {
		$this->start_controls_section(
			'section_title',
			[ 'label' => esc_html__( 'Event Venue', 'the-events-calendar' ) ]
		);

		// Show Venue Header control.
		$this->add_shared_control(
			'show',
			[
				'id'      => 'show_venue_header',
				'label'   => esc_html__( 'Show Widget Header', 'the-events-calendar' ),
				'default' => 'no',
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'venue_header_tag',
				'default'   => 'h2',
				'condition' => [
					'show_venue_header' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event venue name.
	 *
	 * @since TBD
	 */
	protected function venue_name_content_options() {
		$this->start_controls_section(
			'venue_name_content_options',
			[ 'label' => esc_html__( 'Name', 'the-events-calendar' ) ]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_venue_name',
				'label' => esc_html__( 'Show Name', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'venue_name_html_tag',
				'label'     => esc_html__( 'HTML Tag', 'the-events-calendar' ),
				'condition' => [
					'show_venue_name' => 'yes',
				],
			]
		);

		// Show Venue Header control.
		$this->add_control(
			'link_venue_name',
			[
				'label'     => esc_html__( 'Link to Venue Profile', 'the-events-calendar' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'the-events-calendar' ),
				'label_off' => esc_html__( 'No', 'the-events-calendar' ),
				'default'   => 'yes',
				'condition' => [
					'show_venue_name' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event venue phone.
	 *
	 * @since TBD
	 */
	protected function venue_phone_content_options() {
		$this->start_controls_section(
			'venue_phone_content_options',
			[ 'label' => esc_html__( 'Phone', 'the-events-calendar' ) ]
		);
		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_venue_phone',
				'label' => esc_html__( 'Show Phone', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'        => 'show_venue_phone_header',
				'label'     => esc_html__( 'Show Header', 'the-events-calendar' ),
				'default'   => 'no',
				'condition' => [
					'show_venue_phone' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'venue_phone_header_tag',
				'label'     => esc_html__( 'HTML Tag', 'the-events-calendar' ),
				'condition' => [
					'show_venue_phone_header' => 'yes',
				],
			]
		);

		// Link Organizer Name control.
		$this->add_control(
			'link_venue_phone',
			[
				'label'       => esc_html__( 'Link venue phone.', 'the-events-calendar' ),
				'description' => esc_html__( 'Make venue phone number a callable link.', 'the-events-calendar' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Yes', 'the-events-calendar' ),
				'label_off'   => esc_html__( 'No', 'the-events-calendar' ),
				'default'     => 'yes',
				'condition'   => [ 'show_venue_phone' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event venue address.
	 *
	 * @since TBD
	 */
	protected function venue_address_content_options() {
		$this->start_controls_section(
			'venue_address_content_options',
			[ 'label' => esc_html__( 'Address', 'the-events-calendar' ) ]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_venue_address',
				'label' => esc_html__( 'Show Address', 'the-events-calendar' ),
			]
		);

		// Show Venue Header control.
		$this->add_shared_control(
			'show',
			[
				'id'        => 'show_venue_address_header',
				'label'     => esc_html__( 'Show Header', 'the-events-calendar' ),
				'default'   => 'no',
				'condition' => [
					'show_venue_address' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'venue_address_header_tag',
				'label'     => esc_html__( 'HTML Tag', 'the-events-calendar' ),
				'condition' => [
					'show_venue_address_header' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_venue_address_map_link',
				'label' => esc_html__( 'Show Map Link', 'the-events-calendar' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event venue website.
	 *
	 * @since TBD
	 */
	protected function venue_website_content_options() {
		$this->start_controls_section(
			'venue_website_content_options',
			[ 'label' => esc_html__( 'Website', 'the-events-calendar' ) ]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_venue_website',
				'label' => esc_html__( 'Show Website', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'        => 'show_venue_website_header',
				'label'     => esc_html__( 'Show Header', 'the-events-calendar' ),
				'default'   => 'no',
				'condition' => [
					'show_venue_website' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'venue_website_header_tag',
				'label'     => esc_html__( 'HTML Tag', 'the-events-calendar' ),
				'condition' => [
					'show_venue_website_header' => 'yes',
				],
			]
		);

		$this->add_shared_control( 'link_target', [ 'prefix' => 'venue_website_link_target' ] );

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event venue website.
	 *
	 * @since TBD
	 */
	protected function venue_map_content_options() {
		$this->start_controls_section(
			'venue_map_content_options',
			[ 'label' => esc_html__( 'Map', 'the-events-calendar' ) ]
		);

		// Show Venue Map control.
		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_venue_map',
				'label' => esc_html__( 'Show Map', 'the-events-calendar' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the venue label.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_venue_header() {
		$this->start_controls_section(
			'venue_section_header_styling',
			[
				'label'     => esc_html__( 'Venue Header', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_venue_header' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'venue_header',
				'selector' => '{{WRAPPER}} .' . $this->get_header_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'venue_header_align',
				'selectors' => '{{WRAPPER}} .' . $this->get_header_class(),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the venue name.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_venue_name() {
		$this->start_controls_section(
			'venue_name_styling',
			[
				'label'     => esc_html__( 'Venue Name', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_venue_name' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'venue_name',
				'selector' => '{{WRAPPER}} .' . $this->get_name_base_class() . ', {{WRAPPER}} .' . $this->get_name_base_class() . ' a',
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'venue_name_align',
				'selectors' => '{{WRAPPER}} .' . $this->get_name_base_class(),
			]
		);


		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the venue phone.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_venue_phone() {
		$this->start_controls_section(
			'venue_phone_styling',
			[
				'label'     => esc_html__( 'Venue Phone', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_venue_phone' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix'    => 'phone_header',
				'label'     => esc_html__( 'Phone Header', 'the-events-calendar' ),
				'condition' => [
					'show_venue_phone_header' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'phone_header',
				'selector' => '{{WRAPPER}} .' . $this->get_phone_base_class() . '-header',
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'phone_header_align',
				'selectors' => '{{WRAPPER}} .' . $this->get_phone_base_class() . '-header',
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix' => 'phone_number',
				'label'  => esc_html__( 'Phone Number', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'phone_number',
				'selector' => '{{WRAPPER}} .' . $this->get_phone_base_class() . '-number',
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'phone_number_align',
				'selectors' => '{{WRAPPER}} .' . $this->get_phone_base_class() . '-number',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the venue address.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_venue_address() {
		$this->start_controls_section(
			'venue_address_styling',
			[
				'label'     => esc_html__( 'Venue Address', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_venue_address' => 'yes',
				],
			]
		);



		$this->add_shared_control(
			'subheader',
			[
				'prefix'    => 'address_header',
				'label'     => esc_html__( 'Phone Number', 'the-events-calendar' ),
				'condition' => [
					'show_venue_address_header' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'address_header',
				'selector' => '{{WRAPPER}} .' . $this->get_address_base_class() . '-header',
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'address_header_align',
				'selectors' => '{{WRAPPER}} .' . $this->get_address_base_class() . '-header',
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix' => 'address_text',
				'label'  => esc_html__( 'Address Text', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'address_text',
				'selector' => '{{WRAPPER}} .' . $this->get_address_base_class(),
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix'    => 'address_map_link',
				'label'     => esc_html__( 'Map Link', 'the-events-calendar' ),
				'separator' => 'before',
				'condition' => [
					'show_venue_address_map_link' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'address_map_link_typography',
				'selector' => '{{WRAPPER}} .' . $this->get_address_base_class() . '-map-link .tribe-events-gmap',
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'address_venue_map_link_align',
				'selectors' => '{{WRAPPER}} .' . $this->get_address_base_class() . '-map-link',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the venue website.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_venue_website() {
		$this->start_controls_section(
			'venue_website_styling',
			[
				'label'     => esc_html__( 'Venue Website', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_venue_website' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix'    => 'website_header',
				'label'     => esc_html__( 'Website Header', 'the-events-calendar' ),
				'condition' => [
					'show_venue_website_header' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'website_label_typography',
				'selector' => '{{WRAPPER}} .' . $this->get_website_base_class() . '-header',
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'website_label_align',
				'selectors' => '{{WRAPPER}} .' . $this->get_website_base_class() . '-header',
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix' => 'website_url',
				'label'  => esc_html__( 'Website Url', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'website_url',
				'selector' => '{{WRAPPER}} .' . $this->get_website_base_class() . '-url a',
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'website_url_align',
				'selectors' => '{{WRAPPER}} .' . $this->get_website_base_class() . '-url',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the venue map.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_venue_map() {
		$this->start_controls_section(
			'styling_section_title',
			[
				'label'       => esc_html__( 'Venue Map', 'the-events-calendar' ),
				'tab'         => Controls_Manager::TAB_STYLE,
				'conditional' => [
					'show_venue_map' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label'          => esc_html__( 'Width', 'the-events-calendar' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .' . $this->get_map_base_class() . ' iframe' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'space',
			[
				'label'          => esc_html__( 'Max Width', 'the-events-calendar' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .' . $this->get_map_base_class() . ' iframe' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label'      => esc_html__( 'Height', 'the-events-calendar' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vh', 'custom' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 500,
					],
					'vh' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => '%',
					'size' => 100, // the map's height should default to 100%.
				],
				'selectors'  => [
					'{{WRAPPER}} .' . $this->get_map_base_class() . ' iframe' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'venue_map_border',
				'selector'  => '{{WRAPPER}} .' . $this->get_map_base_class(),
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'venue_map_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'the-events-calendar' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					'{{WRAPPER}} .' . $this->get_map_base_class() => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'venue_map_box_shadow',
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- This is not a query.
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .' . $this->get_map_base_class(),
			]
		);

		$this->end_controls_section();
	}
}