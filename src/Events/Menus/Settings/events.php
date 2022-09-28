<?php
/**
 * WordPress Settings Framework
 *
 * @link https://github.com/gilbitron/WordPress-Settings-Framework
 * @package wpsf
 */

/**
 * Define your settings
 *
 * The first parameter of this filter should be tec_register_settings_[options_group],
 * in this case "events".
 *
 * Your "options_group" is the second param you use when running new WordPressSettingsFramework()
 * from your init function. It's important as it differentiates your options from others.
 *
 * To use the tabbed example, simply change the second param in the filter below to 'tec_tabbed_settings'
 * and check out the tabbed settings function on line 156.
 */

add_filter( 'wpsf_register_settings_tec', 'tec_tabless_settings' );


/**
 * Tabless example.
 *
 * @param array $tec_settings Settings.
 */
function tec_tabless_settings( $tec_settings ) {
	// General Settings section.
	$tec_settings[] = array(
		'section_id'          => 'general',
		'section_title'       => 'General Settings',
		'section_description' => 'Some intro description about this section.',
		'section_order'       => 5,
		'fields'              => array(
			array(
				'id'          => 'text',
				'title'       => 'Text',
				'desc'        => 'This is a description.',
				'placeholder' => 'This is a placeholder.',
				'type'        => 'text',
				'default'     => 'This is default',
			),
			array(
				'id'      => 'number',
				'title'   => 'Number',
				'desc'    => 'This is a description.',
				'type'    => 'number',
				'default' => 10,
			),
			array(
				'id'         => 'time',
				'title'      => 'Time Picker',
				'desc'       => 'This is a description.',
				'type'       => 'time',
				'timepicker' => array(), // Array of timepicker options (http://fgelinas.com/code/timepicker).
			),
			array(
				'id'         => 'date',
				'title'      => 'Date Picker',
				'desc'       => 'This is a description.',
				'type'       => 'date',
				'datepicker' => array(), // Array of datepicker options (http://api.jqueryui.com/datepicker/).
			),
			array(
				'id'      => 'image_radio',
				'title'   => 'Select a radio',
				'desc'    => 'This is a description.',
				'type'    => 'image_radio',
				'choices' => array(
					'choice-1' => array(
						'text'  => 'Choice 1',
						'image' => 'https://picsum.photos/100',
					),
					'choice-2' => array(
						'text'  => 'Choice 2',
						'image' => 'https://picsum.photos/100',
					),
				),
			),
			array(
				'id'      => 'image_checkboxes',
				'title'   => 'Select a Checkbox',
				'desc'    => 'This is a description.',
				'type'    => 'image_checkboxes',
				'choices' => array(
					'choice-1' => array(
						'text'  => 'Choice 1',
						'image' => 'https://picsum.photos/100',
					),
					'choice-2' => array(
						'text'  => 'Choice 2',
						'image' => 'https://picsum.photos/100',
					),
					'choice-2' => array(
						'text'  => 'Choice 3',
						'image' => 'https://picsum.photos/100',
					),
				),
			),
			array(
				'id'        => 'group',
				'title'     => 'Group',
				'desc'      => 'This is a description.',
				'type'      => 'group',
				'subfields' => array(
					// accepts most types of fields.
					array(
						'id'          => 'sub-text',
						'title'       => 'Sub Text',
						'desc'        => 'This is a description.',
						'placeholder' => 'This is a placeholder.',
						'type'        => 'text',
						'default'     => 'Sub text',
					),
				),
			),
			array(
				'id'          => 'password',
				'title'       => 'Password',
				'desc'        => 'This is a description.',
				'placeholder' => 'This is a placeholder.',
				'type'        => 'password',
				'default'     => 'Example',
			),
			array(
				'id'          => 'textarea',
				'title'       => 'Textarea',
				'desc'        => 'This is a description.',
				'placeholder' => 'This is a placeholder.',
				'type'        => 'textarea',
				'default'     => 'This is default',
			),
			array(
				'id'      => 'select',
				'title'   => 'Select',
				'desc'    => 'This is a description.',
				'type'    => 'select',
				'default' => 'green',
				'choices' => array(
					'red'   => 'Red',
					'green' => 'Green',
					'blue'  => 'Blue',
				),
			),
			array(
				'id'      => 'radio',
				'title'   => 'Radio',
				'desc'    => 'This is a description.',
				'type'    => 'radio',
				'default' => 'green',
				'choices' => array(
					'red'   => 'Red',
					'green' => 'Green',
					'blue'  => 'Blue',
				),
			),
			array(
				'id'      => 'checkbox',
				'title'   => 'Checkbox',
				'desc'    => 'This is a description.',
				'type'    => 'checkbox',
				'default' => 1,
			),
			array(
				'id'      => 'checkboxes',
				'title'   => 'Checkboxes',
				'desc'    => 'This is a description.',
				'type'    => 'checkboxes',
				'default' => array(
					'red',
					'blue',
				),
				'choices' => array(
					'red'   => 'Red',
					'green' => 'Green',
					'blue'  => 'Blue',
				),
			),
			array(
				'id'      => 'color',
				'title'   => 'Color',
				'desc'    => 'This is a description.',
				'type'    => 'color',
				'default' => '#ffffff',
			),
			array(
				'id'      => 'file',
				'title'   => 'File',
				'desc'    => 'This is a description.',
				'type'    => 'file',
				'default' => '',
			),
			array(
				'id'      => 'editor',
				'title'   => 'Editor',
				'desc'    => 'This is a description.',
				'type'    => 'editor',
				'default' => '',
			),
			array(
				'id'          => 'code_editor',
				'title'       => 'Code Editor',
				'desc'        => 'This is a description.',
				'placeholder' => 'This is a placeholder.',
				'type'        => 'code_editor',
				'mimetype'    => 'css',
				'default'     => 'This is default.',
			),
			array(
				'id'       => 'export',
				'title'    => 'Export settings',
				'subtitle' => 'Export settings.',
				'type'     => 'export',
			),
			array(
				'id'       => 'import',
				'title'    => 'Import Settings',
				'subtitle' => 'Import settings.',
				'type'     => 'import',
			),
		),
	);

	// More Settings section.
	$tec_settings[] = array(
		'section_id'    => 'more',
		'section_title' => 'More Settings',
		'section_order' => 10,
		'fields'        => array(
			array(
				'id'       => 'heading-tooltip-link',
				'title'    => 'Heading with tooltip',
				'subtitle' => 'Lorem ipsum dolor sit amet congue aliqua scelerisque dictumst ornare nullam suspendisse.',
				'desc'     => 'This is a description.',
				'type'     => 'text',
				'default'  => 'This is default',
				'link'     => array(
					'url'      => esc_url( 'https://google.com' ),
					'type'     => 'tooltip', // Can be 'tooltip' or 'link'. Default is 'tooltip'.
					'text'     => 'Learn More', // Default is 'Learn More'.
					'external' => true, // Default is `true`.
				),
			),
			array(
				'id'       => 'heading-subtitle-link',
				'title'    => 'Heading with link',
				'subtitle' => 'Lorem ipsum dolor sit amet congue aliqua scelerisque dictumst ornare nullam suspendisse.',
				'desc'     => 'This is a description.',
				'type'     => 'text',
				'default'  => 'This is default',
				'link'     => array(
					'url'      => esc_url( 'https://google.com' ),
					'type'     => 'link', // Can be 'tooltip' or 'link'. Default is 'tooltip'.
					'text'     => 'Learn More', // Default is 'Learn More'.
					'external' => true, // Default is `true`.
				),
			),
			array(
				'id'      => 'more-text',
				'title'   => 'More Text',
				'desc'    => 'This is a description.',
				'type'    => 'text',
				'default' => 'This is default',
			),
			array(
				'id'       => 'control-group',
				'title'    => 'Control Group',
				'subtitle' => 'Select option 1 or 2 to show and hide controls.',
				'type'     => 'select',
				'choices'  => array(
					'option-1' => 'Option 1',
					'option-2' => 'Option 2',
					'option-3' => 'Option 3',
				),
				'default'  => 'text',
			),
			array(
				'id'       => 'show-if-option-1',
				'title'    => 'Show if Option 1',
				'subtitle' => 'Will show if Option 1 is set.',
				'type'     => 'select',
				'type'     => 'text',
				'default'  => 'This is default',
				'show_if'  => array( // Field will only show, if the control `more_control-group` is set to Option 1.
					array(
						'field' => 'more_control-group',
						'value' => array( 'option-1' ),
					),
				),
			),
			array(
				'id'       => 'show-if-option-2',
				'title'    => 'Show if Option 2',
				'subtitle' => 'Will show if Option 2 is set.',
				'type'     => 'select',
				'type'     => 'text',
				'default'  => 'This is default',
				'show_if'  => array( // Field will only show, if the control `more_control-group` is set to Option 2.
					array(
						'field' => 'more_control-group',
						'value' => array( 'option-2' ),
					),
				),
			),
			array(
				'id'       => 'show-if-option-2-or-3',
				'title'    => 'Show if Option 2 or 3',
				'subtitle' => 'Will show if Option 2 or 3 is set.',
				'type'     => 'select',
				'type'     => 'text',
				'default'  => 'This is default',
				'show_if'  => array( // Field will only show, if the control `more_control-group` is set to Option 2 or Option 3.
					array(
						'field' => 'more_control-group',
						'value' => array( 'option-2', 'option-3' ),
					),
				),
			),
			array(
				'id'       => 'hide-if-option-1',
				'title'    => 'Hide if Option 1',
				'subtitle' => 'Will hide if Option 1 is set.',
				'type'     => 'select',
				'type'     => 'text',
				'default'  => 'This is default',
				'hide_if'  => array( // Field will only hide, if the control `more_control-group` is set to Option 1.
					array(
						'field' => 'more_control-group',
						'value' => array( 'option-1' ),
					),
				),
			),
			array(
				'id'      => 'section-control',
				'title'   => 'Will show Additional Settings Group if toggled',
				'flux-checkout',
				'type'    => 'toggle',
				'default' => false,
			),
		),
	);

	$tec_settings[] = array(
		'section_id'            => 'additional',
		'section_title'         => 'Additional Settings',
		'section_order'         => 10,
		'section_control_group' => 'section-control',
		'show_if'               => array( // Field will only show, if the control `more_section-control` is set to true.
			array(
				'field' => 'more_section-control',
				'value' => array( '1' ),
			),
		),
		'fields'                => array(
			array(
				'id'      => 'additional-text',
				'title'   => 'Additional Text',
				'desc'    => 'This is a description.',
				'type'    => 'text',
				'default' => 'This is default',
			),
			array(
				'id'      => 'additional-number',
				'title'   => 'Additional Number',
				'desc'    => 'This is a description.',
				'type'    => 'number',
				'default' => 10,
			),
		),
	);

	return $tec_settings;
}

/**
 * Tabbed example.
 *
 * @param array $tec_settings settings.
 */
function tec_tabbed_settings( $tec_settings ) {
	// Tabs.
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
