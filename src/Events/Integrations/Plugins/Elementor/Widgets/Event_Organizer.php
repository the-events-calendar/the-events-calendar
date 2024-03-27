<?php
/**
 * Event Organizer Elementor Widget.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Typography;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Event_Organizer
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Organizer extends Abstract_Widget {
	/**
	 * Widget slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static string $slug = 'event_organizer';

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
		return esc_html__( 'Event Organizer', 'the-events-calendar' );
	}


	/**
	 * Get the template args for the widget.
	 *
	 * @since TBD
	 *
	 * @return array The template args.
	 */
	protected function template_args(): array {
		$event_id = $this->get_event_id();
		$settings = $this->get_settings_for_display();
		$multiple = $this->has_multiple_organizers();

		if ( isset( $settings['organizer_website_link_target'] ) ) {
			$this->set_template_filter(
				'tribe_get_event_website_link_target',
				function () use ( $settings ) {
					return $settings['organizer_website_link_target'];
				}
			);
		}

		return [
			'organizer_ids'       => array_filter( tribe_get_organizer_ids( $event_id ) ),
			'show_header'         => isset( $settings['show_organizer_header'] ) ? tribe_is_truthy( $settings['show_organizer_header'] ) : true,
			'link_name'           => isset( $settings['link_organizer_name'] ) ? tribe_is_truthy( $settings['link_organizer_name'] ) : true,
			'show_name'           => isset( $settings['show_organizer_name'] ) ? tribe_is_truthy( $settings['show_organizer_name'] ) : true,
			'show_phone'          => isset( $settings['show_organizer_phone'] ) ? tribe_is_truthy( $settings['show_organizer_phone'] ) : true,
			'show_email'          => isset( $settings['show_organizer_email'] ) ? tribe_is_truthy( $settings['show_organizer_email'] ) : true,
			'show_website'        => isset( $settings['show_organizer_website'] ) ? tribe_is_truthy( $settings['show_organizer_website'] ) : true,
			'show_phone_header'   => isset( $settings['show_organizer_phone_header'] ) ? tribe_is_truthy( $settings['show_organizer_phone_header'] ) : true,
			'show_email_header'   => isset( $settings['show_organizer_email_header'] ) ? tribe_is_truthy( $settings['show_organizer_email_header'] ) : true,
			'show_website_header' => isset( $settings['show_organizer_website_header'] ) ? tribe_is_truthy( $settings['show_organizer_website_header'] ) : true,
			'header_tag'          => $settings['organizer_header_tag'] ?? 'h2',
			'phone_header_tag'    => $settings['organizer_phone_header_tag'] ?? 'h3',
			'email_header_tag'    => $settings['organizer_email_header_tag'] ?? 'h3',
			'website_header_tag'  => $settings['organizer_website_header_tag'] ?? 'h3',
			'email_header_text'   => $this->get_email_header_text(),
			'phone_header_text'   => $this->get_phone_header_text(),
			'website_header_text' => $this->get_website_header_text(),
			'multiple'            => $multiple,
			'settings'            => $settings,
			'event_id'            => $event_id,
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
		$target_override = $settings['organizer_website_link_target'];

		if ( ! $target_override ) {
			return $link_target;
		}

		return $target_override;
	}

	/**
	 * Get the email header text for the widget.
	 *
	 * @since TBD
	 *
	 * @return string The email header text.
	 */
	protected function get_email_header_text(): string {
		$header_text = _x(
			'Email',
			'The header string for the Elementor event organizer widget email section.',
			'the-events-calendar'
		);

		/**
		 * Filters the email header text for the event organizer widget.
		 *
		 * @since TBD
		 *
		 * @param string $header_text The header text.
		 * @param Event_Venue $this The event organizer widget instance.
		 *
		 * @return string The filtered header text.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_widget_email_header_text', $header_text, $this );
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
			'The header string for the Elementor event organizer widget phone section.',
			'the-events-calendar'
		);

		/**
		 * Filters the phone header text for the event organizer widget.
		 *
		 * @since TBD
		 *
		 * @param string $header_text The header text.
		 * @param Event_Venue $this The event organizer widget instance.
		 *
		 * @return string The filtered header text.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_widget_phone_header_text', $header_text, $this );
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
			'The header string for the Elementor event organizer widget website section.',
			'the-events-calendar'
		);

		/**
		 * Filters the website header text for the event organizer widget.
		 *
		 * @since TBD
		 *
		 * @param string $header_text The header text.
		 * @param Event_Venue $this The event organizer widget instance.
		 *
		 * @return string The filtered header text.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_widget_website_header_text', $header_text, $this );
	}

	/**
	 * Get the classes for the event organizer widget.
	 *
	 * @since TBD
	 *
	 * @return array<string> The classes for the event organizer widget.
	 */
	public function get_widget_header_classes(): array {
		$classes = [
			'tribe-events-single-section-title',
			$this->get_widget_class() . '-header',
		];

		/**
		 * Filters the classes for the event organizer widget header.
		 *
		 * @since TBD
		 *
		 * @param array $classes The widget header classes.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_header_class', $classes, $this );
	}

	/**
	 * Get the base class for the event organizer name list.
	 *
	 * @since TBD
	 *
	 * @return string The name class.
	 */
	public function get_name_base_class(): string {
		$class = $this->get_widget_class() . '-name';

		/**
		 * Filters the base class for the event organizer name section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The name base class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_name_class', $class, $this );
	}

	/**
	 * Get the base class for the event organizer phone section.
	 *
	 * @since TBD
	 *
	 * @return string The phone class.
	 */
	public function get_phone_base_class(): string {
		$class = $this->get_widget_class() . '-phone';

		/**
		 * Filters the base class for the event organizer phone section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The phone base class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_phone_class', $class, $this );
	}

	/**
	 * Get the label class for the event organizer phone section.
	 *
	 * @since TBD
	 *
	 * @return string The phone class.
	 */
	public function get_phone_label_class(): string {
		$class = $this->get_phone_base_class() . '-label';

		/**
		 * Filters the label class for the event organizer phone section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The phone label class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_phone_label_class', $class, $this );
	}

	/**
	 * Get the base class for the event organizer email section.
	 *
	 * @since TBD
	 *
	 * @return array The address header classes.
	 */
	public function get_email_base_class(): string {
		$class = $this->get_widget_class() . '-email';

		/**
		 * Filters the base class for the event organizer email section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The email base class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_email_class', $class, $this );
	}

	/**
	 * Get the label class for the event organizer email section.
	 *
	 * @since TBD
	 *
	 * @return string The email class.
	 */
	public function get_email_label_class(): string {
		$class = $this->get_email_base_class() . '-label';

		/**
		 * Filters the label class for the event organizer email section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The email label class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_email_label_class', $class, $this );
	}

	/**
	 * Get the base class for the event organizer website section.
	 *
	 * @since TBD
	 *
	 * @return array The address header classes.
	 */
	public function get_website_base_class(): string {
		$class = $this->get_widget_class() . '-website';

		/**
		 * Filters the base class for the event organizer website section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The website base class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_website_class', $class, $this );
	}

	/**
	 * Get the label class for the event organizer website section.
	 *
	 * @since TBD
	 *
	 * @return string The website class.
	 */
	public function get_website_label_class(): string {
		$class = $this->get_website_base_class() . '-label';

		/**
		 * Filters the label class for the event organizer website section header.
		 *
		 * @since TBD
		 *
		 * @param string $class The website label class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_website_label_class', $class, $this );
	}

	/**
	 * Get the class(es) for the event organizer container.
	 *
	 * @since TBD
	 *
	 * @param string $format The format for the class(es).
	 *                       If anything other than "array" is passed, the class(es) will be returned as a string.
	 *
	 * @return string|array The class(es) for the event organizer container.
	 */
	public function get_container_classes( $format = 'array' ) {
		$container_class   = $this->get_element_classes( 'array' );
		$settings          = $this->get_settings_for_display();
		$container_class[] = $this->get_widget_class();

		if ( ! empty( $settings['align'] ) ) {
			$container_class[] = $this->get_widget_class() . '--align-' . esc_attr( $settings['align'] );
		}

		if ( $format !== 'array' ) {
			return implode( ' ', $container_class );
		}

		return $container_class;
	}

	/**
	 * Checks whether the event being previewed has multiple organizers assigned.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the event has multiple organizers.
	 */
	protected function has_multiple_organizers() {
		$event_id      = $this->get_event_id();
		$organizer_ids = array_filter( tribe_get_organizer_ids( $event_id ) );

		return count( $organizer_ids ) > 1;
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

		$this->organizer_name_content_options();

		// Only show the following options if the event has a single organizer.
		if ( ! $this->has_multiple_organizers() ) {
			$this->organizer_phone_content_options();

			$this->organizer_email_content_options();

			$this->organizer_website_content_options();
		}
	}

	/**
	 * Add controls for text content of the event organizer.
	 *
	 * @since TBD
	 */
	protected function content_options() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__( 'Event Organizer', 'the-events-calendar' ),
			]
		);

		// Widget alignment control.
		$this->add_responsive_control(
			'align',
			[
				'label'     => esc_html__( 'Alignment', 'the-events-calendar' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [
						'title' => esc_html__( 'Left', 'the-events-calendar' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => esc_html__( 'Center', 'the-events-calendar' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => esc_html__( 'Right', 'the-events-calendar' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'the-events-calendar' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_class() => 'text-align: {{VALUE}};',
				],
			]
		);

		// Show Organizer Header control.
		$this->add_control(
			'show_organizer_header',
			[
				'label'     => esc_html__( 'Show Widget Header', 'the-events-calendar' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'the-events-calendar' ),
				'label_off' => esc_html__( 'No', 'the-events-calendar' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'organizer_header_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'the-events-calendar' ),
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
					'show_organizer_header' => 'yes',
				],
			]
		);

		// Show Organizer Name control.
		$this->add_control(
			'show_organizer_name',
			[
				'label'     => esc_html__( 'Show Name', 'the-events-calendar' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'the-events-calendar' ),
				'label_off' => esc_html__( 'No', 'the-events-calendar' ),
				'default'   => 'yes',
			]
		);

		// Only show the following options if the event has a single organizer.
		if ( ! $this->has_multiple_organizers() ) {
			// Show Organizer Phone control.
			$this->add_control(
				'show_organizer_phone',
				[
					'label'     => esc_html__( 'Show Phone', 'the-events-calendar' ),
					'type'      => Controls_Manager::SWITCHER,
					'label_on'  => esc_html__( 'Yes', 'the-events-calendar' ),
					'label_off' => esc_html__( 'No', 'the-events-calendar' ),
					'default'   => 'yes',
				]
			);

			// Show Organizer Email control.
			$this->add_control(
				'show_organizer_email',
				[
					'label'     => esc_html__( 'Show Email', 'the-events-calendar' ),
					'type'      => Controls_Manager::SWITCHER,
					'label_on'  => esc_html__( 'Yes', 'the-events-calendar' ),
					'label_off' => esc_html__( 'No', 'the-events-calendar' ),
					'default'   => 'yes',
				]
			);

			// Show Organizer Website control.
			$this->add_control(
				'show_organizer_website',
				[
					'label'     => esc_html__( 'Show Website', 'the-events-calendar' ),
					'type'      => Controls_Manager::SWITCHER,
					'label_on'  => esc_html__( 'Yes', 'the-events-calendar' ),
					'label_off' => esc_html__( 'No', 'the-events-calendar' ),
					'default'   => 'yes',
				]
			);
		}

		$this->end_controls_section();
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
	 * Add controls for text content of the event organizer name.
	 *
	 * @since TBD
	 */
	protected function organizer_name_content_options() {
		$this->start_controls_section(
			'organizer_name_content_options',
			[
				'label'     => esc_html__( 'Event Organizer Name', 'the-events-calendar' ),
				'condition' => [
					'show_organizer_name' => 'yes',
				],
			]
		);

		// Show Organizer Header control.
		$this->add_control(
			'link_organizer_name',
			[
				'label'     => esc_html__( 'Link to Organizer Profile', 'the-events-calendar' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'the-events-calendar' ),
				'label_off' => esc_html__( 'No', 'the-events-calendar' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'organizer_name_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'the-events-calendar' ),
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
					'show_organizer_header' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event organizer phone.
	 *
	 * @since TBD
	 */
	protected function organizer_phone_content_options() {
		$this->start_controls_section(
			'organizer_phone_content_options',
			[
				'label'     => esc_html__( 'Event Organizer Phone', 'the-events-calendar' ),
				'condition' => [
					'show_organizer_phone' => 'yes',
				],
			]
		);

		// Show Organizer Header control.
		$this->add_control(
			'show_organizer_phone_header',
			[
				'label'     => esc_html__( 'Show Header', 'the-events-calendar' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'the-events-calendar' ),
				'label_off' => esc_html__( 'No', 'the-events-calendar' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'organizer_phone_header_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'the-events-calendar' ),
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
					'show_organizer_phone_header' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event organizer email.
	 *
	 * @since TBD
	 */
	protected function organizer_email_content_options() {
		$this->start_controls_section(
			'organizer_email_content_options',
			[
				'label'     => esc_html__( 'Event Organizer Email', 'the-events-calendar' ),
				'condition' => [
					'show_organizer_email' => 'yes',
				],
			]
		);

		// Show Organizer Header control.
		$this->add_control(
			'show_organizer_email_header',
			[
				'label'     => esc_html__( 'Show Header', 'the-events-calendar' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'the-events-calendar' ),
				'label_off' => esc_html__( 'No', 'the-events-calendar' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'organizer_email_header_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'the-events-calendar' ),
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
					'show_organizer_email_header' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event organizer website.
	 *
	 * @since TBD
	 */
	protected function organizer_website_content_options() {
		$this->start_controls_section(
			'organizer_website_content_options',
			[
				'label'     => esc_html__( 'Event Organizer Website', 'the-events-calendar' ),
				'condition' => [
					'show_organizer_website' => 'yes',
				],
			]
		);

		// Show Organizer Header control.
		$this->add_control(
			'show_organizer_website_header',
			[
				'label'     => esc_html__( 'Show Header', 'the-events-calendar' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'the-events-calendar' ),
				'label_off' => esc_html__( 'No', 'the-events-calendar' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'organizer_website_header_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'the-events-calendar' ),
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
					'show_organizer_website_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'organizer_website_link_target',
			[
				'label'       => esc_html__( 'Link Target', 'the-events-calendar' ),
				'description' => esc_html__( 'Choose whether to open the organizer website link in the same window or a new window.', 'the-events-calendar' ),
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
	 * Add controls for text styling of the event organizer.
	 *
	 * @since TBD
	 */
	protected function styling_options() {
		$this->style_organizer_label();

		$this->style_organizer_name();

		$this->style_organizer_phone();

		$this->style_organizer_email();

		$this->style_organizer_website();
	}

	/**
	 * Assembles the styling controls for the organizer label.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_organizer_label() {
		$this->start_controls_section(
			'organizer_section_header_styling',
			[
				'label'     => esc_html__( 'Organizer Header', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_organizer_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'header_color',
			[
				'label'     => esc_html__( 'Text Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_header_classes()[0] => 'color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .' . $this->get_widget_header_classes()[0],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'header_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_header_classes()[0],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'header_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_header_classes()[0],
			]
		);

		$this->add_control(
			'header_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'the-events-calendar' ),
					'multiply'    => esc_html__( 'Multiply', 'the-events-calendar' ),
					'screen'      => esc_html__( 'Screen', 'the-events-calendar' ),
					'overlay'     => esc_html__( 'Overlay', 'the-events-calendar' ),
					'darken'      => esc_html__( 'Darken', 'the-events-calendar' ),
					'lighten'     => esc_html__( 'Lighten', 'the-events-calendar' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'the-events-calendar' ),
					'saturation'  => esc_html__( 'Saturation', 'the-events-calendar' ),
					'color'       => esc_html__( 'Color', 'the-events-calendar' ),
					'difference'  => esc_html__( 'Difference', 'the-events-calendar' ),
					'exclusion'   => esc_html__( 'Exclusion', 'the-events-calendar' ),
					'hue'         => esc_html__( 'Hue', 'the-events-calendar' ),
					'luminosity'  => esc_html__( 'Luminosity', 'the-events-calendar' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_header_classes()[0] => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the organizer name.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_organizer_name() {
		$this->start_controls_section(
			'organizer_name_styling',
			[
				'label'     => esc_html__( 'Organizer Name', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_organizer_name' => 'yes',
				],
			]
		);

		$this->add_control(
			'name_color',
			[
				'label'     => esc_html__( 'Text Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_name_base_class() . ' a' => 'color: {{VALUE}}; border-bottom-color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'name_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'name_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_widget_class(),
			]
		);

		$this->add_control(
			'name_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'the-events-calendar' ),
					'multiply'    => esc_html__( 'Multiply', 'the-events-calendar' ),
					'screen'      => esc_html__( 'Screen', 'the-events-calendar' ),
					'overlay'     => esc_html__( 'Overlay', 'the-events-calendar' ),
					'darken'      => esc_html__( 'Darken', 'the-events-calendar' ),
					'lighten'     => esc_html__( 'Lighten', 'the-events-calendar' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'the-events-calendar' ),
					'saturation'  => esc_html__( 'Saturation', 'the-events-calendar' ),
					'color'       => esc_html__( 'Color', 'the-events-calendar' ),
					'difference'  => esc_html__( 'Difference', 'the-events-calendar' ),
					'exclusion'   => esc_html__( 'Exclusion', 'the-events-calendar' ),
					'hue'         => esc_html__( 'Hue', 'the-events-calendar' ),
					'luminosity'  => esc_html__( 'Luminosity', 'the-events-calendar' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_widget_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the organizer phone.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_organizer_phone() {
		$this->start_controls_section(
			'organizer_phone_styling',
			[
				'label'     => esc_html__( 'Organizer Phone', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_organizer_phone' => 'yes',
				],
			]
		);

		$this->add_control(
			'phone_label_header',
			[
				'label' => esc_html__( 'Phone Header', 'the-events-calendar' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'phone_label_color',
			[
				'label'     => esc_html__( 'Text Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_phone_label_class() => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'phone_label_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_phone_label_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'phone_label_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_phone_label_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'phone_label_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_phone_label_class(),
			]
		);

		$this->add_control(
			'phone_label_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'the-events-calendar' ),
					'multiply'    => esc_html__( 'Multiply', 'the-events-calendar' ),
					'screen'      => esc_html__( 'Screen', 'the-events-calendar' ),
					'overlay'     => esc_html__( 'Overlay', 'the-events-calendar' ),
					'darken'      => esc_html__( 'Darken', 'the-events-calendar' ),
					'lighten'     => esc_html__( 'Lighten', 'the-events-calendar' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'the-events-calendar' ),
					'saturation'  => esc_html__( 'Saturation', 'the-events-calendar' ),
					'color'       => esc_html__( 'Color', 'the-events-calendar' ),
					'difference'  => esc_html__( 'Difference', 'the-events-calendar' ),
					'exclusion'   => esc_html__( 'Exclusion', 'the-events-calendar' ),
					'hue'         => esc_html__( 'Hue', 'the-events-calendar' ),
					'luminosity'  => esc_html__( 'Luminosity', 'the-events-calendar' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_phone_label_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'after',
			]
		);

		$this->add_control(
			'phone_header',
			[
				'label' => esc_html__( 'Phone Text', 'the-events-calendar' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'phone_color',
			[
				'label'     => esc_html__( 'Text Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_phone_base_class() => 'color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .' . $this->get_phone_base_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'phone_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_phone_base_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'phone_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_phone_base_class(),
			]
		);

		$this->add_control(
			'phone_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'the-events-calendar' ),
					'multiply'    => esc_html__( 'Multiply', 'the-events-calendar' ),
					'screen'      => esc_html__( 'Screen', 'the-events-calendar' ),
					'overlay'     => esc_html__( 'Overlay', 'the-events-calendar' ),
					'darken'      => esc_html__( 'Darken', 'the-events-calendar' ),
					'lighten'     => esc_html__( 'Lighten', 'the-events-calendar' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'the-events-calendar' ),
					'saturation'  => esc_html__( 'Saturation', 'the-events-calendar' ),
					'color'       => esc_html__( 'Color', 'the-events-calendar' ),
					'difference'  => esc_html__( 'Difference', 'the-events-calendar' ),
					'exclusion'   => esc_html__( 'Exclusion', 'the-events-calendar' ),
					'hue'         => esc_html__( 'Hue', 'the-events-calendar' ),
					'luminosity'  => esc_html__( 'Luminosity', 'the-events-calendar' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_phone_base_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the organizer email.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_organizer_email() {
		$this->start_controls_section(
			'organizer_email_styling',
			[
				'label'     => esc_html__( 'Organizer Email', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_organizer_email' => 'yes',
				],
			]
		);

		$this->add_control(
			'email_label_header',
			[
				'label' => esc_html__( 'Email Header', 'the-events-calendar' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'email_label_color',
			[
				'label'     => esc_html__( 'Text Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_email_label_class() => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'email_label_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_email_label_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'email_label_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_email_label_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'email_label_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_email_label_class(),
			]
		);

		$this->add_control(
			'email_label_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'the-events-calendar' ),
					'multiply'    => esc_html__( 'Multiply', 'the-events-calendar' ),
					'screen'      => esc_html__( 'Screen', 'the-events-calendar' ),
					'overlay'     => esc_html__( 'Overlay', 'the-events-calendar' ),
					'darken'      => esc_html__( 'Darken', 'the-events-calendar' ),
					'lighten'     => esc_html__( 'Lighten', 'the-events-calendar' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'the-events-calendar' ),
					'saturation'  => esc_html__( 'Saturation', 'the-events-calendar' ),
					'color'       => esc_html__( 'Color', 'the-events-calendar' ),
					'difference'  => esc_html__( 'Difference', 'the-events-calendar' ),
					'exclusion'   => esc_html__( 'Exclusion', 'the-events-calendar' ),
					'hue'         => esc_html__( 'Hue', 'the-events-calendar' ),
					'luminosity'  => esc_html__( 'Luminosity', 'the-events-calendar' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_email_label_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'after',
			]
		);

		$this->add_control(
			'email_header',
			[
				'label' => esc_html__( 'Email Text', 'the-events-calendar' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'email_color',
			[
				'label'     => esc_html__( 'Text Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_email_base_class() => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'email_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_email_base_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'email_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_email_base_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'email_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_email_base_class(),
			]
		);

		$this->add_control(
			'email_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'the-events-calendar' ),
					'multiply'    => esc_html__( 'Multiply', 'the-events-calendar' ),
					'screen'      => esc_html__( 'Screen', 'the-events-calendar' ),
					'overlay'     => esc_html__( 'Overlay', 'the-events-calendar' ),
					'darken'      => esc_html__( 'Darken', 'the-events-calendar' ),
					'lighten'     => esc_html__( 'Lighten', 'the-events-calendar' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'the-events-calendar' ),
					'saturation'  => esc_html__( 'Saturation', 'the-events-calendar' ),
					'color'       => esc_html__( 'Color', 'the-events-calendar' ),
					'difference'  => esc_html__( 'Difference', 'the-events-calendar' ),
					'exclusion'   => esc_html__( 'Exclusion', 'the-events-calendar' ),
					'hue'         => esc_html__( 'Hue', 'the-events-calendar' ),
					'luminosity'  => esc_html__( 'Luminosity', 'the-events-calendar' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_email_base_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the organizer website.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function style_organizer_website() {
		$this->start_controls_section(
			'organizer_website_styling',
			[
				'label'     => esc_html__( 'Organizer Website', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_organizer_website' => 'yes',
				],
			]
		);

		$this->add_control(
			'website_label_header',
			[
				'label' => esc_html__( 'Website Header', 'the-events-calendar' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'website_label_color',
			[
				'label'     => esc_html__( 'Text Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_website_label_class() => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'website_label_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .' . $this->get_website_label_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'website_label_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_website_label_class(),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'website_label_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_website_label_class(),
			]
		);

		$this->add_control(
			'website_label_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'the-events-calendar' ),
					'multiply'    => esc_html__( 'Multiply', 'the-events-calendar' ),
					'screen'      => esc_html__( 'Screen', 'the-events-calendar' ),
					'overlay'     => esc_html__( 'Overlay', 'the-events-calendar' ),
					'darken'      => esc_html__( 'Darken', 'the-events-calendar' ),
					'lighten'     => esc_html__( 'Lighten', 'the-events-calendar' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'the-events-calendar' ),
					'saturation'  => esc_html__( 'Saturation', 'the-events-calendar' ),
					'color'       => esc_html__( 'Color', 'the-events-calendar' ),
					'difference'  => esc_html__( 'Difference', 'the-events-calendar' ),
					'exclusion'   => esc_html__( 'Exclusion', 'the-events-calendar' ),
					'hue'         => esc_html__( 'Hue', 'the-events-calendar' ),
					'luminosity'  => esc_html__( 'Luminosity', 'the-events-calendar' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_website_label_class() => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'after',
			]
		);

		$this->add_control(
			'website_header',
			[
				'label' => esc_html__( 'Website Url', 'the-events-calendar' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'website_color',
			[
				'label'     => esc_html__( 'Text Color', 'the-events-calendar' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_website_base_class() . ' a' => 'color: {{VALUE}}; border-bottom-color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .' . $this->get_website_base_class() . ' a',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'website_text_stroke',
				'selector' => '{{WRAPPER}} .' . $this->get_website_base_class() . ' a',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'website_text_shadow',
				'selector' => '{{WRAPPER}} .' . $this->get_website_base_class() . ' a',
			]
		);

		$this->add_control(
			'website_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'the-events-calendar' ),
					'multiply'    => esc_html__( 'Multiply', 'the-events-calendar' ),
					'screen'      => esc_html__( 'Screen', 'the-events-calendar' ),
					'overlay'     => esc_html__( 'Overlay', 'the-events-calendar' ),
					'darken'      => esc_html__( 'Darken', 'the-events-calendar' ),
					'lighten'     => esc_html__( 'Lighten', 'the-events-calendar' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'the-events-calendar' ),
					'saturation'  => esc_html__( 'Saturation', 'the-events-calendar' ),
					'color'       => esc_html__( 'Color', 'the-events-calendar' ),
					'difference'  => esc_html__( 'Difference', 'the-events-calendar' ),
					'exclusion'   => esc_html__( 'Exclusion', 'the-events-calendar' ),
					'hue'         => esc_html__( 'Hue', 'the-events-calendar' ),
					'luminosity'  => esc_html__( 'Luminosity', 'the-events-calendar' ),
				],
				'selectors' => [
					'{{WRAPPER}} .' . $this->get_website_base_class() . ' a' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}
}
