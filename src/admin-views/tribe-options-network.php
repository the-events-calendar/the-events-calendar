<?php
$allTabs = apply_filters( 'tribe_settings_all_tabs', array() );

$networkTab = array(
	'priority'      => 10,
	'network_admin' => true,
	'fields'        => apply_filters(
		'tribe_network_settings_tab_fields', array(
			'info-start'           => array(
				'type' => 'html',
				'html' => '<div id="modern-tribe-info">',
			),
			'info-box-title'       => array(
				'type' => 'html',
				'html' => '<h2>' . __( 'Network Settings', 'tribe-events-calendar' ) . '</h2>',
			),
			'info-box-description' => array(
				'type' => 'html',
				'html' => '<p>' . __( 'This is where all of the global network settings for Modern Tribe\'s The Events Calendar can be modified.', 'tribe-events-calendar' ) . '</p>',
			),
			'info-end'             => array(
				'type' => 'html',
				'html' => '</div>',
			),
			'hideSettingsTabs'     => array(
				'type'            => 'checkbox_list',
				'label'           => __( 'Hide the following settings tabs on every site:', 'tribe-events-calendar' ),
				'default'         => false,
				'options'         => $allTabs,
				'validation_type' => 'options_multi',
				'can_be_empty'    => true,
			),
		)
	)
);
