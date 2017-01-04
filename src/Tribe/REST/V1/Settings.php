<?php

/**
 * Class Tribe__Events__REST__V1__Settings
 *
 * Adds and manages the TEC REST API settings.
 */
class Tribe__Events__REST__V1__Settings {

	/**
	 * @var Tribe__Events__REST__V1__System
	 */
	protected $system;

	/**
	 * Tribe__Events__REST__V1__Settings constructor.
	 *
	 * @param Tribe__Events__REST__V1__System $system
	 */
	public function __construct( Tribe__Events__REST__V1__System $system ) {
		$this->system = $system;
	}

	/**
	 * @param array $fields
	 *
	 * @return array
	 */
	public function filter_tribe_addons_tab_fields( array $fields = array() ) {
		if ( ! $this->system->supports_wp_rest_api() ) {
			return $fields;
		}

		if ( ! $this->system->supports_tec_rest_api() ) {
			return $fields;
		}

		return $this->add_fields( $fields );
	}

	/**
	 * @param array $fields
	 *
	 * @return array
	 */
	protected function add_fields( array $fields = array() ) {
		$option = Tribe__Events__REST__V1__System::get_disable_option_name();

		$additional_fields = array(
			'rest-v1-api-start' => array(
				'type' => 'html',
				'html' => '<h3>' . esc_html__( 'The Events Calendar REST API', 'the-events-calendar' ) . '</h3>',
			),

			'rest-v1-api-info-box' => array(
				'type' => 'html',
				'html' => '<p>' . __( 'The Events Calendar implements its own REST API that applications and websites can use to get events published on your site; you can disable it by unchecking this option.', 'the-events-calendar' ) . '</p>',
			),

			$option => array(
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Disable The Events Calendar REST API', 'the-events-calendar' ),
				'tooltip'         =>  __( 'By checking this box you will disable The Events Calendar REST API; you can re-enable it later.', 'the-events-calendar' ),
				'validation_type' => 'boolean',
				'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			),
		);

		return array_merge( (array) $fields, $additional_fields );
	}
}