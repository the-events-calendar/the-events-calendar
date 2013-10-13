<?php

/**
 * A sample of acceptable filter/column data
 */

$filters_and_columns = array(
	'key' => array( // Key is required.
		// Required
		'name' => 'Filter / Column title', 
		// If it's a meta query, set this to the meta key to be queried
		'meta' => 'Piece of post meta to query',
		// The taxonomy. APM by default automatically adds taxonomies.
		'taxonomy' => 'registered taxonomy',
		// Your query type doesn't fit the standard kind
		'custom_type' => 'a key for registering your own query handlers',
		// A way to limit the queried field to a dropdown rather than a search box
		'options' => array( 
			'meta_value' => 'Nicer Title',
			'another_meta_value' => 'another_meta_value'
		),
		// optional, for use with "meta" filters. Useful for when you want ordering to assume that meta_values are a certain type, such as numeric or date.
		'cast' => 'SIGNED',
		// what type of field to use in the meta box
		'field' => 'text',
		// read the value.
		'desc' => 'Optional supporting text for display inside a metabox',
		// explicitly put in a particular metabox. Pay attention to the $metaboxes arg on tribe_setup_apm()
		'metabox' => 'somemetaboxid',
		// Set an explicit order inside a metabox
		'meta_order' => 3
		// Some 'types' take additional arguments
	)
);

