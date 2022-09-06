<?php
	return [
		'parent_page' => 'tec',
		'section_id' => 'tec_events_general', // The section ID (required)
		'section_title' => 'General Settings', // The section title (required)
		'section_description' => 'Some intro description about this section.', // The section description (optional)
		'section_order' => 1, // The order of the section (required)
		'fields' => [
			[
				'id' => 'text',
				'title' => 'Text',
				'desc' => 'This is a description.',
				'placeholder' => 'This is a placeholder.',
				'type' => 'text',
				'default' => 'This is the default value'
			],
		],
	];
