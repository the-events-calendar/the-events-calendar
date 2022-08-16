<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Page {
	/**
	 * Static Singleton Holder
	 *
	 * @var self|null
	 */
	private static $instance;

	/**
	 * The page slug
	 * @var string
	 */
	public static $slug = 'aggregator';

	/**
	 * Stores the Registered ID from `add_submenu_page`
	 *
	 * @var string
	 */
	public $ID;


	/**
	 * Stores the Tabs Manager class
	 *
	 * @var null|Tribe__Events__Aggregator__Tabs
	 */
	public $tabs;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * A private method to prevent it to be created twice.
	 * It will add the methods and setup any dependencies
	 */
	private function __construct() {
		$plugin = Tribe__Events__Main::instance();

		add_action( 'admin_menu', [ $this, 'register_menu_item' ] );
		add_action( 'current_screen', [ $this, 'action_request' ] );
		add_action( 'init', [ $this, 'init' ] );

		// check if the license is valid each time the page is accessed
		add_action( 'tribe_aggregator_page_request', [ $this, 'check_for_license_updates' ] );

		// filter the plupload default settings to remove mime type restrictions
		add_filter( 'plupload_default_settings', [ $this, 'filter_plupload_default_settings' ] );

		// Setup Tabs Instance
		$this->tabs = Tribe__Events__Aggregator__Tabs::instance();

		tribe_notice(
			'tribe-aggregator-legacy-import-plugins-active',
			[
				$this,
				'notice_legacy_plugins',
			],
			'type=warning'
		);
	}

	public function init() {
		$plugin = Tribe__Events__Main::instance();

		$localize_data_callback = static function() {
			$localize_data = [
				'csv_column_mapping'   => [
					'events'    => get_option( 'tribe_events_import_column_mapping_events', [] ),
					'organizer' => get_option( 'tribe_events_import_column_mapping_organizers', [] ),
					'venue'     => get_option( 'tribe_events_import_column_mapping_venues', [] ),
				],
				'l10n'                 => [
					'all_day'                           => __( 'All Day', 'the-events-calendar' ),
					'am'                                => _x( 'AM', 'Meridian: am', 'the-events-calendar' ),
					'pm'                                => _x( 'PM', 'Meridian: pm', 'the-events-calendar' ),
					'preview_timeout'                   => __(
						'The preview is taking longer than expected. Please try again in a moment.',
						'the-events-calendar'
					),
					'preview_fetch_error_prefix'        => __(
						'There was an error fetching the results from your import:',
						'the-events-calendar'
					),
					'preview_fetch_warning_prefix'      => __(
						'A warning was generated while fetching the results from your import:',
						'the-events-calendar'
					),
					'import_all'                        => __( 'Import All (%d)', 'the-events-calendar' ),
					'import_all_no_number'              => __( 'Import All', 'the-events-calendar' ),
					'import_checked'                    => __( 'Import Checked (%d)', 'the-events-calendar' ),
					'create_schedule'                   => __( 'Save Scheduled Import', 'the-events-calendar' ),
					'edit_save'                         => __( 'Save Changes', 'the-events-calendar' ),
					'events_required_for_manual_submit' => __(
						'Your import must include at least one event',
						'the-events-calendar'
					),
					'no_results'                        => __(
						'Your preview doesn\'t have any records to import.',
						'the-events-calendar'
					),
					'verify_schedule_delete'            => __(
						'Removing this scheduled import will stop automatic imports from the source. No events will be deleted.',
						'the-events-calendar'
					),
					'view_filters'                      => __( 'View Filters', 'the-events-calendar' ),
					'hide_filters'                      => __( 'Hide Filters', 'the-events-calendar' ),
					'preview_polling'                   => [
						__( 'Please wait while your preview is fetched.', 'the-events-calendar' ),
						__( 'Please continue to wait while your preview is generated.', 'the-events-calendar' ),
						__(
							'If all goes according to plan, you will have your preview in a few moments.',
							'the-events-calendar'
						),
						__(
							'Your preview is taking a bit longer than expected, but it <i>is</i> still being generated.',
							'the-events-calendar'
						),
					],
					'debug'                             => defined( 'WP_DEBUG' ) && true === WP_DEBUG,
				],
				'default_settings'     => tribe( 'events-aggregator.settings' )->get_all_default_settings(),
				'source_origin_regexp' => tribe( 'events-aggregator.settings' )->get_source_origin_regexp(),
			];

			/**
			 * Filters the CSV column mapping output
			 *
			 * @param array $mapping Mapping data indexed by CSV import type
			 */
			$localize_data['csv_column_mapping'] = apply_filters( 'tribe_aggregator_csv_column_mapping', $localize_data['csv_column_mapping'] );

			/**
			 * filters the whole array that will be localized for event aggregator.
			 *
			 * @param array $localize_data
			 */
			$localize_data = apply_filters( 'tribe_aggregator_localized_data', $localize_data );

			return $localize_data;
		};

		// Load these on all the pages
		tribe_assets(
			$plugin,
			[
				[
					'tribe-ea-fields',
					'aggregator-fields.js',
					[
						'jquery',
						'tribe-datatables',
						'underscore',
						'tribe-bumpdown',
						'tribe-dependency',
						'tribe-events-admin',
						'tribe-events-bootstrap-datepicker',
						'tribe-dropdowns',
					],
				],
				[ 'tribe-ea-page', 'datatables-css' ],
			],
			'admin_enqueue_scripts',
			[
				'conditionals' => [
					[ $this, 'is_screen' ],
				],
				'localize'     => (object) [
					'name' => 'tribe_aggregator',
					'data' => $localize_data_callback,
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-ea-styles',
			'aggregator-page.css',
			[],
			'admin_enqueue_scripts',
			[
				'conditionals' => [
					'operator' => 'OR',
					[ $this, 'is_screen' ],
					[ $this, 'aggregator_should_load_scripts' ],
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-ea-notice',
			'aggregator-notice.js',
			[ 'jquery' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [
					'operator' => 'OR',
					[ $this, 'is_screen' ],
					[ $this, 'aggregator_should_load_scripts' ],
				]
			]
		);
	}

	/**
	 * Filter the plupload media settings to remove mime_type restrictions
	 *
	 * Even though .ics is in the default extension list for supported mime types,
	 * Safari ignores that fact. Let's not restrict the extensions (much like the
	 * Dashboard's Add New Media page)
	 *
	 * @param array $settings Plupload settings
	 *
	 * @return array
	 */
	public function filter_plupload_default_settings( $settings ) {
		if ( ! $this->is_screen() ) {
			return $settings;
		}


		if ( isset( $settings['filters']['mime_types'] ) ) {
			$extensions = [
				'csv', // comma-separated values
				'xls', // Excel 97- Excel 2003 Workbook
				'xlsx', // Excel Workbook
			];

			if ( tribe( 'events-aggregator.main' )->is_service_active() ) {
				$extensions[] = 'ics';
			}

			$settings['filters']['mime_types'] = [
				[
					'extensions' => implode( ',', $extensions ),
				],
			];
		}

		return $settings;
	}

	/**
	 * Hooked to `current_screen` allow tabs and other parts of the plugin to hook to aggregator before rendering any headers
	 *
	 * @param  WP_Screen $screen Variable from `current_screen`
	 *
	 * @return bool
	 */
	public function action_request( $screen ) {
		if ( ! $this->is_screen() ) {
			return false;
		}

		/**
		 * Fires an Action to allow Form actions to be hooked to
		 */
		return do_action( 'tribe_aggregator_page_request' );
	}

	/**
	 * Basically an edited version of is_screen(), below,
	 * that allows for loading on all non-post edit admin pages.
	 *
	 * @since 5.16.2.1
	 *
	 * @return boolean
	 */
	public function aggregator_should_load_scripts() {
		global $current_screen;

		// Doing AJAX? bail.
		if ( tribe( 'context' )->doing_ajax() ) {
			return false;
		}

		if ( ! ( $current_screen instanceof WP_Screen ) ) {
			return false;
		}

		// Don't load on post edit screens - can conflict with other datepickers.
		if ( $current_screen->base === 'post' || $current_screen->base === 'post-new') {
			return false;
		}

		/**
		 * Allows for selective disabling of script loading.
		 *
		 * @since 5.16.2.1
		 *
		 * @param boolean $should_load Whether the scripts should load. Default true if we got here
		 */
		return apply_filters( 'aggregator_should_load_scripts', true );
	}

	/**
	 * Checks if we are on the correct screen.
	 *
	 * @return boolean
	 */
	public function is_screen() {
		global $current_screen;

		// Not in the admin we don't even care
		if ( ! is_admin() ) {
			return false;
		}

		// Doing AJAX? bail.
		if ( tribe( 'context' )->doing_ajax() ) {
			return false;
		}

		if ( ! ( $current_screen instanceof WP_Screen ) ) {
			return false;
		}

		return ! empty( $this->ID ) && $current_screen->id === $this->ID;
	}

	/**
	 * Checks if the license is still valid once the aggregator page
	 * is accessed.
	 *
	 * @since 4.6.19
	 *
	 * @return void
	 */
	public function check_for_license_updates() {

		$aggregator = tribe( 'events-aggregator.main' );
		$aggregator->pue_checker->check_for_updates();

	}

	/**
	 * Returns the main admin settings URL.
	 *
	 * @param array|string $args     Query String or Array with the arguments
	 * @param boolean      $relative Return a relative URL or absolute
	 *
	 * @return string
	 */
	public function get_url( $args = [], $relative = false ) {
		$defaults = [
			'page'      => self::$slug,
			'post_type' => Tribe__Events__Main::POSTTYPE,
		];

		// Allow the link to be "changed" on the fly
		$args = wp_parse_args( $args, $defaults );

		// Base relative URL
		$url = 'edit.php';

		// Keep the URL as a Relative one
		if ( ! $relative ) {
			$url = admin_url( $url );
		}

		// Add the Arguments
		$url = add_query_arg( $args, $url );

		/**
		 * Allow users to filter the Admin Page URL
		 *
		 * @param string $url
		 * @param array  $args
		 */
		$url = apply_filters( 'tribe_aggregator_admin_page', $url, $args );

		return $url;
	}

	/**
	 * Gets the Menu label for the Aggregator
	 *
	 * @return string
	 */
	public function get_menu_label() {
		return __( 'Import', 'the-events-calendar' );
	}

	/**
	 * Gets the Page title for the Aggregator
	 *
	 * @return string
	 */
	public function get_page_title() {
		return __( 'Events Import', 'the-events-calendar' );
	}

	/**
	 * Register the Sub Menu item for this page
	 *
	 * @return string Page ID on WordPress
	 */
	public function register_menu_item() {
		$cpt      = get_post_type_object( Tribe__Events__Main::POSTTYPE );
		$this->ID = add_submenu_page(
			$this->get_url( [ 'page' => null ], true ),
			esc_html( $this->get_page_title() ),
			esc_html( $this->get_menu_label() ),
			$cpt->cap->publish_posts,
			self::$slug,
			[ $this, 'render' ]
		);

		return $this->ID;
	}

	/**
	 * A very simple method to include a Aggregator Template, allowing filtering and additions using hooks.
	 *
	 * @param string  $name Which file we are talking about including-
	 * @param array   $data Any context data you need to expose to this file.
	 * @param boolean $echo If we should also print the Template.
	 *
	 * @return string        Final Content HTML
	 */
	public function template( $name, $data = [], $echo = true ) {
		// Clean this Variable
		$name = array_map( 'sanitize_title_with_dashes', (array) explode( '/', $name ) );

		$file = Tribe__Events__Main::instance()->plugin_path;
		$file .= 'src/admin-views/aggregator/' . implode( DIRECTORY_SEPARATOR, $name ) . '.php';

		/**
		 * A more Specific Filter that will include the template name
		 *
		 * @since 4.3
		 *
		 * @param string $file Complete path to include the PHP File.
		 * @param array  $name Template name.
		 * @param array  $data The Data that will be used on this template.
		 *
		 * @return string $file Complete path to include the PHP File
		 */
		$file = apply_filters( 'tribe_aggregator_template_file', $file, $name, $data );

		if ( ! file_exists( $file ) ) {
			return false;
		}

		ob_start();
		/**
		 * Fires an Action before including the template file
		 *
		 * @param string $file     Complete path to include the PHP File
		 * @param string $name     Template name
		 * @param array  $data     The Data that will be used on this template
		 */
		do_action( 'tribe_aggregator_template_before_include', $file, $name, $data );

		// Make any provided variables available in the template's symbol table
		if ( is_array( $data ) ) {
			extract( $data );
		}

		include $file;

		/**
		 * Fires an Action After including the template file
		 *
		 * @param string $file     Complete path to include the PHP File
		 * @param string $name     Template name
		 * @param array  $data     The Data that will be used on this template
		 */
		do_action( 'tribe_aggregator_template_after_include', $file, $name, $data );
		$html = ob_get_clean();

		/**
		 * Allow users to filter the final HTML
		 *
		 * @param string $html     The final HTML
		 * @param string $file     Complete path to include the PHP File
		 * @param string $name     Template name
		 * @param array  $data     The Data that will be used on this template
		 */
		$html = apply_filters( 'tribe_aggregator_template_html', $html, $file, $name, $data );

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}

	/**
	 * A simple shortcut to render the Template for the page
	 *
	 * @return string
	 */
	public function render() {
		return $this->template( 'page' );
	}

	public function notice_legacy_plugins() {
		if ( ! Tribe__Admin__Helpers::instance()->is_screen() ) {
			return false;
		}

		$aggregator = tribe( 'events-aggregator.main' );

		if ( ! $aggregator->is_service_active() ) {
			return false;
		}

		$ical_active     = $aggregator->is_legacy_ical_active();
		$facebook_active = $aggregator->is_legacy_facebook_active();

		if ( ! $ical_active && ! $facebook_active ) {
			return false;
		}

		$active = [];

		if ( $facebook_active ) {
			$active[] = '<b>' . esc_html__( 'Facebook Events', 'the-events-calendar' ) . '</b>';
		}

		if ( $ical_active ) {
			$active[] = '<b>' . esc_html__( 'iCal Importer', 'the-events-calendar' ) . '</b>';
		}

		ob_start();
		?>
		<p>
			<?php
			printf(
				esc_html(
					_n(
						'It looks like you are using our legacy plugin, %1$s, along with our new Event Aggregator service. Event Aggregator includes all the features of the legacy plugin plus enhanced functionality. For best results, please deactivate %1$s.',
						'It looks like you are using our legacy plugins, %1$s and %2$s, along with our new Event Aggregator service. Event Aggregator includes all the features of the legacy plugins plus enhanced functionality. For best results, please deactivate %1$s and %2$s.',
						count( $active ),
						'the-events-calendar'
					)
				),
				$active[0],
				isset( $active[1] ) ? $active[1] : ''
			);
			?>
		</p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'plugins.php?plugin_status=active' ) ); ?>"><?php esc_html_e( 'Manage Active Plugins', 'the-events-calendar' ); ?></a>
		</p>
		<?php
		$html = ob_get_clean();

		return Tribe__Admin__Notices::instance()->render( 'tribe-aggregator-legacy-import-plugins-active', $html );
	}
}
