<?php

$template_options = array(
	'' => __( 'Default Events Template', 'tribe-events-calendar' ),
	'default' => __( 'Default Page Template', 'tribe-events-calendar' ),
);
$templates = get_page_templates();
ksort( $templates );
foreach ( array_keys( $templates ) as $template ) {
	$template_options[$templates[$template]] = $template;
}

$templatesTab = array(
	'priority' => 20,
	'fields' => array(
		'info-start' => array(
			'type' => 'html',
			'html' => '<div id="modern-tribe-info">'
		),
		'info-box-title' => array(
			'type' => 'html',
			'html' => '<h2>' . __('Template Settings', 'tribe-events-calendar') . '</h2>',
		),
		'info-box-description' => array(
			'type' => 'html',
			'html' => '<p>' . __('Having trouble making the calendar fit in your theme? On this page, rectify that by selecting what WordPress "page template" The Events Calendar will use to display your event views. The page template controls the usage and placement of the header, sidebar, main content, footer and any other unique content blocks your site may be using. The dropdown below includes the Default Events Template (bundled with the Events framework), Default Page Template (the theme\'s default page.php template), and any other page templates your theme may have shipped with.</p><p>While we do our best to make sure the plugin plays nicely with as many themes as possible, there will inevitably be situations where none of the templating options available below will look 100% perfect. In that situation you may want to check out <a href="http://tri.be/support/documentation/events-calendar-themers-guide/">our themer\'s guide</a> to figure out what templating approach is best for you.', 'tribe-events-calendar') . '</p>',
		),
		'info-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
		'tribeEventsTemplate' => array(
			'type' => 'dropdown_chosen',
		 	'label' => __( 'Events Template', 'tribe-events-calendar' ),
			'tooltip' => __( 'Choose a page template to control the look and feel of your calendar.', 'tribe-events-calendar' ),
			'validation_type' => 'options',
			'size' => 'large',
			'default' => 'default',
			'options' => $template_options,
		),
		'tribeEventsBeforeHTML' => array(
			'type' => 'textarea',
		 	'label' => __( 'Add HTML before calendar', 'tribe-events-calendar' ),
			'tooltip' => __( 'Some themes may require that you add extra divs before the calendar list to help with styling.<br>This is displayed directly after the header.', 'tribe-events-calendar' ) . ' ' . __( 'You may use (x)HTML.', 'tribe-events-calendar' ),
			'validation_type' => 'html',
			'size' => 'large',
		),
		'tribeEventsAfterHTML' => array(
			'type' => 'textarea',
		 	'label' => __( 'Add HTML after calendar', 'tribe-events-calendar' ),
			'tooltip' => __( 'Some themes may require that you add extra divs after the calendar list to help with styling.<br>This is displayed directly above the footer.', 'tribe-events-calendar' ) . ' ' . __( 'You may use (x)HTML.', 'tribe-events-calendar' ),
			'validation_type' => 'html',
			'size' => 'large',
		),
	),
);