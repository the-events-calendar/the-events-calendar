<?php
/**
 * Events Editor Compatibility
 *
 * @since TBD
 */
class Tribe__Events__Editor__Compatibility {

	/**
	 * Gets if user toggled blocks editor on the settings
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function is_blocks_editor_toggled_on() {
		$option = tribe_get_option( $this->get_toggle_blocks_editor_key(), false );

		return tribe_is_truthy( $option );
	}

	/**
	 * Gets the option key for toggling Blocks Editor active
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_toggle_blocks_editor_key() {
		return 'toggle_blocks_editor';
	}

	/**
	 * Gets the option key for the Hidden Field of toggling blocks editor
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_toggle_blocks_editor_hidden_key() {
		return 'toggle_blocks_editor_hidden_field';
	}

	/**
	 * Inserts the Toggle and Hidden Field for the Activation of Blocks Editor
	 *
	 * @since TBD
	 *
	 * @param array $fields Fields from Options General
	 *
	 * @return array
	 */
	public function insert_toggle_blocks_editor_field( $fields = array() ) {
		if ( ! tribe( 'editor' )->is_wp_version() ) {
			return $fields;
		}

		$insert_after = 'liveFiltersUpdate';
		$insert_data = array(
			$this->get_toggle_blocks_editor_key() => array(
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Activate Block Editor for Events', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'Enable early access to the new Gutenberg block editor interface for creating events.', 'the-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
				'attributes'      => array( 'id' => 'tribe-blocks-editor-toggle-field' ),
			),
			$this->get_toggle_blocks_editor_hidden_key() => array(
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Hidden Blocks Editor Config', 'the-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
				'attributes'      => array( 'id' => 'tribe-blocks-editor-toggle-hidden-field' ),
			),
		);

		return Tribe__Main::array_insert_after_key( $insert_after, $fields, $insert_data );
	}

}
