<?php

namespace Tribe\Events\Integrations\Fusion;

/**
 * Class Widget_Shortcode
 *
 * @since   5.5.0
 *
 * @package Tribe\Events\Pro\Integrations\Fusion
 */
class Widget_Shortcode {

	/**
	 * Fetches a list of widgets we will fix inside of the Fusion Core builder.
	 *
	 * @since 5.5.0
	 *
	 * @return array
	 */
	public function get_widget_class_map() {
		$classes = [
			\Tribe\Events\Views\V2\Widgets\Widget_List::class,
		];

		/**
		 * Filtering the widget classes we fix for out own widgets on Fusion builder/core.
		 *
		 * @since 5.5.0
		 *
		 * @param array $classes List of classes we are
		 */
		$classes = (array) apply_filters( 'tribe_events_integrations_fusion_widget_class_map', $classes );

		$map = [];

		foreach  ( $classes as $class_name ) {
			$key = str_replace( '\\', '', $class_name );
			$map[ $key ] = $class_name;
		}

		return $map;
	}

	/**
	 * Filters the attributes for shortcodes to modify the class names for Avada/Fusion core widgets.
	 *
	 * @since 5.5.0
	 *
	 *
	 * @param array  $out       The output array of shortcode attributes.
	 * @param array  $pairs     The supported attributes and their defaults.
	 * @param array  $atts      The user defined shortcode attributes.
	 * @param string $shortcode The shortcode name.
	 *
	 * @return array Change the attributes to fix the class name after WordPress borks the Namespaced method.
	 */
	public function fix_type_for_namespaced_widgets( $out, $pairs, $atts, $shortcode ) {
		if ( 'fusion_widget' !== $shortcode ) {
			return $out;
		}

		$class_map = $this->get_widget_class_map();

		// Bail when we are not mapped to fix this type of widget.
		if ( ! isset( $out['type'], $class_map[ $out['type'] ] ) ) {
			return $out;
		}

		$out['type'] = $class_map[ $out['type'] ];

		return $out;
	}
}
