<?php


/**
 * Class Tribe__Events__Integrations__WPML__Defaults
 *
 * Handles sensible defaults for to The Events Calendar in WPML.
 */
class Tribe__Events__Integrations__WPML__Defaults {

	/**
	 * @var string The name of the sub-option that will store the first run flag.
	 */
	public $defaults_option_name = 'wpml_did_set_defaults';

	/**
	 * @var Tribe__Events__Integrations__WPML__Defaults
	 */
	protected static $instance;

	/**
	 * @var SitePress
	 */
	protected $sitepress;

	/**
	 * @var Tribe__Settings_Manager
	 */
	protected $settings_manager;

	/**
	 * Tribe__Events__Integrations__WPML__Defaults constructor.
	 *
	 * @param SitePress|null               $sitepress
	 * @param Tribe__Settings_Manager|null $settings_manager
	 */
	public function __construct( SitePress $sitepress = null, Tribe__Settings_Manager $settings_manager = null ) {
		if ( empty( $sitepress ) ) {
			global /** @var SitePress $sitepress */
			$sitepress;
		}
		$this->sitepress        = $sitepress;
		$this->settings_manager = $settings_manager ? $settings_manager : Tribe__Settings_Manager::instance();
	}

	/**
	 * The class singleton constructor
	 *
	 * @return Tribe__Events__Integrations__WPML__Defaults
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Conditionally set some default values for event related custom fields translation.
	 *
	 * @return bool `false` if defaults were already set, `true` otherwise.
	 */
	public function set_defaults() {
		// make this check again has the action is triggered many times in a request lifecycle
		if ( $this->has_set_defaults() ) {
			return false;
		}

		$translation_management = $this->core_tm();

		if ( empty( $translation_management ) ) {
			return false;
		}

		$fields = $this->get_default_copy_fields();
		foreach ( $fields as $field ) {
			$translation_management->settings['custom_fields_translation'][ $field ] = WPML_COPY_CUSTOM_FIELD;
		}

		// remove the method to avoid infinite loops
		remove_action( 'icl_save_settings', array( $this, 'set_defaults' ) );

		// the Translation Management plugin might not be active on this
		// installation, save this option only if Translation Management is active.
		$translation_management->save_settings();
		Tribe__Settings_Manager::set_option( $this->defaults_option_name, true );

		return true;
	}

	/**
	 * Checks whether default custom field translation option values have been for the current installation.
	 *
	 * @return bool Whether defaaults have been set already or not.
	 */
	public function has_set_defaults() {
		return false !== Tribe__Settings_Manager::get_option( $this->defaults_option_name, false );
	}

	/**
	 * Returns a list of The Events Calendar related fields that should be copied in translation by default.
	 *
	 * This is not a per-event setting but a per-site setting.
	 *
	 * @return array
	 */
	protected function get_default_copy_fields() {
		$default_copy_fields = array(
			'_EventAllDay',
			'_EventStartDate',
			'_EventEndDate',
			'_EventStartDateUTC',
			'_EventEndDateUTC',
			'_EventDuration',
			'_EventVenueID',
			'_EventShowMapLink',
			'_EventShowMap',
			'_EventCurrencySymbol',
			'_EventCurrencyPosition',
			'_EventCost',
			'_EventCostMin',
			'_EventCostMax',
			'_EventURL',
			'_EventOrganizerID',
			'_EventPhone',
			'_EventHideFromUpcoming',
			'_EventTimezone',
			'_EventTimezoneAbbr',
			'_EventRecurrenceRRULE',
			'_VenueCountry',
			'_VenueAddress',
			'_VenueCity',
			'_VenueStateProvince',
			'_VenueState',
			'_VenueProvince',
			'_VenueZip',
			'_VenuePhone',
			'_VenueURL',
			'_OrganizerEmail',
			'_OrganizerWebsite',
			'_OrganizerPhone',
		);

		// The reason this array is not filtered is that each plugin should act independently in setting
		// its own specific defaults, plus this array will be parse once per site and concurrency cannot be
		// granted.
		return $default_copy_fields;
	}

	/**
	 * Wrapper around `Sitepress::core_tm()` method to allow for older WPML versions
	 * to still work.
	 *
	 * @return bool|TranslationManagement Either a ready to use `TranslationManagement`
	 *                                    instance or `false` if the object is not initialized
	 *                                    or not available.
	 */
	protected function core_tm( ) {
		if ( method_exists( $this->sitepress, 'core_tm' ) ) {
			$translation_management = $this->sitepress->core_tm();
		} else {
			global $iclTranslationManagement;
			$translation_management = $iclTranslationManagement;
		}

		$tm_is_active = ! empty( $translation_management )
						&& is_a( $translation_management, 'TranslationManagement' );

		return $tm_is_active ? $translation_management : false;
	}
}
