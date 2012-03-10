<?php

add_action( 'tribe_settings_tabs_after_help', 'tribe_test_tab' );
function tribe_test_tab() {
	$testTabArgs = array(
		'fields' => array(
			'heading_test' => array( 'type' => 'heading', 'label' => 'Heading Test' ),
			'html_test' => array( 'type' => 'html', 'label' => 'Html Test', 'html' => 'some random html'),
			'text_test' => array( 'type' => 'text', 'label' => 'This is a text field'),
			'radio_test' => array( 'type' => 'radio', 'label' => 'This is a radio field', 'options' => array('1' => 'one', '2' => 'two')),
			'checkbox_text' => array( 'type' => 'checkbox_bool', 'label' => 'This is a checkbox field'),
		),
	);
	new TribeSettingsTab( 'test', __('Test', 'tribe-events-calendar'), $testTabArgs );
}