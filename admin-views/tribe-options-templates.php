<?php

$template_options = array(
	'' => __('Default Events Template', 'tribe-events-calendar' ),
	'default' => __('Default Page Template', 'tribe-events-calendar' ),
);
$templates = get_page_templates();
ksort( $templates );
foreach (array_keys( $templates ) as $template ) {
	$template_options[$templates[$template]] = $template;
}

$templatesTab = array(
	'priority' => 20,
	'fields' => array(
		'template-heading' => array(
			'type' => 'heading',
			'label' => __('Template Settings', 'tribe-events-calendar'),
		),
		'tribeEventsTemplate' => array(
			'type' => 'dropdown_chosen',
		 	'label' => __('Events Template', 'tribe-events-calendar'),
			'tooltip' => __('Choose a page template to control the look and feel of your calendar.', 'tribe-events-calendar'),
			'validation_type' => 'options',
			'size' => 'large',
			'default' => 'default',
			'options' => $template_options,
		),
		'tribeEventsBeforeHTML' => array(
			'type' => 'textarea',
		 	'label' => __('Add HTML before calendar', 'tribe-events-calendar'),
			'tooltip' => __('Some themes may require that you add extra divs before the calendar list to help with styling.<br>This is displayed directly after the header.', 'tribe-events-calendar').' '.__('You may use (x)HTML.', 'tribe-events-calendar'),
			'validation_type' => 'html',
			'size' => 'large',
		),
		'tribeEventsAfterHTML' => array(
			'type' => 'textarea',
		 	'label' => __('Add HTML after calendar', 'tribe-events-calendar'),
			'tooltip' => __('Some themes may require that you add extra divs after the calendar list to help with styling.<br>This is displayed directly above the footer.', 'tribe-events-calendar').' '.__('You may use (x)HTML.', 'tribe-events-calendar'),
			'validation_type' => 'html',
			'size' => 'large',
		),
	),
);