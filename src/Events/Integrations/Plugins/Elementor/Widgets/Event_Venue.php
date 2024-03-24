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
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Typography;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Event_Venue
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Venue extends Abstract_Widget {
	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_venue';

	/**
	 * Create the widget title.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Event Venue', 'tribe-events-calendar-pro' );
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
				'tec_get_event_venue_website_link_target',
				function () use ( $settings ) {
					return $settings['organizer_website_link_target'];
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
			'tribe-events-calendar-pro'
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
		return apply_filters( 'tec_events_elementor_event_venue_widget_header_text', $header_text, $this );
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
			'tribe-events-calendar-pro'
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
		return apply_filters( 'tec_events_elementor_event_venue_widget_website_header_text', $header_text, $this );
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
			'tribe-events-calendar-pro'
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
		return apply_filters( 'tec_events_elementor_event_venue_widget_phone_header_text', $header_text, $this );
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
			'tribe-events-calendar-pro'
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
		return apply_filters( 'tec_events_elementor_event_venue_widget_address_header_text', $header_text, $this );
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
	 * Get the classes for the widget header.
	 *
	 * @since TBD
	 *
	 * @return array The header classes.
	 */
	public function get_header_classes() {
		$classes = [
			'tribe-events-single-section-title',
			$this->get_widget_class() . '-header',
		];

		/**
		 * Filters the classes for the event venue widget header.
		 *
		 * @since TBD
		 *
		 * @param array $classes The widget header classes.
		 * @param Event_Venue $this The event venue widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_venue_header_class', $classes, $this );
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
		return apply_filters( 'tec_events_elementor_event_venue_name_class', $class, $this );
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
		return apply_filters( 'tec_events_elementor_event_venue_address_class', $class, $this );
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
		return apply_filters( 'tec_events_elementor_event_venue_phone_class', $class, $this );
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
		return apply_filters( 'tec_events_elementor_event_venue_website_class', $class, $this );
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
		return apply_filters( 'tec_events_elementor_event_venue_map_class', $class, $this );
	}

	/**
	 * Register controls for the widget.
	 *
	 * @since TBD
	 */
	protected function register_controls() {
		// Content tab.
		$this->content_panel();
		// Style tab.
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
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since TBD
	 */
	protected function style_panel() {
		// Styling options.
		$this->styling_options();
	}

	/**
	 * Add controls for text content of the event venue.
	 *
	 * @since TBD
	 */
	protected function content_options() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__( 'Event Venue', 'tribe-events-calendar-pro' ),
			]
		);

		// Widget alignment control.
		$this->add_responsive_control(
			'align',
			[
				'label'     => esc_html__( 'Alignment', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [
						'title' => esc_html__( 'Left', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => esc_html__( 'Center', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => esc_html__( 'Right', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'tribe-events-calendar-pro' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .' . implode( ' ', (array) $this->get_container_classes() ) => 'text-align: {{VALUE}};',
				],
			]
		);

		// Show Venue Header control.
		$this->add_control(
			'show_venue_header',
			[
				'label'     => esc_html__( 'Show Widget Header', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'venue_header_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default'   => 'h2',
				'condition' => [
					'show_venue_header' => 'yes',
				],
			]
		);

		// Show Venue Name control.
		$this->add_control(
			'show_venue_name',
			[
				'label'     => esc_html__( 'Show Name', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
			]
		);

		// Show Venue Address control.
		$this->add_control(
			'show_venue_address',
			[
				'label'     => esc_html__( 'Show Address', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
			]
		);

		// Show Venue Phone control.
		$this->add_control(
			'show_venue_phone',
			[
				'label'     => esc_html__( 'Show Phone', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
			]
		);

		// Show Venue Website control.
		$this->add_control(
			'show_venue_website',
			[
				'label'     => esc_html__( 'Show Website', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
			]
		);

		// Show Venue Map control.
		$this->add_control(
			'show_venue_map',
			[
				'label'     => esc_html__( 'Show Map', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
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
			[
				'label'     => esc_html__( 'Event Venue Name', 'tribe-events-calendar-pro' ),
				'condition' => [
					'show_venue_name' => 'yes',
				],
			]
		);

		$this->add_control(
			'venue_name_html_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default'   => 'h3',
				'condition' => [
					'show_venue_address_header' => 'yes',
				],
			]
		);

		// Show Venue Header control.
		$this->add_control(
			'link_venue_name',
			[
				'label'     => esc_html__( 'Link to Venue Profile', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
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
			[
				'label'     => esc_html__( 'Event Venue Phone', 'tribe-events-calendar-pro' ),
				'condition' => [
					'show_venue_phone' => 'yes',
				],
			]
		);

		// Show Venue Header control.
		$this->add_control(
			'show_venue_phone_header',
			[
				'label'     => esc_html__( 'Show Header', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'no',
			]
		);

		$this->add_control(
			'venue_phone_header_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default'   => 'h3',
				'condition' => [
					'show_venue_phone_header' => 'yes',
				],
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
			[
				'label'     => esc_html__( 'Event Venue Address', 'tribe-events-calendar-pro' ),
				'condition' => [
					'show_venue_address' => 'yes',
				],
			]
		);

		// Show Venue Header control.
		$this->add_control(
			'show_venue_address_header',
			[
				'label'     => esc_html__( 'Show Header', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'no',
			]
		);

		$this->add_control(
			'venue_address_header_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default'   => 'h3',
				'condition' => [
					'show_venue_address_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_venue_address_map_link',
			[
				'label'     => esc_html__( 'Show Map Link', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'yes',
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
			[
				'label'     => esc_html__( 'Event Venue Website', 'tribe-events-calendar-pro' ),
				'condition' => [
					'show_venue_website' => 'yes',
				],
			]
		);

		// Show Venue Header control.
		$this->add_control(
			'show_venue_website_header',
			[
				'label'     => esc_html__( 'Show Header', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off' => esc_html__( 'No', 'tribe-events-calendar-pro' ),
				'default'   => 'no',
			]
		);

		$this->add_control(
			'venue_website_header_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default'   => 'h3',
				'condition' => [
					'show_venue_website_header' => 'yes',
				],
			]
		);
		$this->add_control(
			'venue_website_link_target',
			[
				'label'       => esc_html__( 'Link Target', 'tribe-events-calendar-pro' ),
				'description' => esc_html__( 'Choose whether to open the venue website link in the same window or a new window.', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '_self',
				'options'     => [
					'_self'  => 'same window',
					'_blank' => 'new window',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text styling of the event venue.
	 *
	 * @since TBD
	 */
	protected function styling_options() {
		$this->style_venue_label();

		$this->style_venue_name();

		$this->style_venue_address();

		$this->style_venue_phone();

		$this->style_venue_website();

		$this->style_venue_map();
	}

	/**
	 * Assembles the styling controls for the venue label.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_venue_label() {
		$this->start_controls_section(
			'venue_section_header_styling',
			[
				'label'     => esc_html__( 'Venue Header', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_venue_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'header_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_header_classes()[0] => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'header_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_header_classes()[0],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'header_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_header_classes()[0],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'header_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_header_classes()[0],
			]
		);

		$this->add_control(
			'header_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'tribe-events-calendar-pro' ),
					'multiply'    => esc_html__( 'Multiply', 'tribe-events-calendar-pro' ),
					'screen'      => esc_html__( 'Screen', 'tribe-events-calendar-pro' ),
					'overlay'     => esc_html__( 'Overlay', 'tribe-events-calendar-pro' ),
					'darken'      => esc_html__( 'Darken', 'tribe-events-calendar-pro' ),
					'lighten'     => esc_html__( 'Lighten', 'tribe-events-calendar-pro' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'tribe-events-calendar-pro' ),
					'saturation'  => esc_html__( 'Saturation', 'tribe-events-calendar-pro' ),
					'color'       => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
					'difference'  => esc_html__( 'Difference', 'tribe-events-calendar-pro' ),
					'exclusion'   => esc_html__( 'Exclusion', 'tribe-events-calendar-pro' ),
					'hue'         => esc_html__( 'Hue', 'tribe-events-calendar-pro' ),
					'luminosity'  => esc_html__( 'Luminosity', 'tribe-events-calendar-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} ' . $this->get_header_classes()[0] => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
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
				'label'     => esc_html__( 'Venue Name', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_venue_name' => 'yes',
				],
			]
		);

		$this->add_control(
			'name_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_name_base_class() . ', {{WRAPPER}} .' . $this->get_name_base_class() . ' a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'name_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_name_base_class() . ', {{WRAPPER}} .' . $this->get_name_base_class() . ' a',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'name_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_name_base_class() . ', {{WRAPPER}} .' . $this->get_name_base_class() . ' a',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'name_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_name_base_class() . ', {{WRAPPER}} .' . $this->get_name_base_class() . ' a',
			]
		);

		$this->add_control(
			'name_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'tribe-events-calendar-pro' ),
					'multiply'    => esc_html__( 'Multiply', 'tribe-events-calendar-pro' ),
					'screen'      => esc_html__( 'Screen', 'tribe-events-calendar-pro' ),
					'overlay'     => esc_html__( 'Overlay', 'tribe-events-calendar-pro' ),
					'darken'      => esc_html__( 'Darken', 'tribe-events-calendar-pro' ),
					'lighten'     => esc_html__( 'Lighten', 'tribe-events-calendar-pro' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'tribe-events-calendar-pro' ),
					'saturation'  => esc_html__( 'Saturation', 'tribe-events-calendar-pro' ),
					'color'       => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
					'difference'  => esc_html__( 'Difference', 'tribe-events-calendar-pro' ),
					'exclusion'   => esc_html__( 'Exclusion', 'tribe-events-calendar-pro' ),
					'hue'         => esc_html__( 'Hue', 'tribe-events-calendar-pro' ),
					'luminosity'  => esc_html__( 'Luminosity', 'tribe-events-calendar-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_name_base_class() . ', {{WRAPPER}} .' . $this->get_name_base_class() . ' a' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
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
				'label'     => esc_html__( 'Venue Phone', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_venue_phone' => 'yes',
				],
			]
		);

		$this->add_control(
			'phone_label_header',
			[
				'label'     => esc_html__( 'Phone Header', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'show_venue_phone_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'phone_label_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_phone_base_class() . '-header' => 'color: {{VALUE}};',
				],
				'condition' => [
					'show_venue_phone_header' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'phone_label_typography',
				'global'    => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector'  => '{{WRAPPER}} .' . $this->get_phone_base_class() . '-header',
				'condition' => [
					'show_venue_phone_header' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'      => 'phone_label_text_stroke',
				'selector'  => '{{WRAPPER}} .' . $this->get_phone_base_class() . '-header',
				'condition' => [
					'show_venue_phone_header' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'      => 'phone_label_text_shadow',
				'selector'  => '{{WRAPPER}} .' . $this->get_phone_base_class() . '-header',
				'condition' => [
					'show_venue_phone_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'phone_label_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'tribe-events-calendar-pro' ),
					'multiply'    => esc_html__( 'Multiply', 'tribe-events-calendar-pro' ),
					'screen'      => esc_html__( 'Screen', 'tribe-events-calendar-pro' ),
					'overlay'     => esc_html__( 'Overlay', 'tribe-events-calendar-pro' ),
					'darken'      => esc_html__( 'Darken', 'tribe-events-calendar-pro' ),
					'lighten'     => esc_html__( 'Lighten', 'tribe-events-calendar-pro' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'tribe-events-calendar-pro' ),
					'saturation'  => esc_html__( 'Saturation', 'tribe-events-calendar-pro' ),
					'color'       => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
					'difference'  => esc_html__( 'Difference', 'tribe-events-calendar-pro' ),
					'exclusion'   => esc_html__( 'Exclusion', 'tribe-events-calendar-pro' ),
					'hue'         => esc_html__( 'Hue', 'tribe-events-calendar-pro' ),
					'luminosity'  => esc_html__( 'Luminosity', 'tribe-events-calendar-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_phone_base_class() . '-header' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'after',
				'condition' => [
					'show_venue_phone_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'phone_header',
			[
				'label' => esc_html__( 'Phone Text', 'tribe-events-calendar-pro' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'phone_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_phone_base_class() . '-number' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'phone_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_phone_base_class() . '-number',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'phone_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_phone_base_class() . '-number',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'phone_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_phone_base_class() . '-number',
			]
		);

		$this->add_control(
			'phone_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'tribe-events-calendar-pro' ),
					'multiply'    => esc_html__( 'Multiply', 'tribe-events-calendar-pro' ),
					'screen'      => esc_html__( 'Screen', 'tribe-events-calendar-pro' ),
					'overlay'     => esc_html__( 'Overlay', 'tribe-events-calendar-pro' ),
					'darken'      => esc_html__( 'Darken', 'tribe-events-calendar-pro' ),
					'lighten'     => esc_html__( 'Lighten', 'tribe-events-calendar-pro' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'tribe-events-calendar-pro' ),
					'saturation'  => esc_html__( 'Saturation', 'tribe-events-calendar-pro' ),
					'color'       => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
					'difference'  => esc_html__( 'Difference', 'tribe-events-calendar-pro' ),
					'exclusion'   => esc_html__( 'Exclusion', 'tribe-events-calendar-pro' ),
					'hue'         => esc_html__( 'Hue', 'tribe-events-calendar-pro' ),
					'luminosity'  => esc_html__( 'Luminosity', 'tribe-events-calendar-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_phone_base_class() . '-number' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
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
				'label'     => esc_html__( 'Venue Address', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_venue_address' => 'yes',
				],
			]
		);

		$this->add_control(
			'address_label_header',
			[
				'label'     => esc_html__( 'Address Header', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'show_venue_address_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'address_label_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_address_base_class() . '-header' => 'color: {{VALUE}};',
				],
				'condition' => [
					'show_venue_address_header' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'address_label_typography',
				'global'    => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector'  => '{{WRAPPER}} .' . $this->get_address_base_class() . '-header',
				'condition' => [
					'show_venue_address_header' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'      => 'address_label_text_stroke',
				'selector'  => '{{WRAPPER}} .' . $this->get_address_base_class() . '-header',
				'condition' => [
					'show_venue_address_header' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'      => 'address_label_text_shadow',
				'selector'  => '{{WRAPPER}} .' . $this->get_address_base_class() . '-header',
				'condition' => [
					'show_venue_address_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'address_label_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'tribe-events-calendar-pro' ),
					'multiply'    => esc_html__( 'Multiply', 'tribe-events-calendar-pro' ),
					'screen'      => esc_html__( 'Screen', 'tribe-events-calendar-pro' ),
					'overlay'     => esc_html__( 'Overlay', 'tribe-events-calendar-pro' ),
					'darken'      => esc_html__( 'Darken', 'tribe-events-calendar-pro' ),
					'lighten'     => esc_html__( 'Lighten', 'tribe-events-calendar-pro' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'tribe-events-calendar-pro' ),
					'saturation'  => esc_html__( 'Saturation', 'tribe-events-calendar-pro' ),
					'color'       => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
					'difference'  => esc_html__( 'Difference', 'tribe-events-calendar-pro' ),
					'exclusion'   => esc_html__( 'Exclusion', 'tribe-events-calendar-pro' ),
					'hue'         => esc_html__( 'Hue', 'tribe-events-calendar-pro' ),
					'luminosity'  => esc_html__( 'Luminosity', 'tribe-events-calendar-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_address_base_class() . '-header' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'after',
				'condition' => [
					'show_venue_address_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'address_header',
			[
				'label' => esc_html__( 'Address Text', 'tribe-events-calendar-pro' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'address_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_address_base_class()  => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'address_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_address_base_class() ,
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'address_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_address_base_class() ,
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'address_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_address_base_class() ,
			]
		);

		$this->add_control(
			'address_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'tribe-events-calendar-pro' ),
					'multiply'    => esc_html__( 'Multiply', 'tribe-events-calendar-pro' ),
					'screen'      => esc_html__( 'Screen', 'tribe-events-calendar-pro' ),
					'overlay'     => esc_html__( 'Overlay', 'tribe-events-calendar-pro' ),
					'darken'      => esc_html__( 'Darken', 'tribe-events-calendar-pro' ),
					'lighten'     => esc_html__( 'Lighten', 'tribe-events-calendar-pro' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'tribe-events-calendar-pro' ),
					'saturation'  => esc_html__( 'Saturation', 'tribe-events-calendar-pro' ),
					'color'       => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
					'difference'  => esc_html__( 'Difference', 'tribe-events-calendar-pro' ),
					'exclusion'   => esc_html__( 'Exclusion', 'tribe-events-calendar-pro' ),
					'hue'         => esc_html__( 'Hue', 'tribe-events-calendar-pro' ),
					'luminosity'  => esc_html__( 'Luminosity', 'tribe-events-calendar-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_address_base_class()  => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->add_control(
			'address_venue_map_link_header',
			[
				'label'     => esc_html__( 'Map Link', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'show_venue_address_map_link' => 'yes',
				],
			]
		);

		$this->add_control(
			'address_venue_map_link_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_address_base_class() . '-map-link .tribe-events-gmap' => 'color: {{VALUE}};',
				],
				'condition' => [
					'show_venue_address_map_link' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'address_venue_map_link_typography',
				'global'    => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector'  => '{{WRAPPER}} .' . $this->get_address_base_class() . '-map-link .tribe-events-gmap',
				'condition' => [
					'show_venue_address_map_link' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'      => 'address_venue_map_link_text_stroke',
				'selector'  => '{{WRAPPER}} .' . $this->get_address_base_class() . '-map-link .tribe-events-gmap',
				'condition' => [
					'show_venue_address_map_link' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'      => 'address_venue_map_link_text_shadow',
				'selector'  => '{{WRAPPER}} .' . $this->get_address_base_class() . '-map-link .tribe-events-gmap',
				'condition' => [
					'show_venue_address_map_link' => 'yes',
				],
			]
		);

		$this->add_control(
			'address_venue_map_link_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'tribe-events-calendar-pro' ),
					'multiply'    => esc_html__( 'Multiply', 'tribe-events-calendar-pro' ),
					'screen'      => esc_html__( 'Screen', 'tribe-events-calendar-pro' ),
					'overlay'     => esc_html__( 'Overlay', 'tribe-events-calendar-pro' ),
					'darken'      => esc_html__( 'Darken', 'tribe-events-calendar-pro' ),
					'lighten'     => esc_html__( 'Lighten', 'tribe-events-calendar-pro' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'tribe-events-calendar-pro' ),
					'saturation'  => esc_html__( 'Saturation', 'tribe-events-calendar-pro' ),
					'color'       => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
					'difference'  => esc_html__( 'Difference', 'tribe-events-calendar-pro' ),
					'exclusion'   => esc_html__( 'Exclusion', 'tribe-events-calendar-pro' ),
					'hue'         => esc_html__( 'Hue', 'tribe-events-calendar-pro' ),
					'luminosity'  => esc_html__( 'Luminosity', 'tribe-events-calendar-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_address_base_class() . '-map-link .tribe-events-gmap' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
				'condition' => [
					'show_venue_address_map_link' => 'yes',
				],
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
				'label'     => esc_html__( 'Venue Website', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_venue_website' => 'yes',
				],
			]
		);

		$this->add_control(
			'website_label_header',
			[
				'label'     => esc_html__( 'Website Header', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'show_venue_website_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'website_label_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_website_base_class() . '-header' => 'color: {{VALUE}};',
				],
				'condition' => [
					'show_venue_website_header' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'website_label_typography',
				'global'    => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector'  => '{{WRAPPER}} .' . $this->get_website_base_class() . '-header',
				'condition' => [
					'show_venue_website_header' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'      => 'website_label_text_stroke',
				'selector'  => '{{WRAPPER}} .' . $this->get_website_base_class() . '-header',
				'condition' => [
					'show_venue_website_header' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'      => 'website_label_text_shadow',
				'selector'  => '{{WRAPPER}} .' . $this->get_website_base_class() . '-header',
				'condition' => [
					'show_venue_website_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'website_label_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'tribe-events-calendar-pro' ),
					'multiply'    => esc_html__( 'Multiply', 'tribe-events-calendar-pro' ),
					'screen'      => esc_html__( 'Screen', 'tribe-events-calendar-pro' ),
					'overlay'     => esc_html__( 'Overlay', 'tribe-events-calendar-pro' ),
					'darken'      => esc_html__( 'Darken', 'tribe-events-calendar-pro' ),
					'lighten'     => esc_html__( 'Lighten', 'tribe-events-calendar-pro' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'tribe-events-calendar-pro' ),
					'saturation'  => esc_html__( 'Saturation', 'tribe-events-calendar-pro' ),
					'color'       => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
					'difference'  => esc_html__( 'Difference', 'tribe-events-calendar-pro' ),
					'exclusion'   => esc_html__( 'Exclusion', 'tribe-events-calendar-pro' ),
					'hue'         => esc_html__( 'Hue', 'tribe-events-calendar-pro' ),
					'luminosity'  => esc_html__( 'Luminosity', 'tribe-events-calendar-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_website_base_class() . '-header' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'after',
				'condition' => [
					'show_venue_website_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'venue_website_header',
			[
				'label' => esc_html__( 'Website Url', 'tribe-events-calendar-pro' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'website_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_website_base_class() . '-url a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'website_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_website_base_class() . '-url a',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'website_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_website_base_class() . '-url a',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'website_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_website_base_class() . '-url a',
			]
		);

		$this->add_control(
			'website_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'tribe-events-calendar-pro' ),
					'multiply'    => esc_html__( 'Multiply', 'tribe-events-calendar-pro' ),
					'screen'      => esc_html__( 'Screen', 'tribe-events-calendar-pro' ),
					'overlay'     => esc_html__( 'Overlay', 'tribe-events-calendar-pro' ),
					'darken'      => esc_html__( 'Darken', 'tribe-events-calendar-pro' ),
					'lighten'     => esc_html__( 'Lighten', 'tribe-events-calendar-pro' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'tribe-events-calendar-pro' ),
					'saturation'  => esc_html__( 'Saturation', 'tribe-events-calendar-pro' ),
					'color'       => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
					'difference'  => esc_html__( 'Difference', 'tribe-events-calendar-pro' ),
					'exclusion'   => esc_html__( 'Exclusion', 'tribe-events-calendar-pro' ),
					'hue'         => esc_html__( 'Hue', 'tribe-events-calendar-pro' ),
					'luminosity'  => esc_html__( 'Luminosity', 'tribe-events-calendar-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_website_base_class() . '-url a' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
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
				'label'     => esc_html__( 'Venue Map', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_venue_map' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label'          => esc_html__( 'Width', 'tribe-events-calendar-pro' ),
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
				'label'          => esc_html__( 'Max Width', 'tribe-events-calendar-pro' ),
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
				'label'      => esc_html__( 'Height', 'tribe-events-calendar-pro' ),
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
				'label'      => esc_html__( 'Border Radius', 'tribe-events-calendar-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					'{{WRAPPER}} .' . $this->get_map_base_class() => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// phpcs:disable WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'venue_map_box_shadow',
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .' . $this->get_map_base_class(),
			]
		);
		// phpcs:enable WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude

		$this->end_controls_section();
	}
}
