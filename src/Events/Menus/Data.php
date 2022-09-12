<?php
/**
 * TEC Settings
 *
 * @since TBD
 *
 * @see https://github.com/gilbitron/WordPress-Settings-Framework
 * @package TEC\Events\Menuss
 */

/**
 * Define our settings.
 */

add_filter(
	'wpsf_register_settings_tec',
	function( $tec_settings ) {
		// Tabs. These control the actual _tabs_ - not their content!
		$tec_settings['tabs'] = array(
			array(
				'id'    => 'tab_1',
				'title' => esc_html__( 'Tab 1', 'text-domain' ),
			),
			array(
				'id'    => 'tab_2',
				'title' => esc_html__( 'Tab 2', 'text-domain' ),
			),
			array(
				'id'                => 'tab_3',
				'title'             => esc_html__( 'Tab 3', 'text-domain' ),
				'tab_control_group' => 'tab-control',
				'show_if'           => array( // Field will only show if the control `tab_2_section_2_tab-control` is set to true.
					array(
						'field' => 'tab_2_section_3_tab-control',
						'value' => array( '1' ),
					),
				),
			),
		);

		// Settings.
		$tec_settings['sections'] = array(
			array(
				'tab_id'        => 'tab_1',
				'section_id'    => 'section_1',
				'section_title' => 'Section 1',
				'section_order' => 10,
				'fields'        => array(
					array(
						'id'      => 'text-1',
						'title'   => 'Text',
						'desc'    => 'This is a description.',
						'type'    => 'text',
						'default' => 'This is default',
					),
				),
			),
			array(
				'tab_id'        => 'tab_1',
				'section_id'    => 'section_2',
				'section_title' => 'Section 2',
				'section_order' => 10,
				'fields'        => array(
					array(
						'id'      => 'text-2',
						'title'   => 'Text',
						'desc'    => 'This is a description.',
						'type'    => 'text',
						'default' => 'This is default',
					),
				),
			),
			array(
				'tab_id'        => 'tab_2',
				'section_id'    => 'section_3',
				'section_title' => 'Section 3',
				'section_order' => 10,
				'fields'        => array(
					array(
						'id'      => 'text-3',
						'title'   => 'Text',
						'desc'    => 'This is a description.',
						'type'    => 'text',
						'default' => 'This is default',
					),
					array(
						'id'      => 'tab-control',
						'title'   => 'Will show Tab 3 if toggled',
						'type'    => 'toggle',
						'default' => false,
					),
				),
			),
			array(
				'tab_id'        => 'tab_3',
				'section_id'    => 'section_4',
				'section_title' => 'Section 4',
				'section_order' => 10,
				'fields'        => array(
					array(
						'id'      => 'text-4',
						'title'   => 'Text',
						'desc'    => 'This is a description.',
						'type'    => 'text',
						'default' => 'This is default',
					),
					array(
						'id'       => 'complex-group-1',
						'title'    => 'Complex Show Hide 1',
						'subtitle' => 'Multiple controls can show or hide fields',
						'type'     => 'select',
						'choices'  => array(
							'option-1' => 'Option 1',
							'option-2' => 'Option 2',
							'option-3' => 'Option 3',
						),
						'default'  => 'text',
					),
					array(
						'id'       => 'complex-group-2',
						'title'    => 'Complex Show Hide 2',
						'subtitle' => 'Multiple controls can show or hide fields',
						'type'     => 'toggle',
						'default'  => false,
					),
					array(
						'id'       => 'complex-group-3',
						'title'    => 'Complex Show Hide 3',
						'subtitle' => 'Multiple controls can show or hide fields',
						'type'     => 'toggle',
						'default'  => false,
					),
					array(
						'id'       => 'complex-group-show',
						'title'    => 'Complex Show Example',
						'subtitle' => 'Will show if Control 1 is Option 1 or Option 2 AND Control 2 is True, OR if Control 3 is true',
						'type'     => 'select',
						'type'     => 'text',
						'default'  => 'This is default',
						'show_if'  => array(
							// An array here is an AND group.
							array(
								// Show if Control 1 is Option 1 OR Option 2.
								array(
									'field' => 'tab_3_section_4_complex-group-1',
									'value' => array( 'option-1', 'option-2' ),
								),
								// AND Control 2 is True.
								array(
									'field' => 'tab_3_section_4_complex-group-2',
									'value' => array( '1' ),
								),
							),
							// OR show if Control 3 is True.
							array(
								'field' => 'tab_3_section_4_complex-group-3',
								'value' => array( '1' ),
							),
						),
					),
					array(
						'id'       => 'complex-group-hide',
						'title'    => 'Complex Hide Example',
						'subtitle' => 'Will hide if Control 1 is Option 1 or Option 2 AND Control 2 is True, OR if Control 3 is true',
						'type'     => 'select',
						'type'     => 'text',
						'default'  => 'This is default',
						'hide_if'  => array(
							// An array here is an AND group.
							array(
								// Hide if Control 1 is Option 1 OR Option 2.
								array(
									'field' => 'tab_3_section_4_complex-group-1',
									'value' => array( 'option-1', 'option-2' ),
								),
								// AND Control 2 is True.
								array(
									'field' => 'tab_3_section_4_complex-group-2',
									'value' => array( '1' ),
								),
							),
							// OR hide if Control 3 is True.
							array(
								'field' => 'tab_3_section_4_complex-group-3',
								'value' => array( '1' ),
							),
						),
					),
				),
			),
		);

		return $tec_settings;
	}
);
