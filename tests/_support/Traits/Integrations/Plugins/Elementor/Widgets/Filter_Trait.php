<?php
/**
 * Provides test methods for Elementor widget using the TEC templating engine.
 *
 * @since   6.6.0
 *
 * @package Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets;
 */

namespace Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets;

use TEC\Events\Integrations\Plugins\Elementor\Widgets\Contracts\Abstract_Widget;

/**
 * Trait for Elementor widget template tests.
 *
 * @since   6.6.0
 *
 * @package Tribe\Events\Test\Traits\Integrations\Plugins\Elementor\Widgets;
 */
trait Filter_Trait {
	/**
	 * Filters the template data for the widget.
	 *
	 * @since 6.5.2
	 *
	 * @param array $override The data to override with.
	 * @param array $passed   The data passed to the template.
	 */
	public function filter_template_data( $override ) {
		add_filter(
			$this->filter,
			function( $passed ) use ( $override ){
				return array_merge( $passed, $override );
			},
			20
		);
	}

	public function trigger_filter( $object ) {
		if ( isset( $object['additional'] ) ) {
			foreach ( $object['additional'] as $key => $value ) {
				$data[ $key ] = $value;
			}
		}

		// Make sure this overrides the "additional" data.
		$data[ $object['label'] ] = $object['value'];

		$this->filter_template_data(
			$data
		);
	}

	public function tidy_render( $render ){
		// Remove the whitespace and newlines.
		$render = preg_replace( '/\s+/', ' ', $render );

		return $render;
	}

	public function render_filtered( $object, $widget ) {
		$this->trigger_filter( $object );

		if ( ! $widget instanceof Abstract_Widget ) {
			$widget = new $widget();
		}

		$output = $this->tidy_render( $widget->get_output() );

		if ( isset( $object['render'] ) && false === $object['render'] ) {
			// No string means the widget won't render anything.
			$this->assertEmpty( $output );
		} else {
			// Ensure the rendered HTML is as expected.
			$this->assertMatchesHtmlSnapshot( $output );

			if ( empty( $object['invert'] ) ) {
				// ensure the label has been changed
				$this->assertContains( $object['string'], $output );
			} else {
				// ensure the label does not exist
				$this->assertNotContains( $object['string'], $output );
			}
		}
	}
}
