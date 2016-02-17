<?php
class Tribe__Events__Pro__APM_Filters__APM_Filters {
	/**
	 * Class constructor, adds the actions and filters.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'ecp_filters' ) );
		add_action( 'tribe_cpt_filters_after_init', array( $this, 'default_columns' ) );
		add_filter( 'tribe_query_options', array( $this, 'query_options_for_date' ), 10, 3 );
	}

	/**
	 * Set the default columns if a custom set has not been created/being used.
	 *
	 * @param Tribe_APM $apm The passed APM instance.
	 * @return void
	 */
	public function default_columns( $apm ) {
		global $ecp_apm;
		if ( $ecp_apm === $apm ) {
			// Fallback is the order the columns fall back to if nothing was explicitly set
			// An array of column header IDs
			$fallback_columns = array(
				'title',
				'ecp_organizer_filter_key',
				'ecp_venue_filter_key',
				'events-cats',
				'recurring',
				'start-date',
				'end-date',
			);

			/**
			 * Allows filtering the fallback columns that will be used if nothing is explicitly set.
			 *
			 * @since 4.1
			 *
			 * @param array  $fallback_columns An array of filter identifying keys.
			 */
			$fallback_columns = apply_filters('tribe_events_pro_apm_filters_fallback_columns', $fallback_columns);

			$ecp_apm->columns->set_fallback( $fallback_columns );
		}
	}

	/**
	 * Create the events APM with the additional APM filters that TEC uses.
	 *
	 * @return void
	 */
	public function ecp_filters() {

		if ( ! class_exists( 'Tribe_APM' ) ) {
			add_action( 'admin_notices', array( $this, 'maybe_notify_about_new_plugin' ) );
			return;
		}

		$filter_args = array(
			'ecp_venue_filter_key' => array(
				'name' => tribe_get_venue_label_singular(),
				'custom_type' => 'ecp_venue_filter',
				'sortable' => 'true',
			),
			'ecp_organizer_filter_key' => array(
				'name' => tribe_get_organizer_label_singular(),
				'custom_type' => 'ecp_organizer_filter',
				'sortable' => 'true',
			),
			'ecp_start_date' => array(
				'name' => esc_html__( 'Start Date', 'tribe-events-calendar-pro' ),
				'custom_type' => 'custom_date',
				'disable' => 'columns',
			),
			'ecp_end_date' => array(
				'name' => esc_html__( 'End Date', 'tribe-events-calendar-pro' ),
				'custom_type' => 'custom_date',
				'disable' => 'columns',
			),
			'ecp_cost' => array(
				'name' => esc_html__( 'Event Cost', 'tribe-events-calendar-pro' ),
				'meta' => '_EventCost',
				'cast' => 'NUMERIC',
			),
			'ecp_cat' => array(
				'name' => esc_html__( 'Event Cats', 'tribe-events-calendar-pro' ),
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'disable' => 'columns',
			),
			'ecp_title' => array(
				'name' => esc_html__( 'Title', 'tribe-events-calendar-pro' ),
				'custom_type' => 'title',
				'disable' => 'columns',
			),
			'ecp_recur' => array(
				'name' => esc_html__( 'Recurring', 'tribe-events-calendar-pro' ),
				'custom_type' => 'recur',
				'disable' => 'columns',
			),
			'ecp_content' => array(
				'name' => esc_html__( 'Description', 'tribe-events-calendar-pro' ),
				'custom_type' => 'content',
				'disable' => 'columns',
			),
		);

		/**
		 * Allows filtering the filters set up arguments.
		 *
		 * @since 4.1
		 *
		 * @param array $filter_args An associative array of filter set up arguments, see each filter for details.
		 */
		$filter_args = apply_filters('tribe_events_pro_apm_filters_args', $filter_args);

		global $ecp_apm;
		$ecp_apm = new Tribe_APM( Tribe__Events__Main::POSTTYPE, $filter_args );
		$ecp_apm->do_metaboxes = false;
		$ecp_apm->add_taxonomies = false;
	}

	/**
	 * Comparison operators for comparing dates that TEC will need to use.
	 *
	 * @param array $options the current options.
	 * @param string $key
	 * @param mixed $unused_filter
	 * @return array The options with the additional operators.
	 */
	public function query_options_for_date( $options, $key, $unused_filter ) {
		if ( 'ecp_start' == $key ) {
			$options = array( 'gte' => '>=', 'lte' => '<=' );
		}

		return $options;
	}

	public function maybe_notify_about_new_plugin() {

		if ( isset( $_GET['dismiss_apm_nag'] ) ) {
			add_user_meta( get_current_user_id(), '_tribe_apm_plugin_nag', true );
		}

		$screen = get_current_screen();

		if ( $screen->id !== 'edit-tribe_events' ) {
			return;
		}

		if ( get_user_meta( get_current_user_id(), '_tribe_apm_plugin_nag', true ) ) {
			return;
		}


		echo '<div class="updated"><p>';

		$download_link = sprintf(
			'<a href="%s">%s</a>',
			'https://wordpress.org/plugins/advanced-post-manager/',
			esc_html__( 'Download for free', 'tribe-events-calendar-pro' )
		);

		$dismiss_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( 'dismiss_apm_nag', 1 ) ),
			esc_html__( 'Dismiss', 'tribe-events-calendar-pro' )
		);

		printf(
			esc_html__( 'Pssst! Looking for the filters? They live in a separate plugin now | %s | %s', 'tribe-events-calendar-pro' ),
			$download_link,
			$dismiss_link
		);

		echo '</p></div>';

	}
}