<?php
/**
 * Handy function for creating a dropdown field for filters
 * 
 * @param $name string The HTML name for the <select> field
 * @param $options array An array of $key=>$value pairs, producing <option value="$key">$value</option> in the dropdown
 * @param $active string|array The active state of the field. Values correspond to the $key's in $options
 * @param $allow_multi boolean Whether or not this field should be expandable to a multi-select field
 * @return string HTML <select> element
 */
function tribe_select_field( $name, $options = array(), $active = '', $allow_multi = false ) {
	if ( ! class_exists('Tribe_Filters') ) {
		include_once TRIBE_APM_LIB_PATH . 'tribe-filters.class.php';
	}
	return Tribe_Filters::select_field( $name, $options, $active, $allow_multi );
}

/**
 * Registers APM
 *
 * @param $post_type string The post_type Advanced Post Manager will be attached to
 * @param $args array A multidimensional array of filter/column arrays. See documentation for more.
 * @param $metaboxes string|array An array of metabox => Meta Box Title pairs or a single Meta Box Title string
 * @return object Tribe_APM object
 */
function tribe_setup_apm( $post_type, $args, $metaboxes = array() ) {
	return new Tribe_APM( $post_type, $args, $metaboxes );
}