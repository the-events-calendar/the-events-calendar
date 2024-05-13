<?php
/**
 * Event Organizer Elementor Widget.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Elementor\Controls_Manager;
use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Class Event_Organizer
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
class Event_Organizer extends Abstract_Widget {
	use Traits\With_Shared_Controls;
	use Traits\Has_Preview_Data;
	use Traits\Event_Query;

	/**
	 * Widget slug.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	protected static string $slug = 'event_organizer';

	/**
	 * Whether the widget has styles to register/enqueue.
	 *
	 * @since 6.4.0
	 *
	 * @var bool
	 */
	protected static bool $has_styles = true;

	/**
	 * Create the widget title.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	protected function title(): string {
		return esc_html__( 'Event Organizer', 'the-events-calendar' );
	}

	/**
	 * Get the template args for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return array The template args.
	 */
	protected function template_args(): array {
		$event_id = $this->get_event_id();
		$settings = $this->get_settings_for_display();

		return [
			'show_organizer_header'         => tribe_is_truthy( $settings['show_organizer_header'] ?? true ),
			'show_organizer_name'           => tribe_is_truthy( $settings['show_organizer_name'] ?? true ),
			'show_organizer_phone'          => tribe_is_truthy( $settings['show_organizer_phone'] ?? true ),
			'link_organizer_phone'          => tribe_is_truthy( $settings['link_organizer_phone'] ?? true ),
			'show_organizer_email'          => tribe_is_truthy( $settings['show_organizer_email'] ?? true ),
			'link_organizer_email'          => tribe_is_truthy( $settings['link_organizer_email'] ?? true ),
			'show_organizer_website'        => tribe_is_truthy( $settings['show_organizer_website'] ?? true ),
			'show_organizer_phone_header'   => tribe_is_truthy( $settings['show_organizer_phone_header'] ?? true ),
			'show_organizer_email_header'   => tribe_is_truthy( $settings['show_organizer_email_header'] ?? true ),
			'show_organizer_website_header' => tribe_is_truthy( $settings['show_organizer_website_header'] ?? true ),
			'organizer_header_tag'          => $settings['organizer_header_tag'] ?? 'h2',
			'organizer_name_tag'            => $settings['organizer_name_tag'] ?? 'h3',
			'organizer_phone_header_tag'    => $settings['organizer_phone_header_tag'] ?? 'h4',
			'organizer_email_header_tag'    => $settings['organizer_email_header_tag'] ?? 'h4',
			'organizer_website_header_tag'  => $settings['organizer_website_header_tag'] ?? 'h4',
			'organizer_email_header_text'   => $this->get_email_header_text(),
			'organizer_phone_header_text'   => $this->get_phone_header_text(),
			'organizer_website_header_text' => $this->get_website_header_text(),
			'multiple'                      => $this->has_multiple_organizers(),
			'settings'                      => $settings,
			'event_id'                      => $event_id,
			'organizers'                    => $this->get_organizer_data(),
		];
	}

	/**
	 * Get the template args for the widget preview.
	 *
	 * @since 6.4.0
	 *
	 * @return array The template args for the preview.
	 */
	protected function preview_args(): array {
		$id   = $this->get_event_id();
		$args = $this->template_args();

		if ( tribe_is_event( $id ) ) {
			return $args;
		}

		if ( ! empty( $args['organizers'] ) ) {
			return $args;
		}

		$args['organizers'][0] = $this->get_mock_organizer();

		return $args;
	}

	/**
	 * Get the mock organizer data for the widget preview.
	 *
	 * @since 6.4.0
	 *
	 * @return array The mock organizer data.
	 */
	protected function get_mock_organizer() {
		$phone = '555-555-5555';
		return [
			'id'         => 1,
			'name'       => _x( 'John Doe', 'Placeholder name for widget preview', 'the-events-calendar' ),
			'link'       => '#',
			'phone'      => $phone,
			'phone_link' => $this->format_phone_link( $phone ),
			'website'    => '<a href="http://theeventscaledndar.com" target="_self" rel="external">View Organizer Website</a>',
			'email'      => 'info@theeventscalendar.com',
		];
	}

	/**
	 * Get the organizer data for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return array The organizer data.
	 */
	protected function get_organizer_data(): array {
		$organizers    = [];
		$settings      = $this->get_settings_for_display();
		$event_id      = $this->get_event_id();
		$organizer_ids = array_filter( tribe_get_organizer_ids( $event_id ) );
		$target        = $settings['organizer_website_link_target'] ?? '_self';

		foreach ( $organizer_ids as $organizer_id ) {
			$phone                       = tribe_get_organizer_phone( $organizer_id );
			$organizers[ $organizer_id ] = [
				'id'         => $organizer_id,
				'name'       => tribe_get_organizer( $organizer_id ),
				'phone'      => $phone,
				'phone_link' => tribe_is_truthy( $settings['link_organizer_phone'] ?? false ) ? $this->format_phone_link( $phone ) : false,
				'website'    => tribe_get_organizer_website_link( $organizer_id, null, $target ),
				'email'      => tribe_get_organizer_email( $organizer_id ),
			];
		}

		return $organizers;
	}

	/**
	 * Format a phone number for use in a tel link.
	 *
	 * @since 6.4.0
	 *
	 * @param string $phone The phone number to format.
	 */
	protected function format_phone_link( $phone ): string {
		// For a dial link we remove spaces, and replace 'ext' or 'x' with 'p' to pause before dialing the extension.
		return 'tel:' . str_ireplace( [ 'ext', 'x', ' ' ], [ 'p', 'p', '' ], $phone );
	}

	/**
	 * Get the email header text for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string The email header text.
	 */
	protected function get_email_header_text(): string {
		$header_text = _x(
			'Email:',
			'The header string for the Elementor event organizer widget email section.',
			'the-events-calendar'
		);

		/**
		 * Filters the email header text for the event organizer widget.
		 *
		 * @since 6.4.0
		 *
		 * @param string $header_text The header text.
		 * @param Event_Organizer $this The event organizer widget instance.
		 *
		 * @return string The filtered header text.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_widget_email_header_text', $header_text, $this );
	}

	/**
	 * Get the phone header text for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string The phone header text.
	 */
	protected function get_phone_header_text(): string {
		$header_text = _x(
			'Phone:',
			'The header string for the Elementor event organizer widget phone section.',
			'the-events-calendar'
		);

		/**
		 * Filters the phone header text for the event organizer widget.
		 *
		 * @since 6.4.0
		 *
		 * @param string $header_text The header text.
		 * @param Event_Organizer $this The event organizer widget instance.
		 *
		 * @return string The filtered header text.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_widget_phone_header_text', $header_text, $this );
	}

	/**
	 * Get the website header text for the widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string The website header text.
	 */
	protected function get_website_header_text(): string {
		$header_text = _x(
			'Website:',
			'The header string for the Elementor event organizer widget website section.',
			'the-events-calendar'
		);

		/**
		 * Filters the website header text for the event organizer widget.
		 *
		 * @since 6.4.0
		 *
		 * @param string $header_text The header text.
		 * @param Event_Organizer $this The event organizer widget instance.
		 *
		 * @return string The filtered header text.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_widget_website_header_text', $header_text, $this );
	}

	/**
	 * Get the classes for the event organizer widget.
	 *
	 * @since 6.4.0
	 *
	 * @return string The classes for the event organizer widget.
	 */
	public function get_header_class(): string {
		$class = $this->get_widget_class() . '-header';

		/**
		 * Filters the classes for the event organizer widget header.
		 *
		 * @since 6.4.0
		 *
		 * @param string          $class The widget header class.
		 * @param Event_Organizer $this  The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_header_class', $class, $this );
	}

	/**
	 * Get the base class for the event organizer name list.
	 *
	 * @since 6.4.0
	 *
	 * @return string The name class.
	 */
	public function get_name_base_class(): string {
		$class = $this->get_widget_class() . '-name';

		/**
		 * Filters the base class for the event organizer name section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The name base class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_name_class', $class, $this );
	}

	/**
	 * Get the wrapper class for the event organizer name.
	 *
	 * @since 6.4.0
	 *
	 * @return string The name class.
	 */
	public function get_name_wrapper_class(): string {
		$class = $this->get_widget_class() . '-name-wrapper';

		/**
		 * Filters the wrapper class for the event organizer name section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The name wrapper class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_name_wrapper_class', $class, $this );
	}

	/**
	 * Get the base class for the event organizer phone section.
	 *
	 * @since 6.4.0
	 *
	 * @return string The phone class.
	 */
	public function get_phone_base_class(): string {
		$class = $this->get_widget_class() . '-phone';

		/**
		 * Filters the base class for the event organizer phone section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The phone base class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_phone_class', $class, $this );
	}

	/**
	 * Get the wrapper class for the event organizer phone.
	 *
	 * @since 6.4.0
	 *
	 * @return string The phone class.
	 */
	public function get_phone_wrapper_class(): string {
		$class = $this->get_phone_base_class() . '-wrapper';

		/**
		 * Filters the wrapper class for the event organizer phone section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The phone wrapper class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_phone_wrapper_class', $class, $this );
	}

	/**
	 * Get the header class for the event organizer phone section.
	 *
	 * @since 6.4.0
	 *
	 * @return string The phone header class.
	 */
	public function get_phone_header_class(): string {
		$class = $this->get_phone_base_class() . '-header';

		/**
		 * Filters the header class for the event organizer phone section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The phone header class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_phone_header_class', $class, $this );
	}

	/**
	 * Get the base class for the event organizer email section.
	 *
	 * @since 6.4.0
	 *
	 * @return array The address header classes.
	 */
	public function get_email_base_class(): string {
		$class = $this->get_widget_class() . '-email';

		/**
		 * Filters the base class for the event organizer email section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The email base class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_email_class', $class, $this );
	}

	/**
	 * Get the wrapper class for the event organizer email.
	 *
	 * @since 6.4.0
	 *
	 * @return string The email class.
	 */
	public function get_email_wrapper_class(): string {
		$class = $this->get_email_base_class() . '-wrapper';

		/**
		 * Filters the wrapper class for the event organizer email section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The email wrapper class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_email_wrapper_class', $class, $this );
	}

	/**
	 * Get the header class for the event organizer email section.
	 *
	 * @since 6.4.0
	 *
	 * @return string The email class.
	 */
	public function get_email_header_class(): string {
		$class = $this->get_email_base_class() . '-header';

		/**
		 * Filters the header class for the event organizer email section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The email header class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_email_header_class', $class, $this );
	}

	/**
	 * Get the base class for the event organizer website section.
	 *
	 * @since 6.4.0
	 *
	 * @return array The address header classes.
	 */
	public function get_website_base_class(): string {
		$class = $this->get_widget_class() . '-website';

		/**
		 * Filters the base class for the event organizer website section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The website base class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_website_class', $class, $this );
	}

	/**
	 * Get the wrapper class for the event organizer website.
	 *
	 * @since 6.4.0
	 *
	 * @return string The website class.
	 */
	public function get_website_wrapper_class(): string {
		$class = $this->get_website_base_class() . '-wrapper';

		/**
		 * Filters the wrapper class for the event organizer website section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The website wrapper class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_website_wrapper_class', $class, $this );
	}

	/**
	 * Get the header class for the event organizer website section.
	 *
	 * @since 6.4.0
	 *
	 * @return string The website class.
	 */
	public function get_website_header_class(): string {
		$class = $this->get_website_base_class() . '-header';

		/**
		 * Filters the header class for the event organizer website section header.
		 *
		 * @since 6.4.0
		 *
		 * @param string $class The website header class.
		 * @param Event_Organizer $this The event organizer widget instance.
		 */
		return apply_filters( 'tec_events_elementor_event_organizer_website_header_class', $class, $this );
	}

	/**
	 * Get the class(es) for the event organizer container.
	 *
	 * @since 6.4.0
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
	 * @since 6.4.0
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
	 * @since 6.4.0
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
	 * @since 6.4.0
	 */
	protected function content_panel() {
		$this->content_options();

		$this->organizer_name_content_options();

		// Only show the following options if the event has a single organizer.
		if ( ! $this->has_multiple_organizers() ) {
			$this->organizer_phone_content_options();

			$this->organizer_website_content_options();

			$this->organizer_email_content_options();
		}

		$this->add_event_query_section();
	}

	/**
	 * Add controls for text content of the event organizer.
	 *
	 * @since 6.4.0
	 */
	protected function content_options() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__( 'Event Organizer', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'      => 'show_organizer_header',
				'label'   => esc_html__( 'Show Widget Header', 'the-events-calendar' ),
				'default' => 'no',
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'organizer_header_tag',
				'label'     => esc_html__( 'Widget Header HTML Tag', 'the-events-calendar' ),
				'default'   => 'h2',
				'condition' => [ 'show_organizer_header' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add styling controls for the widget.
	 *
	 * @since 6.4.0
	 */
	protected function style_panel() {
		$this->style_organizer_header();

		$this->style_organizer_name();

		$this->style_organizer_phone();

		$this->style_organizer_email();

		$this->style_organizer_website();
	}

	/**
	 * Add controls for text content of the event organizer name.
	 *
	 * @since 6.4.0
	 */
	protected function organizer_name_content_options() {
		$this->start_controls_section(
			'organizer_name_content_options',
			[ 'label' => esc_html__( 'Name', 'the-events-calendar' ) ]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_organizer_name',
				'label' => esc_html__( 'Show Name', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'organizer_name_tag',
				'label'     => esc_html__( 'Name HTML Tag', 'the-events-calendar' ),
				'default'   => 'h2',
				'condition' => [ 'show_organizer_name' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event organizer phone.
	 *
	 * @since 6.4.0
	 */
	protected function organizer_phone_content_options() {
		$this->start_controls_section(
			'organizer_phone_content_options',
			[
				'label' => esc_html__( 'Phone', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_organizer_phone',
				'label' => esc_html__( 'Show Phone', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'        => 'show_organizer_phone_header',
				'label'     => esc_html__( 'Show Header', 'the-events-calendar' ),
				'default'   => 'no',
				'condition' => [ 'show_organizer_phone' => 'yes' ],
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'organizer_phone_header_tag',
				'label'     => esc_html__( 'Header HTML Tag', 'the-events-calendar' ),
				'default'   => 'h4',
				'condition' => [ 'show_organizer_phone_header' => 'yes' ],
			]
		);

		// Link Organizer Name control.
		$this->add_control(
			'link_organizer_phone',
			[
				'label'       => esc_html__( 'Link organizer phone.', 'the-events-calendar' ),
				'description' => esc_html__( 'Make organizer phone number a callable link.', 'the-events-calendar' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Yes', 'the-events-calendar' ),
				'label_off'   => esc_html__( 'No', 'the-events-calendar' ),
				'default'     => 'yes',
				'condition'   => [ 'show_organizer_phone' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event organizer email.
	 *
	 * @since 6.4.0
	 */
	protected function organizer_email_content_options() {
		$this->start_controls_section(
			'organizer_email_content_options',
			[
				'label' => esc_html__( 'Email', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_organizer_email',
				'label' => esc_html__( 'Show Email', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'        => 'show_organizer_email_header',
				'label'     => esc_html__( 'Show Header', 'the-events-calendar' ),
				'condition' => [ 'show_organizer_email' => 'yes' ],
				'default'   => 'no',
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'organizer_email_header_tag',
				'label'     => esc_html__( 'Header HTML Tag', 'the-events-calendar' ),
				'default'   => 'h4',
				'condition' => [ 'show_organizer_email_header' => 'yes' ],
			]
		);

		// Link Organizer Name control.
		$this->add_control(
			'link_organizer_email',
			[
				'label'       => esc_html__( 'Link organizer email.', 'the-events-calendar' ),
				'description' => esc_html__( 'Make organizer email a mailto link.', 'the-events-calendar' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Yes', 'the-events-calendar' ),
				'label_off'   => esc_html__( 'No', 'the-events-calendar' ),
				'default'     => 'yes',
				'condition'   => [ 'show_organizer_email' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Add controls for text content of the event organizer website.
	 *
	 * @since 6.4.0
	 */
	protected function organizer_website_content_options() {
		$this->start_controls_section(
			'organizer_website_content_options',
			[
				'label' => esc_html__( 'Website', 'the-events-calendar' ),
			]
		);

		// Show Organizer Header control.
		$this->add_shared_control(
			'show',
			[
				'id'    => 'show_organizer_website',
				'label' => esc_html__( 'Show Website', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'show',
			[
				'id'        => 'show_organizer_website_header',
				'label'     => esc_html__( 'Show Header', 'the-events-calendar' ),
				'condition' => [ 'show_organizer_website' => 'yes' ],
				'default'   => 'no',
			]
		);

		$this->add_shared_control(
			'tag',
			[
				'id'        => 'organizer_website_header_tag',
				'label'     => esc_html__( 'Header HTML Tag', 'the-events-calendar' ),
				'default'   => 'h4',
				'condition' => [ 'show_organizer_website_header' => 'yes' ],
			]
		);

		$this->add_shared_control( 'link_target', [ 'prefix' => 'organizer_website' ] );

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the organizer label.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	protected function style_organizer_header() {
		$this->start_controls_section(
			'organizer_header_styling_section',
			[
				'label'     => esc_html__( 'Header', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_organizer_header' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'organizer_header',
				'selector' => '{{WRAPPER}} .' . $this->get_header_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'organizer_header_alignment',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_header_class() ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the organizer name.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	protected function style_organizer_name() {
		$this->start_controls_section(
			'organizer_name_styling',
			[
				'label'     => esc_html__( 'Name', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_organizer_name' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'organizer_name',
				'selector' => '{{WRAPPER}} .' . $this->get_name_base_class() . ', {{WRAPPER}} .' . $this->get_name_base_class() . ' a',
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'organizer_name_alignment',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_name_base_class() ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the organizer phone.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	protected function style_organizer_phone() {
		$this->start_controls_section(
			'organizer_phone_styling',
			[
				'label'     => esc_html__( 'Phone', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_organizer_phone' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix' => 'phone_header',
				'label'  => esc_html__( 'Phone Header', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'phone_header',
				'selector' => '{{WRAPPER}} .' . $this->get_phone_header_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'phone_header_alignment',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_phone_header_class() ],
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix'    => 'phone_text',
				'label'     => esc_html__( 'Phone Text', 'the-events-calendar' ),
				'separator' => 'before',
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'phone_text',
				'selector' => '{{WRAPPER}} .' . $this->get_phone_base_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'phone_text_alignment',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_phone_base_class() ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the organizer email.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	protected function style_organizer_email() {
		$this->start_controls_section(
			'organizer_email_styling',
			[
				'label'     => esc_html__( 'Email', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_organizer_email' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix' => 'email_header',
				'label'  => esc_html__( 'Email Header', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'email_header',
				'selector' => '{{WRAPPER}} .' . $this->get_email_header_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'email_header_alignment',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_email_header_class() ],
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix'    => 'email_text',
				'label'     => esc_html__( 'Email Text', 'the-events-calendar' ),
				'separator' => 'before',
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'email_text',
				'selector' => '{{WRAPPER}}  .' . $this->get_email_base_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'email_text_alignment',
				'selectors' => [ '{{WRAPPER}} .a' . $this->get_email_base_class() ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the styling controls for the organizer website.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	protected function style_organizer_website() {
		$this->start_controls_section(
			'organizer_website_styling',
			[
				'label'     => esc_html__( 'Website', 'the-events-calendar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_organizer_website' => 'yes',
				],
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix' => 'website_header',
				'label'  => esc_html__( 'Website Header', 'the-events-calendar' ),
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'website_header',
				'selector' => '{{WRAPPER}} .' . $this->get_website_header_class(),
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'website_header_alignment',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_website_header_class() ],
			]
		);

		$this->add_shared_control(
			'subheader',
			[
				'prefix'    => 'website_url',
				'label'     => esc_html__( 'Website Url', 'the-events-calendar' ),
				'separator' => 'before',
			]
		);

		$this->add_shared_control(
			'typography',
			[
				'prefix'   => 'website_url',
				'selector' => '{{WRAPPER}} .' . $this->get_website_base_class() . ' a',
			]
		);

		$this->add_shared_control(
			'alignment',
			[
				'id'        => 'website_url_alignment',
				'selectors' => [ '{{WRAPPER}} .' . $this->get_website_base_class() ],
			]
		);

		$this->end_controls_section();
	}
}
