<?php
/**
 * Provides shared control methods for Elementor widgets using the TEC templating engine.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets\Traits;
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets\Traits;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Typography;
use WP_Error;

/**
 * Trait With_Shared_Controls
 *
 * @since 6.4.0
 *
 * @package TEC\Events_Pro\Integrations\Plugins\Elementor\Widgets\Traits;
 */
trait With_Shared_Controls {
	/**
	 * Add a shared control.
	 *
	 * @since 6.4.0
	 *
	 * @param string $control The control to add.
	 * @param array  $args    Additional arguments for the control.
	 */
	protected function add_shared_control( $control, $args = [] ): void {
		call_user_func(
			[ $this, $control ],
			$args
		);
	}

	/**
	 * Add control for HTML tag.
	 *
	 * @since 6.4.0
	 *
	 * @param array $args Additional arguments for the control.
	 *                    Requires:
	 *                        - string id: The control ID.
	 *                    Accepts:
	 *                        - string label:    The label for the control.
	 *                        - array options:   The alignment options.
	 *                        - array condition: The conditions for showing the control.
	 */
	protected function tag( $args = [] ): void {
		$check = $this->check_required_args( $args, 'id' );

		if ( is_wp_error( $check ) ) {
			echo esc_html( $check->get_error_message() );
			return;
		}

		$this->add_control(
			$args['id'],
			[
				'label'     => $args['label'] ?? esc_html__( 'HTML Tag', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $args['options'] ?? [
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
				'default'   => $args['default'] ?? 'h3',
				'condition' => $args['condition'] ?? [],
			]
		);
	}

	/**
	 * Add a control for text alignment.
	 *
	 * @since 6.4.0
	 *
	 * @param array $args      Additional arguments for the control.
	 *                         Requires:
	 *                             - string id: The control ID.
	 *                             - array  selectors: The selectors to apply the alignment to.
	 *                         Accepts:
	 *                             - string label:    The label for the control.
	 *                             - array  options:   The alignment options.
	 *                             - string default:  The default alignment.
	 *                             - array  condition: The conditions for showing the control.
	 */
	protected function alignment( $args = [] ): void {
		$check = $this->check_required_args( $args, [ 'id', 'selectors' ] );

		if ( is_wp_error( $check ) ) {
			echo esc_html( $check->get_error_message() );
			return;
		}

		$updated = [];

		foreach ( (array) $args['selectors'] as $selector ) {
			$updated[ $selector ] = 'text-align: {{VALUE}};';
		}
		$this->add_responsive_control(
			$args['id'],
			[
				'label'     => $args['label'] ?? esc_html__( 'Alignment', 'the-events-calendar' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => $args['options'] ?? [
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
				'default'   => $args['default'] ?? '',
				'selectors' => (array) $updated,
				'condition' => $args['condition'] ?? [],
			]
		);
	}

	/**
	 * Add a control for flex alignment. Mimics a text-align control but uses flexbox.
	 *
	 * @since 6.4.0
	 *
	 * @param array $args      Additional arguments for the control.
	 *                         Requires:
	 *                             - string id: The control ID.
	 *                             - array  selectors: The selectors to apply the alignment to.
	 *                         Accepts:
	 *                             - string label:    The label for the control.
	 *                             - array  options:   The alignment options.
	 *                             - string default:  The default alignment.
	 *                             - array  condition: The conditions for showing the control.
	 */
	protected function flex_alignment( $args = [] ): void {
		$check = $this->check_required_args( $args, [ 'id', 'selectors' ] );

		if ( is_wp_error( $check ) ) {
			echo esc_html( $check->get_error_message() );
			return;
		}

		$updated = [];

		foreach ( (array) $args['selectors'] as $selector ) {
			$updated[ $selector ] = 'justify-content: {{VALUE}};';
		}
		$this->add_responsive_control(
			$args['id'],
			[
				'label'     => $args['label'] ?? esc_html__( 'Alignment', 'the-events-calendar' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => $args['options'] ?? [
					'left'         => [
						'title' => esc_html__( 'Left', 'the-events-calendar' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'       => [
						'title' => esc_html__( 'Center', 'the-events-calendar' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'        => [
						'title' => esc_html__( 'Right', 'the-events-calendar' ),
						'icon'  => 'eicon-text-align-right',
					],
					'space-evenly' => [
						'title' => esc_html__( 'Justified', 'the-events-calendar' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'   => $args['default'] ?? '',
				'selectors' => (array) $updated,
				'condition' => $args['condition'] ?? [],
			]
		);
	}

	/**
	 * Add control for showing an element.
	 *
	 * @since 6.4.0
	 *
	 * @param array $args Additional arguments for the control.
	 *                        Requires:
	 *                            - string id: The control ID.
	 *                        Accepts:
	 *                            - string label:     The label for the control.
	 *                            - string label_on:  The label for the "on" state. Defaults to "Show".
	 *                            - string label_off: The label for the "off" state. Defaults to "Hide".
	 *                            - string default:   The default state ("yes" or "no", defaults to "yes").
	 *                            - string description The description for the control.
	 */
	protected function show( $args = [] ): void {
		$check = $this->check_required_args( $args, 'id' );

		if ( is_wp_error( $check ) ) {
			echo esc_html( $check->get_error_message() );
			return;
		}


		$this->add_control(
			$args['id'],
			[
				'label'       => $args['label'] ?? esc_html__( 'Show Element', 'the-events-calendar' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => $args['label_on'] ?? esc_html__( 'Show', 'the-events-calendar' ),
				'label_off'   => $args['label_off'] ?? esc_html__( 'Hide', 'the-events-calendar' ),
				'default'     => $args['default'] ?? 'yes',
				'description' => $args['description'] ?? '',
			]
		);
	}

	/**
	 * Add controls for text styling.
	 * Includes text color, typography, text stroke, text shadow, and blend mode controls.
	 *
	 * @since 6.4.0
	 *
	 * @param array $args Additional arguments for the control.
	 *                        Requires:
	 *                            - string prefix: The control ID prefix.
	 *                        Accepts:
	 *                            - string selector  The CSS selector used for the controls. Defaults to "{{WRAPPER}}"
	 *                            - string separator The separator for the controls.
	 *                            - array global     The global typography settings.
	 *                                                  Accepted values: 'primary', 'secondary', 'text', or 'accent'. Defaults to "primary'.
	 */
	protected function typography( $args = [] ) {
		$check = $this->check_required_args( $args, 'prefix' );

		if ( is_wp_error( $check ) ) {
			echo esc_html( $check->get_error_message() );
			return;
		}

		$selector          = strval( $args['selector'] ?? '{{WRAPPER}}' );
		$global_color      = empty( $args['global'] ) ? false : 'COLOR_' . strtoupper( $args['global'] ?? 'PRIMARY' );
		$global_typography = empty( $args['global'] ) ? false : 'TYPOGRAPHY_' . strtoupper( $args['global'] ?? 'PRIMARY' );

		$color_opts = [
			'label'     => esc_html__( 'Text Color', 'the-events-calendar' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => $args['separator'] ?? 'none',
			'selectors' => [ $selector => 'color: {{VALUE}};' ],
		];

		if ( $global_color ) {
			$color_opts['global'] = [
				'default' => constant( 'Elementor\Core\Kits\Documents\Tabs\Global_Colors::' . $global_color ),
			];
		}


		$this->add_control(
			$args['prefix'] . '_color',
			$color_opts
		);

		$typography_opts = [
			'name'      => $args['prefix'] . '_typography',
			'selector'  => $selector,
			'separator' => $args['separator'] ?? 'none',
		];

		if ( $global_typography ) {
			$typography_opts['global'] = [
				'default' => constant( 'Elementor\Core\Kits\Documents\Tabs\Global_Typography::' . $global_typography ),
			];
		}

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			$typography_opts
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'      => $args['prefix'] . '_text_stroke',
				'selector'  => $selector,
				'separator' => $args['separator'] ?? 'none',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'      => $args['prefix'] . 'text_shadow',
				'selector'  => $selector,
				'separator' => $args['separator'] ?? 'none',
			]
		);

		$this->add_control(
			$args['prefix'] . '_blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'the-events-calendar' ),
				'type'      => Controls_Manager::SELECT,
				'separator' => $args['separator'] ?? 'none',
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
				'selectors' => [ '{{WRAPPER}} .' . $selector => 'mix-blend-mode: {{VALUE}};' ],
				'condition' => $args['condition'] ?? '',
			]
		);
	}

	/**
	 * Add control for link target.
	 *
	 * @since 6.4.0
	 *
	 * @param array $args Additional arguments for the control.
	 *                        Requires:
	 *                            - string prefix: The control ID prefix.
	 *                        Accepts:
	 *                            - string label       The label for the control.
	 *                            - string description The description for the control.
	 */
	protected function link_target( $args = [] ): void {
		$check = $this->check_required_args( $args, 'prefix' );

		if ( is_wp_error( $check ) ) {
			echo esc_html( $check->get_error_message() );
			return;
		}

		$this->add_control(
			$args['prefix'] . '_link_target',
			[
				'label'       => $args['label'] ?? esc_html__( 'Link Target', 'the-events-calendar' ),
				'description' => $args['description'] ?? esc_html__( 'Choose whether to open the link in the same window or a new window.', 'the-events-calendar' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '_self',
				'options'     => [
					'_self'  => 'same window',
					'_blank' => 'new window',
				],
			]
		);
	}

	/**
	 * Add control for phone number.
	 *
	 * @since 6.4.0
	 *
	 * @param array $args Additional arguments for the control.
	 *                        Requires:
	 *                            - string prefix: The control ID prefix.
	 *                        Accepts:
	 *                            - string label The label for the control.
	 *                            - string separator The separator for the controls.
	 *                            - array condition The conditions for showing the control.
	 */
	protected function subheader( $args = [] ) {
		$check = $this->check_required_args( $args, 'prefix' );

		if ( is_wp_error( $check ) ) {
			echo esc_html( $check->get_error_message() );
			return;
		}

		$this->add_control(
			$args['prefix'] . '_title',
			[
				'label'     => $args['label'],
				'type'      => Controls_Manager::HEADING,
				'separator' => $args['separator'] ?? 'none',
				'condition' => $args['condition'] ?? '',
			]
		);
	}

	/**
	 * Allows above functions to check their required args and throw an error if they are missing.
	 *
	 * @since 6.4.0
	 *
	 * @param array        $args     The arguments to check.
	 * @param string|array $required The required arguments. Converted to an array.
	 *
	 * @return ?WP_Error         True if all required args are present, WP_Error if not.
	 */
	private function check_required_args( $args, $required ): ?WP_Error {
		$required = (array) $required;
		foreach ( $required as $req ) {
			if ( ! isset( $args[ $req ] ) ) {
				return new WP_Error(
					'broke',
					sprintf(
						/* translators: %s: The missing argument */
						__( 'Missing required argument: %s', 'the-events-calendar' ),
						$req
					)
				);
			}
		}

		return null;
	}
}
