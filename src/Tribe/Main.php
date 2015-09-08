<?php

	if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
		class Tribe__Events__Pro__Main {

			private static $instance;

			public $pluginDir;
			public $pluginPath;
			public $pluginUrl;
			public $pluginSlug;

			public $weekSlug = 'week';
			public $photoSlug = 'photo';

			public $singular_event_label;
			public $plural_event_label;

			/** @var Tribe__Events__Pro__Recurrence_Permalinks */
			public $permalink_editor = null;

			/**
			 * @var Tribe__Events__Pro__Single_Event_Meta
			 */
			public $single_event_meta;

			/** @var Tribe__Events__Pro__Recurrence__Queue_Processor */
			public $queue_processor;

			/** @var Tribe__Events__Pro__Recurrence__Queue_Realtime */
			public $queue_realtime;

			/**
			 * @var Tribe__Events__Pro__Embedded_Maps
			 */
			public $embedded_maps;

			/**
			 * @var Tribe__Events__Pro__Shortcodes__Widget_Wrappers
			 */
			public $widget_wrappers;


			const REQUIRED_TEC_VERSION = '3.12';
			const VERSION = '3.12';

			private function __construct() {
				$this->pluginDir = trailingslashit( basename( EVENTS_CALENDAR_PRO_DIR ) );
				$this->pluginPath = trailingslashit( EVENTS_CALENDAR_PRO_DIR );
				$this->pluginUrl = plugins_url( $this->pluginDir );
				$this->pluginSlug = 'events-calendar-pro';

				$this->loadTextDomain();

				$this->weekSlug = sanitize_title( __( 'week', 'tribe-events-calendar-pro' ) );
				$this->photoSlug = sanitize_title( __( 'photo', 'tribe-events-calendar-pro' ) );

				require_once( $this->pluginPath . 'src/functions/template-tags/general.php' );
				require_once( $this->pluginPath . 'src/functions/template-tags/week.php' );
				require_once( $this->pluginPath . 'src/functions/template-tags/venue.php' );
				require_once( $this->pluginPath . 'src/functions/template-tags/widgets.php' );

				// Load Deprecated Template Tags
				if ( ! defined( 'TRIBE_DISABLE_DEPRECATED_TAGS' ) ) {
					require_once $this->pluginPath . 'src/functions/template-tags/deprecated.php';
				}

				add_action( 'admin_init', array( $this, 'run_updates' ), 10, 0 );

				// Tribe common resources
				add_action( 'tribe_helper_activation_complete', array( $this, 'helpersLoaded' ) );

				add_action( 'init', array( $this, 'init' ), 10 );
				add_action( 'admin_print_styles', array( $this, 'admin_enqueue_styles' ) );
				add_action( 'tribe_events_enqueue', array( $this, 'admin_enqueue_scripts' ) );
				add_action( 'tribe_venues_enqueue', array( $this, 'admin_enqueue_scripts' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_pro_scripts' ), 8 );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

				// Rewrite Related Filters
				add_filter( 'tribe_events_pre_rewrite', array( $this, 'filter_add_routes' ), 11 );
				add_filter( 'tribe_events_rewrite_base_slugs', array( $this, 'filter_add_base_slugs' ), 11 );
				add_filter( 'tribe_events_rewrite_i18n_domains', array( $this, 'filter_add_i18n_pro_domain' ), 11 );

				add_action( 'tribe_settings_do_tabs', array( $this, 'add_settings_tabs' ) );
				add_filter( 'tribe_settings_tab_fields', array( $this, 'filter_settings_tab_fields' ), 10, 2 );
				add_action( 'tribe_events_parse_query', array( $this, 'parse_query' ) );
				add_action( 'tribe_events_pre_get_posts', array( $this, 'pre_get_posts' ) );
				add_filter( 'tribe_enable_recurring_event_queries', '__return_true', 10, 1 );
				add_filter( 'body_class', array( $this, 'body_class' ) );
				add_filter( 'tribe_events_current_view_template', array( $this, 'select_page_template' ) );
				add_filter( 'tribe_events_current_template_class', array( $this, 'get_current_template_class' ) );
				add_filter( 'tribe_events_template_paths', array( $this, 'template_paths' ) );
				add_filter( 'tribe_events_template_class_path', array( $this, 'template_class_path' ) );

				add_filter( 'tribe_help_tab_getting_started_text', array( $this, 'add_help_tab_getting_started_text' ) );
				add_filter( 'tribe_help_tab_introtext', array( $this, 'add_help_tab_intro_text' ) );
				add_filter( 'tribe_help_tab_forumtext', array( $this, 'add_help_tab_forumtext' ) );

				add_action( 'widgets_init', array( $this, 'pro_widgets_init' ), 100 );
				add_action( 'wp_loaded', array( $this, 'allow_cpt_search' ) );
				add_action( 'plugin_row_meta', array( $this, 'addMetaLinks' ), 10, 2 );
				add_filter( 'tribe_get_events_title', array( $this, 'reset_page_title' ), 10, 2 );
				add_filter( 'tribe_events_title_tag', array( $this, 'maybeAddEventTitle' ), 10, 3 );

				add_filter( 'tribe_help_tab_forums_url', array( $this, 'helpTabForumsLink' ) );
				add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array(
					$this,
					'addLinksToPluginActions',
				) );

				add_filter( 'tribe_events_before_html', array( $this, 'events_before_html' ), 10 );

				// add custom fields to "the_meta" on single event template
				add_filter( 'tribe_events_single_event_the_meta_addon', array(
					$this,
					'single_event_the_meta_addon',
				), 10, 2 );
				add_filter( 'tribe_events_single_event_meta_group_template_keys', array(
					$this,
					'single_event_meta_group_template_keys',
				), 10 );
				add_filter( 'tribe_events_single_event_meta_template_keys', array(
					$this,
					'single_event_meta_template_keys',
				), 10 );
				add_filter( 'tribe_event_meta_venue_name', array( 'Tribe__Events__Pro__Single_Event_Meta', 'venue_name' ), 10, 2 );
				add_filter( 'tribe_event_meta_organizer_name', array(
					'Tribe__Events__Pro__Single_Event_Meta',
					'organizer_name',
				), 10, 2 );
				add_filter( 'tribe_events_single_event_the_meta_group_venue', array(
					$this,
					'single_event_the_meta_group_venue',
				), 10, 2 );

				$this->enable_recurring_info_tooltip();
				add_action( 'tribe_events_before_the_grid', array( $this, 'disable_recurring_info_tooltip' ), 10, 0 );
				add_action( 'tribe_events_after_the_grid', array( $this, 'enable_recurring_info_tooltip' ), 10, 0 );
				add_action( 'tribe_events_single_event_after_the_meta', array( $this, 'register_related_events_view' ) );

				// see function tribe_convert_units( $value, $unit_from, $unit_to )
				add_filter( 'tribe_convert_kms_to_miles_ratio', array( $this, 'kms_to_miles_ratio' ) );
				add_filter( 'tribe_convert_miles_to_kms_ratio', array( $this, 'miles_to_kms_ratio' ) );

				/* Setup Tribe Events Bar */
				add_filter( 'tribe-events-bar-views', array( $this, 'setup_weekview_in_bar' ), 10, 1 );
				add_filter( 'tribe-events-bar-views', array( $this, 'setup_photoview_in_bar' ), 30, 1 );
				add_filter( 'tribe_events_ugly_link', array( $this, 'ugly_link' ), 10, 3 );
				add_filter( 'tribe_events_getLink', array( $this, 'get_link' ), 10, 4 );
				add_filter( 'tribe_get_listview_link', array( $this, 'get_all_link' ) );
				add_filter( 'tribe_get_listview_dir_link', array( $this, 'get_all_dir_link' ) );
				add_filter( 'tribe_bar_datepicker_caption', array( $this, 'setup_datepicker_label' ), 10, 1 );
				add_action( 'tribe_events_after_the_title', array( $this, 'add_recurring_occurance_setting_to_list' ) );

				add_filter( 'tribe_is_ajax_view_request', array( $this, 'is_pro_ajax_view_request' ), 10, 2 );

				add_action( 'tribe_events_pre_get_posts', array( $this, 'setup_hide_recurrence_in_query' ) );

				add_filter( 'wp', array( $this, 'detect_recurrence_redirect' ) );
				add_filter( 'template_redirect', array( $this, 'filter_canonical_link_on_recurring_events' ), 10, 1 );

				$this->permalink_editor = apply_filters( 'tribe_events_permalink_editor', new Tribe__Events__Pro__Recurrence_Permalinks() );
				add_filter( 'post_type_link', array(
					$this->permalink_editor,
					'filter_recurring_event_permalinks',
				), 10, 4 );

				add_filter( 'tribe_events_register_venue_type_args', array( $this, 'addSupportsThumbnail' ), 10, 1 );
				add_filter( 'tribe_events_register_organizer_type_args', array( $this, 'addSupportsThumbnail' ), 10, 1 );
				add_action( 'post_updated_messages', array( $this, 'updatePostMessages' ), 20 );

				add_filter( 'tribe_events_default_value_strategy', array( $this, 'set_default_value_strategy' ) );
				add_action( 'plugins_loaded', array( $this, 'init_apm_filters' ) );

				// override list view ajax get_event args if viewing all instances of a recurring post
				add_filter( 'tribe_events_listview_ajax_get_event_args', array( $this, 'override_listview_get_event_args' ), 10, 2 );
				add_filter( 'tribe_events_listview_ajax_event_display', array( $this, 'override_listview_display_setting' ), 10, 2 );
			}

			/**
			 * Make necessary database updates on admin_init
			 *
			 * @return void
			 */
			public function run_updates() {
				if ( ! class_exists( 'Tribe__Events__Updater' ) ) {
					return; // core needs to be updated for compatibility
				}
				$updater = new Tribe__Events__Pro__Updater( self::VERSION );
				if ( $updater->update_required() ) {
					$updater->do_updates();
				}
			}

			/**
			 * @return bool Whether related events should be shown in the single view
			 */
			public function show_related_events() {
				if ( tribe_get_option( 'hideRelatedEvents', false ) == true ) {
					return false;
				}

				return true;
			}

			/**
			 * add related events to single event view
			 *
			 * @return void
			 */
			public function register_related_events_view() {
				if ( $this->show_related_events() ) {
					tribe_single_related_events();
				}
			}

			/**
			 * Append the recurring info tooltip after an event schedule
			 *
			 * @param string $schedule_details
			 * @param int $event_id
			 *
			 * @return string
			 */
			public function append_recurring_info_tooltip( $schedule_details, $event_id = 0 ) {
				$tooltip = tribe_events_recurrence_tooltip( $event_id );

				return $schedule_details . $tooltip;
			}

			public function enable_recurring_info_tooltip() {
				add_filter( 'tribe_events_event_schedule_details', array( $this, 'append_recurring_info_tooltip' ), 9, 2 );
			}

			public function disable_recurring_info_tooltip() {
				remove_filter(
					'tribe_events_event_schedule_details', array(
					$this,
					'append_recurring_info_tooltip',
				), 9, 2
				);
			}

			public function recurring_info_tooltip_status() {
				if ( has_filter(
					'tribe_events_event_schedule_details', array(
						$this,
						'append_recurring_info_tooltip',
					)
				)
				) {
					return true;
				}

				return false;
			}

			/**
			 * Filters in a meta walker group for new items regarding the PRO addon.
			 *
			 * @param string $html The current HTML for the event meta..
			 * @param int $event_id The post_id of the current event.
			 *
			 * @return string The modified HTML for the event meta.
			 */
			public function single_event_the_meta_addon( $html, $event_id ) {

				// add custom meta if it's available
				$html .= tribe_get_meta_group( 'tribe_event_group_custom_meta' );

				return $html;
			}

			/**
			 * Adds for the meta walker a key for custom meta to do with PRO addon.
			 *
			 * @param array $keys The current array of meta keys.
			 *
			 * @return array The modified array.
			 */
			public function single_event_meta_template_keys( $keys ) {
				$keys[] = 'tribe_event_custom_meta';

				return $keys;
			}

			/**
			 * Adds for the meta walker a key for custom meta groups to do with PRO addon.
			 *
			 * @param array $keys The current array of meta keys.
			 *
			 * @return array The modified array.
			 */
			public function single_event_meta_group_template_keys( $keys ) {
				$keys[] = 'tribe_event_group_custom_meta';

				return $keys;
			}

			/**
			 * Adds (currently nothing) to the venue section of the meta walker for single events.
			 *
			 * @param bool $status Whether currently it is filtered to display venue information in a group or not.
			 * @param int $event_id The post_id of the current event.
			 *
			 * @return bool The modified boolean.
			 */
			public function single_event_the_meta_group_venue( $status, $event_id ) {

				return $status;
			}

			/**
			 * Modifies the page title for pro views.
			 *
			 * @param string $new_title The currently filtered title.
			 * @param string $title The oldest default title.
			 * @param string $sep The separator for title elements.
			 *
			 * @return string The modified title.
			 * @todo remove in 3.10
			 * @deprecated
			 */
			public function maybeAddEventTitle( $new_title, $title, $sep = null ) {
				if ( has_filter( 'tribe_events_pro_add_title' ) ) {
					_deprecated_function( "The 'tribe_events_pro_add_title' filter", '3.8', " the 'tribe_events_add_title' filter" );

					return apply_filters( 'tribe_events_pro_add_title', $new_title, $title, $sep );
				}

				return $new_title;
			}

			/**
			 * Gets the events_before_html content.
			 *
			 * @param string $html The events_before_html currently.
			 *
			 * @return string The modified html.
			 */
			public function events_before_html( $html ) {
				global $wp_query;
				if ( $wp_query->tribe_is_event_venue || $wp_query->tribe_is_event_organizer ) {
					add_filter( 'tribe-events-bar-should-show', '__return_false' );
				}

				return $html;
			}

			/**
			 * Sets the page title for the various PRO views.
			 *
			 * @param string $title The current title.
			 *
			 * @return string The modified title.
			 */
			public function reset_page_title( $title, $depth = true ) {
				global $wp_query;
				$tec = Tribe__Events__Main::instance();
				$date_format = apply_filters( 'tribe_events_pro_page_title_date_format', tribe_get_date_format( true ) );

				if ( tribe_is_showing_all() ) {
					$reset_title = sprintf( __( 'All %1$s for %2$s', 'tribe-events-calendar-pro' ), strtolower( $this->plural_event_label ), get_the_title() );
				}

				// week view title
				if ( tribe_is_week() ) {
					$reset_title = sprintf(
						__( '%1$s for week of %2$s', 'tribe-events-calendar-pro' ),
						$this->plural_event_label,
						date_i18n( $date_format, strtotime( tribe_get_first_week_day( $wp_query->get( 'start_date' ) ) ) )
					);
				}

				if ( ! empty( $reset_title ) && is_tax( $tec->get_event_taxonomy() ) && $depth ) {
					$cat = get_queried_object();
					$reset_title = '<a href="' . tribe_get_events_link() . '">' . $reset_title . '</a>';
					$reset_title .= ' &#8250; ' . $cat->name;
				}

				return isset( $reset_title ) ? $reset_title : $title;
			}

			/**
			 * The class init function.
			 *
			 * @return void
			 */
			public function init() {
				Tribe__Events__Pro__Mini_Calendar::instance();
				Tribe__Events__Pro__Custom_Meta::init();
				Tribe__Events__Pro__Recurrence_Meta::init();
				Tribe__Events__Pro__Geo_Loc::instance();
				Tribe__Events__Pro__Community_Modifications::init();
				$this->displayMetaboxCustomFields();
				$this->single_event_meta = new Tribe__Events__Pro__Single_Event_Meta;
				$this->queue_processor = new Tribe__Events__Pro__Recurrence__Queue_Processor;
				$this->queue_realtime = new Tribe__Events__Pro__Recurrence__Queue_Realtime;
				$this->embedded_maps = new Tribe__Events__Pro__Embedded_Maps;
				$this->widget_wrappers = new Tribe__Events__Pro__Shortcodes__Widget_Wrappers;
				$this->singular_event_label = tribe_get_event_label_singular();
				$this->plural_event_label = tribe_get_event_label_plural();
			}

			/**
			 * At the pre_get_post hook detect if we should redirect to a particular instance
			 * for an invalid 404 recurrence entries.
			 *
			 * @return void
			 */
			public function detect_recurrence_redirect() {
				global $wp_query, $wp;
				if ( ! isset( $wp_query->query_vars['eventDisplay'] ) ) {
					return false;
				}

				$current_url = null;

				switch ( $wp_query->query_vars['eventDisplay'] ) {
					case 'single-event':
						// a recurrence event with a bad date will throw 404 because of WP_Query limiting by date range
						if ( is_404() || empty( $wp_query->query['eventDate'] ) ) {
							$recurrence_check = array_merge( array( 'posts_per_page' => -1 ), $wp_query->query );
							unset( $recurrence_check['eventDate'] );
							unset( $recurrence_check['tribe_events'] );

							// retrieve event object
							$get_recurrence_event = new WP_Query( $recurrence_check );
							// if a reccurence event actually exists then proceed with redirection
							if ( ! empty( $get_recurrence_event->posts ) && tribe_is_recurring_event( $get_recurrence_event->posts[0]->ID ) && get_post_status( $get_recurrence_event->posts[0] ) == 'publish' ) {
								$current_url = Tribe__Events__Main::instance()->getLink( 'all', $get_recurrence_event->posts[0]->ID );
							}
							break;
						}

						// A child event should be using its parent's slug. If it's using its own, redirect.
						if ( tribe_is_recurring_event( get_the_ID() ) && '' !== get_option( 'permalink_structure' ) ) {
							$event = get_post( get_the_ID() );
							if ( ! empty( $event->post_parent ) ) {
								if ( isset( $wp_query->query['name'] ) && $wp_query->query['name'] == $event->post_name ) {
									$current_url = get_permalink( $event->ID );
								}
							}
						}
						break;

				}

				if ( ! empty( $current_url ) ) {
					// redirect user with 301
					$confirm_redirect = apply_filters( 'tribe_events_pro_detect_recurrence_redirect', true, $wp_query->query_vars['eventDisplay'] );
					do_action( 'tribe_events_pro_detect_recurrence_redirect', $wp_query->query_vars['eventDisplay'] );
					if ( $confirm_redirect ) {
						wp_safe_redirect( $current_url, 301 );
						exit;
					}
				}
			}

			public function filter_canonical_link_on_recurring_events() {
				if ( is_singular( Tribe__Events__Main::POSTTYPE ) && get_query_var( 'eventDate' ) && has_action( 'wp_head', 'rel_canonical' ) ) {
					remove_action( 'wp_head', 'rel_canonical' );
					add_action( 'wp_head', array( $this, 'output_recurring_event_canonical_link' ) );
				}
			}

			public function output_recurring_event_canonical_link() {
				// set the EventStartDate so Tribe__Events__Main can filter the permalink appropriately
				$post = get_post( get_queried_object_id() );
				$post->EventStartDate = get_query_var( 'eventDate' );

				// use get_post_permalink instead of get_permalink so that the post isn't converted
				// back to an ID, then to a post again (without the EventStartDate)
				$link = get_post_permalink( $post );

				echo "<link rel='canonical' href='" . esc_url( $link ) . "' />\n";
			}

			/**
			 * Loop through recurrence posts array and find out the next recurring instance from right now
			 *
			 * @param WP_Post[] $event_list
			 *
			 * @return int
			 */
			public function get_last_recurrence_id( $event_list ) {
				global $wp_query;

				$event_list = empty( $event_list ) ? $wp_query->posts : $event_list;
				$right_now = current_time( 'timestamp' );
				$next_recurrence = 0;

				// find next recurrence date by loop
				foreach ( $event_list as $key => $event ) {
					if ( $right_now < strtotime( $event->EventStartDate ) ) {
						$next_recurrence = $event;
					}
				}
				if ( empty( $next_recurrence ) && ! empty( $event_list ) ) {
					$next_recurrence = reset( $event_list );
				}

				return apply_filters( 'tribe_events_pro_get_last_recurrence_id', $next_recurrence->ID, $event_list, $right_now );
			}

			/**
			 * Common library plugins have been activated. Functions that need to be applied afterwards can be added here.
			 *
			 * @return void
			 */
			public function helpersLoaded() {
				remove_action( 'widgets_init', 'tribe_related_posts_register_widget' );
				if ( class_exists( 'TribeRelatedPosts' ) ) {
					TribeRelatedPosts::instance();
					require_once( $this->pluginPath . 'vendor/tribe-related-posts/template-tags.php' );
				}
			}

			/**
			 * Determines whether or not to show the custom fields metabox for events.
			 *
			 * @return bool Whether to show or not.
			 */
			public function displayMetaboxCustomFields() {
				$show_box = tribe_get_option( 'disable_metabox_custom_fields' );
				if ( $show_box == 'show' ) {
					return true;
				}
				if ( $show_box == 'hide' ) {
					remove_post_type_support( Tribe__Events__Main::POSTTYPE, 'custom-fields' );
					return false;
				}
				if ( empty( $show_box ) ) {
					global $wpdb;
					$meta_keys = $wpdb->get_results(
						"SELECT DISTINCT pm.meta_key FROM $wpdb->postmeta pm
										LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id
										WHERE p.post_type = '" . Tribe__Events__Main::POSTTYPE . "'
										AND pm.meta_key NOT LIKE '_wp_%'
										AND pm.meta_key NOT IN (
											'_edit_last',
											'_edit_lock',
											'_thumbnail_id',
											'_EventConference',
											'_EventAllDay',
											'_EventHideFromUpcoming',
											'_EventOrigin',
											'_EventShowMap',
											'_EventVenueID',
											'_EventShowMapLink',
											'_EventCost',
											'_EventOrganizerID',
											'_EventRecurrence',
											'_EventStartDate',
											'_EventEndDate',
											'_EventDuration',
											'_FacebookID')"
					);
					if ( empty( $meta_keys ) ) {
						remove_post_type_support( Tribe__Events__Main::POSTTYPE, 'custom-fields' );
						$show_box = 'hide';
						$r = false;
					} else {
						$show_box = 'show';
						$r = true;
					}

					tribe_update_option( 'disable_metabox_custom_fields', $show_box );

					return $r;
				}

			}

			/**
			 * Add the default settings tab
			 *
			 * @return void
			 */
			public function add_settings_tabs() {
				require_once( $this->pluginPath . 'src/admin-views/tribe-options-defaults.php' );
				new Tribe__Events__Settings_Tab( 'defaults', __( 'Default Content', 'tribe-events-calendar-pro' ), $defaultsTab );
				// The single-entry array at the end allows for the save settings button to be displayed.
				new Tribe__Events__Settings_Tab( 'additional-fields', __( 'Additional Fields', 'tribe-events-calendar-pro' ), array(
					'priority' => 35,
					'fields'   => array( null ),
				) );
			}

			public function filter_settings_tab_fields( $fields, $tab ) {
				$this->singular_event_label = tribe_get_event_label_singular();
				$this->plural_event_label = tribe_get_event_label_plural();
				switch ( $tab ) {
					case 'display':
						$fields = Tribe__Events__Main::array_insert_after_key(
							'tribeDisableTribeBar', $fields, array(
								'hideRelatedEvents' => array(
									'type'            => 'checkbox_bool',
									'label'           => __( 'Hide related events', 'tribe-events-calendar-pro' ),
									'tooltip'         => __( 'Remove related events from the single event view', 'tribe-events-calendar-pro' ),
									'default'         => false,
									'validation_type' => 'boolean',
								),
							)
						);
						$fields = Tribe__Events__Main::array_insert_after_key(
							'monthAndYearFormat', $fields, array(
								'weekDayFormat' => array(
									'type' => 'text',
									'label' => __( 'Week Day Format', 'tribe-events-calendar-pro' ),
									'tooltip' => __( 'Enter the format to use for week days. Used when showing days of the week in Week view.', 'tribe-events-calendar-pro' ),
									'default' => 'D jS',
									'size' => 'medium',
									'validation_type' => 'html',
								),
							)
						);
						$fields = Tribe__Events__Main::array_insert_after_key(
							'hideRelatedEvents', $fields, array(
								'week_view_hide_weekends' => array(
									'type'            => 'checkbox_bool',
									'label'           => __( 'Hide weekends on Week View', 'tribe-events-calendar-pro' ),
									'tooltip'         => __( 'Check this to only show weekdays on Week View', 'tribe-events-calendar-pro' ),
									'default'         => false,
									'validation_type' => 'boolean',
								),
							)
						);
						break;
				}

				return $fields;
			}

			/**
			 * Add the "Getting Started" text to the help tab for PRO addon.
			 *
			 * @return string The modified content.
			 */
			public function add_help_tab_getting_started_text() {
				$getting_started_text[] = sprintf( __( "Thanks for buying Events Calendar PRO! From all of us at Modern Tribe, we sincerely appreciate it. If you're looking for help with Events Calendar PRO, you've come to the right place. We are committed to helping make your calendar be spectacular... and hope the resources provided below will help get you there.", 'tribe-events-calendar-pro' ) );
				$content = implode( $getting_started_text );

				return $content;
			}

			/**
			 * Add the intro text that concerns PRO to the help tab.
			 *
			 * @return string The modified content.
			 */
			public function add_help_tab_intro_text() {
				$intro_text[] = '<p>' . __( "If this is your first time using The Events Calendar Pro, you're in for a treat and are already well on your way to creating a first event. Here are some basics we've found helpful for users jumping into it for the first time:", 'tribe-events-calendar-pro' ) . '</p>';
				$intro_text[] = '<ul>';
				$intro_text[] = '<li>';
				$intro_text[] = sprintf( __( '%sOur New User Primer%s was designed for folks in your exact position. Featuring both step-by-step videos and written walkthroughs that feature accompanying screenshots, the primer aims to take you from zero to hero in no time.', 'tribe-events-calendar-pro' ), '<a href="http://m.tri.be/4t" target="blank">', '</a>' );
				$intro_text[] = '</li><li>';
				$intro_text[] = sprintf( __( '%sInstallation/Setup FAQs%s from our support page can help give an overview of what the plugin can and cannot do. This section of the FAQs may be helpful as it aims to address any basic install questions not addressed by the new user primer.', 'tribe-events-calendar-pro' ), '<a href="http://m.tri.be/4u" target="blank">', '</a>' );
				$intro_text[] = '</li><li>';
				$intro_text[] = sprintf( __( "Take care of your license key. Though not required to create your first event, you'll want to get it in place as soon as possible to guarantee your access to support and upgrades. %sHere's how to find your license key%s, if you don't have it handy.", 'tribe-events-calendar-pro' ), '<a href="http://m.tri.be/4v" target="blank">', '</a>' );
				$intro_text[] = '</li></ul><p>';
				$intro_text[] = __( "Otherwise, if you're feeling adventurous, you can get started by heading to the Events menu and adding your first event.", 'tribe-events-calendar-pro' );
				$intro_text[] = '</p>';
				$intro_text = implode( $intro_text );

				return $intro_text;
			}

			/**
			 * Add help text regarding the Tribe forums to the help tab.
			 *
			 * @return string The content.
			 */
			public function add_help_tab_forumtext() {
				$forum_text[] = '<p>' . sprintf( __( 'Written documentation can only take things so far...sometimes, you need help from a real person. This is where our %ssupport forums%s come into play.', 'tribe-events-calendar-pro' ), '<a href="http://m.tri.be/4w/" target="blank">', '</a>' ) . '</p>';
				$forum_text[] = '<p>' . sprintf( __( "Users who have purchased an Events Calendar PRO license are granted total access to our %spremium support forums%s. Unlike at the %sWordPress.org support forum%s, where our involvement is limited to identifying and patching bugs, we have a dedicated support team for PRO users. We're on the PRO forums daily throughout the business week, and no thread should go more than 24-hours without a response.", 'tribe-events-calendar-pro' ), '<a href="http://m.tri.be/4w/" target="blank">', '</a>', '<a href="http://wordpress.org/support/plugin/the-events-calendar" target="blank">', '</a>' ) . '</p>';
				$forum_text[] = '<p>' . __( "Our number one goal is helping you succeed, and to whatever extent possible, we'll help troubleshoot and guide your customizations or tweaks. While we won't build your site for you, and we can't guarantee we'll be able to get you 100% integrated with every theme or plugin out there, we'll do all we can to point you in the right direction and to make you -- and your client, as is often more importantly the case -- satisfied.", 'tribe-events-calendar-pro' ) . '</p>';
				$forum_text[] = '<p>' . __( "Before posting a new thread, please do a search to make sure your issue hasn't already been addressed. When posting please make sure to provide as much detail about the problem as you can (with screenshots or screencasts if feasible), and make sure that you've identified whether a plugin / theme conflict could be at play in your initial message.", 'tribe-events-calendar-pro' ) . '</p>';
				$forum_text = implode( $forum_text );

				return $forum_text;
			}

			/**
			 * If the user has chosen to replace default values, set up
			 * the Pro class to read those defaults from options
			 *
			 * @param Tribe__Events__Default_Values $strategy
			 * @return Tribe__Events__Default_Values
			 */
			public function set_default_value_strategy( $strategy ) {
				if ( tribe_get_option( 'defaultValueReplace' ) ) {
					$strategy = new Tribe__Events__Pro__Default_Values();
				}
				return $strategy;
			}

			/**
			 * Add rewrite routes for custom PRO stuff and views.
			 *
			 * @param Tribe__Events__Rewrite $rewrite The Tribe__Events__Rewrite object
			 *
			 * @return void
			 */
			public function filter_add_routes( $rewrite ) {
				$rewrite
					->archive( array( '{{ week }}' ), array( 'eventDisplay' => 'week' ) )
					->archive( array( '{{ week }}', '(\d{2})' ), array( 'eventDisplay' => 'week', 'eventDate' => '%1' ) )
					->archive( array( '{{ week }}', '(\d{4}-\d{2}-\d{2})' ), array( 'eventDisplay' => 'week', 'eventDate' => '%1' ) )

					->tax( array( '{{ week }}' ), array( 'eventDisplay' => 'week' ) )
					->tax( array( '{{ week }}', '(\d{4}-\d{2}-\d{2})' ), array( 'eventDisplay' => 'week', 'eventDate' => '%2' ) )

					->tag( array( '{{ week }}' ), array( 'eventDisplay' => 'week' ) )
					->tag( array( '{{ week }}', '(\d{4}-\d{2}-\d{2})' ), array( 'eventDisplay' => 'week', 'eventDate' => '%2' ) )

					->archive( array( '{{ photo }}' ), array( 'eventDisplay' => 'photo' ) )
					->archive( array( '{{ photo }}', '(\d{4}-\d{2}-\d{2})' ), array( 'eventDisplay' => 'photo', 'eventDate' => '%1' ) )

					->tax( array( '{{ photo }}' ), array( 'eventDisplay' => 'photo' ) )
					->tag( array( '{{ photo }}' ), array( 'eventDisplay' => 'photo' ) );
			}

			/**
			 * Add the required bases for the Pro Views
			 * @param  array $bases  Bases that are already set
			 * @return array         The modified version of the array of bases
			 */
			public function filter_add_base_slugs( $bases = array() ) {
				// For translations purpose we add this as a string not required to assign it to a variable
				__( 'week', 'tribe-events-calendar-pro' );
				__( 'photo', 'tribe-events-calendar-pro' );

				$bases['week'] = (array) 'week';
				$bases['photo'] = (array) 'photo';

				return $bases;
			}

			/**
			 * We add the Pro to the Tranlations domains
			 *
			 * @param  array $bases  Domains that are already set
			 * @return array         The modified version of the array of domains
			 */
			public function filter_add_i18n_pro_domain( $domains = array() ) {
				$domains['tribe-events-calendar-pro'] = $this->pluginDir . 'lang/';

				return $domains;
			}

			/**
			 * Adds the proper css class(es) to the body tag.
			 *
			 * @param array $classes The current array of body classes.
			 *
			 * @return array The modified array of body classes.
			 * @TODO move this to template class
			 */
			public function body_class( $classes ) {
				global $wp_query;

				// @TODO do we really need all these array_diff()s?

				if ( $wp_query->tribe_is_event_query ) {
					if ( $wp_query->tribe_is_week ) {
						$classes[] = ' tribe-events-week';
						// remove the default gridview class from core
						$classes = array_diff( $classes, array( 'events-gridview' ) );
					}
					if ( $wp_query->tribe_is_photo ) {
						$classes[] = ' tribe-events-photo';
						// remove the default gridview class from core
						$classes = array_diff( $classes, array( 'events-gridview' ) );
					}
					if ( $wp_query->tribe_is_map ) {
						$classes[] = ' tribe-events-map';
						// remove the default gridview class from core
						$classes = array_diff( $classes, array( 'events-gridview' ) );
					}
					if ( tribe_is_map() || ! tribe_get_option( 'hideLocationSearch', false ) ) {
						$classes[] = ' tribe-events-uses-geolocation';
					}

					if (
						! empty( $wp_query->query['tribe_events'] )
						&& 'custom-recurrence' === $wp_query->query['tribe_events']
						&& ! empty( $wp_query->query['eventDisplay'] )
						&& 'all' === $wp_query->query['eventDisplay']
					) {
						$classes[] = ' tribe-events-recurrence-archive';
					}
				}

				return $classes;
			}

			/**
			 * Set PRO query flags
			 *
			 * @param WP_Query $query The current query object.
			 *
			 * @return WP_Query The modified query object.
			 **/
			public function parse_query( $query ) {
				$query->tribe_is_week = false;
				$query->tribe_is_photo = false;
				$query->tribe_is_map = false;
				$query->tribe_is_event_pro_query = false;
				if ( ! empty( $query->query_vars['eventDisplay'] ) ) {
					$query->tribe_is_event_pro_query = true;
					switch ( $query->query_vars['eventDisplay'] ) {
						case 'week':
							$query->tribe_is_week = true;
							break;
						case 'photo':
							$query->tribe_is_photo = true;
							break;
						case 'map':
							/*
							* Query setup for the map view is located in
							* Tribe__Events__Pro__Geo_Loc->setup_geoloc_in_query()
							*/
							$query->tribe_is_map = true;
							break;
					}
				}
			}

			/**
			 * Add custom query modification to the pre_get_posts hook as necessary for PRO.
			 *
			 * @param WP_Query $query The current query object.
			 *
			 * @return WP_Query The modified query object.
			 */
			public function pre_get_posts( $query ) {
				if ( $query->is_single() && $query->get( 'eventDate' ) ) {
					$this->set_post_id_for_recurring_event_query( $query );
				}
				if ( ! empty( $query->tribe_is_event_pro_query ) ) {
					switch ( $query->query_vars['eventDisplay'] ) {
						case 'week':

							$start_date = tribe_get_first_week_day( $query->get( 'eventDate' ) );
							$end_date   = tribe_get_last_week_day( $start_date );

							// if the setting to hide weekends is true
							if ( tribe_get_option( 'week_view_hide_weekends', false ) == true ) {
								$start_of_week = get_option( 'start_of_week' );
								// check if the week is set to start on a weekend day
								// If so, start on the next weekday.
								// 0 = Sunday, 6 = Saturday
								if ( $start_of_week == 0 || $start_of_week == 6 ) {
									$start_date = date( Tribe__Events__Date_Utils::DBDATEFORMAT, strtotime( $start_date . ' +1 Weekday' ) );
								}
								// If the week starts on saturday or friday
								// sunday and/or saturday would be on the other end, so we need to end the previous weekday
								// 5 = Friday, 6 = Saturday
								if ( $start_of_week == 5 || $start_of_week == 6 ) {
									$end_date = date( Tribe__Events__Date_Utils::DBDATEFORMAT, strtotime( $end_date . ' -1 Weekday' ) );
								}
							}

							// if the setting to hide weekends is on
							// need to filter the query
							// need to only show 5 days on the week view

							// if we're using an non-default hour range on week view
							if ( has_filter( 'tribe_events_week_get_hours' ) ) {
								$start_date .= ' ' . tribe_events_week_get_hours( 'first-hour' );
								$end_date .= ' ' . tribe_events_week_get_hours( 'last-hour' );
							}

							$query->set( 'eventDate', $start_date  );
							$query->set( 'start_date', $start_date );
							$query->set( 'end_date', $end_date );
							$query->set( 'posts_per_page', -1 ); // show ALL week posts
							$query->set( 'hide_upcoming', false );
							break;
						case 'photo':
							$query->set( 'hide_upcoming', false );
							break;
						case 'all':
							new Tribe__Events__Pro__Recurrence__Event_Query( $query );
							break;
					}
					apply_filters( 'tribe_events_pro_pre_get_posts', $query );
				}
			}

			/**
			 * A recurring event will have the base post's slug in the
			 * 'name' query var. We need to remove that and replace it
			 * with the correct post's ID
			 *
			 * @param WP_Query $query
			 *
			 * @return void
			 */
			private function set_post_id_for_recurring_event_query( $query ) {
				$date = $query->get( 'eventDate' );
				$slug = $query->get( 'name' );
				if ( empty( $date ) || empty( $slug ) ) {
					return; // we shouldn't be here
				}
				$cache = new Tribe__Events__Cache();
				$post_id = $cache->get( 'single_event_' . $slug . '_' . $date, 'save_post' );
				if ( ! empty( $post_id ) ) {
					unset( $query->query_vars['name'] );
					unset( $query->query_vars['tribe_events'] );
					$query->set( 'p', $post_id );

					return;
				}
				global $wpdb;
				$parent_sql = "SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type=%s";
				$parent_sql = $wpdb->prepare( $parent_sql, $slug, Tribe__Events__Main::POSTTYPE );
				$parent_id = $wpdb->get_var( $parent_sql );

				$parent_start = get_post_meta( $parent_id, '_EventStartDate', true );
				if ( empty( $parent_start ) ) {
					return; // how does this series not have a start date?
				} else {
					$parent_start_date = date( 'Y-m-d', strtotime( $parent_start ) );
					$parent_start_time = date( 'H:i:s', strtotime( $parent_start ) );
				}

				if ( $parent_start_date == $date ) {
					$post_id = $parent_id;
				} else {
					$child_sql = "SELECT ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_EventStartDate' WHERE p.post_parent=%d AND p.post_type=%s AND m.meta_value=%s";
					$child_sql = $wpdb->prepare( $child_sql, $parent_id, Tribe__Events__Main::POSTTYPE, $date.' '.$parent_start_time );
					$post_id = $wpdb->get_var( $child_sql );
				}

				if ( $post_id ) {
					unset( $query->query_vars['name'] );
					unset( $query->query_vars['tribe_events'] );
					$query->set( 'p', $post_id );
					$cache->set( 'single_event_' . $slug . '_' . $date, $post_id, Tribe__Events__Cache::NO_EXPIRATION, 'save_post' );
				}
			}

			/**
			 * Get the path to the current events template.
			 *
			 * @param string $template The current template path.
			 *
			 * @return string The modified template path.
			 */
			public function select_page_template( $template ) {
				// venue view
				if ( is_singular( Tribe__Events__Main::VENUE_POST_TYPE ) ) {
					$template = Tribe__Events__Templates::getTemplateHierarchy( 'pro/single-venue' );
				}
				// organizer view
				if ( is_singular( Tribe__Events__Main::ORGANIZER_POST_TYPE ) ) {
					$template = Tribe__Events__Templates::getTemplateHierarchy( 'pro/single-organizer' );
				}
				// week view
				if ( tribe_is_week() ) {
					$template = Tribe__Events__Templates::getTemplateHierarchy( 'pro/week' );
				}

				// photo view
				if ( tribe_is_photo() ){
					$template = Tribe__Events__Templates::getTemplateHierarchy( 'pro/photo' );
				}

				// map view
				if ( tribe_is_map() ) {
					$template = Tribe__Events__Templates::getTemplateHierarchy( 'pro/map' );
				}

				// recurring "all" view
				if ( tribe_is_showing_all() ) {
					$template = Tribe__Events__Templates::getTemplateHierarchy( 'list' );
				}

				return $template;
			}

			/**
			 * Check the ajax request action looking for pro views
			 *
			 * @param $is_ajax_view_request bool
			 */
			public function is_pro_ajax_view_request( $is_ajax_view_request, $view ) {

				// if a particular view wasn't requested, or this isn't an ajax request, or there was no action param in the request, don't continue
				if ( $view == false || ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || empty( $_REQUEST['action'] ) ) {
					return $is_ajax_view_request;
				}

				switch ( $view ) {
					case 'map' :
						$is_ajax_view_request = ( $_REQUEST['action'] == Tribe__Events__Pro__Templates__Map::AJAX_HOOK );
						break;

					case 'photo' :
						$is_ajax_view_request = ( $_REQUEST['action'] == Tribe__Events__Pro__Templates__Photo::AJAX_HOOK );
						break;

					case 'week' :
						$is_ajax_view_request = ( $_REQUEST['action'] == Tribe__Events__Pro__Templates__Week::AJAX_HOOK );
						break;
				}

				return $is_ajax_view_request;

			}

			/**
			 * Specify the PHP class for the current page template
			 *
			 * @param string $class The current class we are filtering.
			 *
			 * @return string The class.
			 */
			public function get_current_template_class( $class ) {

				// venue view
				if ( is_singular( Tribe__Events__Main::VENUE_POST_TYPE ) ) {
					$class = 'Tribe__Events__Pro__Templates__Single_Venue';
				} // organizer view
				elseif ( is_singular( Tribe__Events__Main::ORGANIZER_POST_TYPE ) ) {
					$class = 'Tribe__Events__Pro__Templates__Single_Organizer';
				} // week view
				elseif ( tribe_is_week() || tribe_is_ajax_view_request( 'week' ) ) {
					$class = 'Tribe__Events__Pro__Templates__Week';
				} // photo view
				elseif ( tribe_is_photo() || tribe_is_ajax_view_request( 'photo' ) ) {
					$class = 'Tribe__Events__Pro__Templates__Photo';
				} // map view
				elseif ( tribe_is_map() || tribe_is_ajax_view_request( 'map' ) ) {
					$class = 'Tribe__Events__Pro__Templates__Map';
				}

				return $class;

			}

			/**
			 * Add premium plugin paths for each file in the templates array
			 *
			 * @param $template_paths array
			 *
			 * @return array
			 */
			public function template_paths( $template_paths = array() ) {

				$template_paths['pro'] = $this->pluginPath;

				return $template_paths;

			}

			/**
			 * Add premium plugin paths for each file in the templates array
			 *
			 * @param $template_class_path string
			 *
			 * @return array
			 **/
			public function template_class_path( $template_class_paths = array() ) {

				$template_class_paths[] = $this->pluginPath.'/lib/template-classes/';

				return $template_class_paths;

			}

			/**
			 * Enqueues the necessary JS for the admin side of things.
			 *
			 * @return void
			 */
			public function admin_enqueue_scripts() {
				wp_enqueue_script( 'handlebars', $this->pluginUrl . '/vendor/handlebars/handlebars.min.js', array(), apply_filters( 'tribe_events_pro_js_version', self::VERSION ), true );
				wp_enqueue_script( 'moment', $this->pluginUrl . '/vendor/momentjs/moment.min.js', array(), apply_filters( 'tribe_events_pro_js_version', self::VERSION ), true );
				wp_enqueue_script( Tribe__Events__Main::POSTTYPE . '-premium-admin', tribe_events_pro_resource_url( 'events-admin.js' ), array( 'jquery-ui-datepicker' ), apply_filters( 'tribe_events_pro_js_version', self::VERSION ), true );
				wp_enqueue_script( Tribe__Events__Main::POSTTYPE . '-premium-recurrence', tribe_events_pro_resource_url( 'events-recurrence.js' ), array( Tribe__Events__Main::POSTTYPE.'-premium-admin', 'handlebars', 'moment' ), apply_filters( 'tribe_events_pro_js_version', self::VERSION ), true );
				$data = apply_filters( 'tribe_events_pro_localize_script', array(), 'TribeEventsProAdmin', Tribe__Events__Main::POSTTYPE.'-premium-admin' );
				wp_localize_script( Tribe__Events__Main::POSTTYPE . '-premium-admin', 'TribeEventsProAdmin', $data );
				wp_localize_script( Tribe__Events__Main::POSTTYPE . '-premium-admin', 'tribe_events_pro_recurrence_strings', array(
					'date' => Tribe__Events__Pro__Recurrence_Meta::date_strings(),
					'recurrence' => Tribe__Events__Pro__Recurrence_Meta::recurrence_strings(),
					'exclusion' => array(),
				) );
			}

			public function admin_enqueue_styles() {
				wp_enqueue_style( Tribe__Events__Main::POSTTYPE . '-premium-admin', tribe_events_pro_resource_url( 'events-admin.css' ), array(), apply_filters( 'tribe_events_pro_css_version', self::VERSION ) );
			}

			/**
			 * Enqueue the proper styles depending on what is requred by a given page load.
			 *
			 * @return void
			 */
			public function enqueue_styles() {

				if ( tribe_is_event_query()
				     || is_active_widget( false, false, 'tribe-events-adv-list-widget' )
				     || is_active_widget( false, false, 'tribe-mini-calendar' )
				     || is_active_widget( false, false, 'tribe-events-countdown-widget' )
				     || is_active_widget( false, false, 'next_event' )
				     || is_active_widget( false, false, 'tribe-events-venue-widget' )
				) {

					Tribe__Events__Pro__Template_Factory::asset_package( 'events-pro-css' );

				}
			}

			/**
			 * Enqueue the proper PRO scripts as necessary.
			 *
			 * @return void
			 */
			public function enqueue_pro_scripts() {
				if ( tribe_is_event_query() ) {
					// @TODO filter the tribe_events_resource_url() function
					$path = Tribe__Events__Pro__Template_Factory::getMinFile( tribe_events_pro_resource_url( 'tribe-events-pro.js' ), true );
					wp_enqueue_script(
						'tribe-events-pro',
						$path,
						array(
							'jquery',
							'tribe-events-calendar-script',
						),
						apply_filters( 'tribe_events_pro_js_version', self::VERSION ),
						false
					);

					$geoloc = Tribe__Events__Pro__Geo_Loc::instance();

					$data = array(
						'geocenter' => $geoloc->estimate_center_point(),
						'map_tooltip_event' => __( 'Event: ', 'tribe-events-calendar-pro' ),
						'map_tooltip_address' => __( 'Address: ', 'tribe-events-calendar-pro' ),
					);

					$data = apply_filters( 'tribe_events_pro_localize_script', $data, 'Tribe__Events__Pro__Main', 'tribe-events-pro' );

					wp_localize_script( 'tribe-events-pro', 'TribeEventsPro', $data );

				}
			}

			/**
			 * Sets up to add the query variable for hiding subsequent recurrences of recurring events on the frontend.
			 *
			 * @param WP_Query $query The current query object.
			 *
			 * @return WP_Query The modified query object.
			 */
			public function setup_hide_recurrence_in_query( $query ) {

				if ( ! isset( $query->query_vars['is_tribe_widget'] ) || ! $query->query_vars['is_tribe_widget'] ){
					// don't hide any recurrences on the all recurrences view
					if ( tribe_is_showing_all() || tribe_is_week() || tribe_is_month() || tribe_is_day() ) {
						return $query;
					}
				}

				// don't hide any recurrences in the admin
				if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
					return $query;
				}

				// don't override an explicitly passed value
				if ( isset( $query->query_vars['tribeHideRecurrence'] ) ) {
					return $query;
				}

				// if the admin option is set to hide recurrences, or the user option is set
				if ( $this->should_hide_recurrence( $query ) ) {
					$query->query_vars['tribeHideRecurrence'] = 1;
				}

				return $query;
			}

			/**
			 * Returns whether or not we show only the first instance of each recurring event in listview
			 *
			 * @param WP_Query $query The current query object.
			 *
			 * @return boolean
			 */
			public function should_hide_recurrence( $query = null ) {
				// let's not hide recurrence if we are showing all recurrence events
				if ( tribe_is_showing_all() ) {
					return false;
				}

				// let's not hide recurrence if we are showing all recurrence events via AJAX
				if ( ! empty( $_GET['tribe_post_parent'] ) ) {
					return false;
				}

				// let's not hide recurrence if we are showing all recurrence events via AJAX
				if ( ! empty( $_POST['tribe_post_parent'] ) ) {
					return false;
				}

				// let's not hide recurrence if we are on month or week view
				if (
					is_object( $query )
					&& ! empty( $query->query['eventDisplay'] )
					&& in_array( $query->query['eventDisplay'], array( 'month', 'week' ) )
				) {
					return false;
				}

				// let's HIDE recurrence events if we've set the option
				if ( tribe_get_option( 'hideSubsequentRecurrencesDefault', false ) ) {
					return true;
				}

				// let's HIDE recurrence events if tribeHideRecurrence via GET
				if ( isset( $_GET['tribeHideRecurrence'] ) && 1 == $_GET['tribeHideRecurrence'] ) {
					return true;
				}

				// let's HIDE recurrence events if tribeHideRecurrence via POST
				if ( isset( $_POST['tribeHideRecurrence'] ) && 1 == $_POST['tribeHideRecurrence'] ) {
					return true;
				}

				return false;
			}//end should_hide_recurrence

			/**
			 * Return the forums link as it should appear in the help tab.
			 *
			 * @return string
			 */
			public function helpTabForumsLink( $content ) {
				if ( get_option( 'pue_install_key_events_calendar_pro ' ) ) {
					return 'http://m.tri.be/4x';
				} else {
					return 'http://m.tri.be/4w';
				}
			}

			/**
			 * Return additional action for the plugin on the plugins page.
			 *
			 * @return array
			 */
			public function addLinksToPluginActions( $actions ) {
				if ( class_exists( 'TribeEvents' ) ) {
					$url = add_query_arg(
						array(
							'post_type' => Tribe__Events__Main::POSTTYPE,
							'page'      => 'tribe-events-calendar',
						),
						admin_url( 'edit.php' )
					);

					$actions['settings'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'tribe-events-calendar-pro' ) . '</a>';
				}

				return $actions;
			}

			/**
			 * Adds thumbnail/featured image support to Organizers and Venues when PRO is activated.
			 *
			 * @param array $post_type_args The current register_post_type args.
			 *
			 * @return array The new register_post_type args.
			 */
			public function addSupportsThumbnail( $post_type_args ) {
				$post_type_args['supports'][] = 'thumbnail';

				return $post_type_args;
			}

			/**
			 * Enable "view post" links on metaposts
			 *
			 * @param $messages array
			 * return array
			 */
			public function updatePostMessages( $messages ) {
				global $post, $post_ID;

				$messages[ Tribe__Events__Main::VENUE_POST_TYPE ][1] = sprintf( __( 'Venue updated. <a href="%s">View venue</a>', 'tribe-events-calendar-pro' ), esc_url( get_permalink( $post_ID ) ) );
				/* translators: %s: date and time of the revision */
				$messages[ Tribe__Events__Main::VENUE_POST_TYPE ][6] = sprintf( __( 'Venue published. <a href="%s">View venue</a>', 'tribe-events-calendar-pro' ), esc_url( get_permalink( $post_ID ) ) );
				$messages[ Tribe__Events__Main::VENUE_POST_TYPE ][8] = sprintf( __( 'Venue submitted. <a target="_blank" href="%s">Preview venue</a>', 'tribe-events-calendar-pro' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) );
				$messages[ Tribe__Events__Main::VENUE_POST_TYPE ][9]  = sprintf(
					__( 'Venue scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview venue</a>', 'tribe-events-calendar-pro' ),
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'tribe-events-calendar-pro' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) )
				);
				$messages[ Tribe__Events__Main::VENUE_POST_TYPE ][10] = sprintf( __( 'Venue draft updated. <a target="_blank" href="%s">Preview venue</a>', 'tribe-events-calendar-pro' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) );

				$messages[ Tribe__Events__Main::ORGANIZER_POST_TYPE ][1] = sprintf( __( 'Organizer updated. <a href="%s">View organizer</a>', 'tribe-events-calendar' ), esc_url( get_permalink( $post_ID ) ) );
				$messages[ Tribe__Events__Main::ORGANIZER_POST_TYPE ][6] = sprintf( __( 'Organizer published. <a href="%s">View organizer</a>', 'tribe-events-calendar' ), esc_url( get_permalink( $post_ID ) ) );
				$messages[ Tribe__Events__Main::ORGANIZER_POST_TYPE ][8] = sprintf( __( 'Organizer submitted. <a target="_blank" href="%s">Preview organizer</a>', 'tribe-events-calendar' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) );
				$messages[ Tribe__Events__Main::ORGANIZER_POST_TYPE ][9]  = sprintf(
					__( 'Organizer scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview organizer</a>', 'tribe-events-calendar' ),
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'tribe-events-calendar' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) )
				);
				$messages[ Tribe__Events__Main::ORGANIZER_POST_TYPE ][10] = sprintf( __( 'Organizer draft updated. <a target="_blank" href="%s">Preview organizer</a>', 'tribe-events-calendar' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) );

				return $messages;

			}

			/**
			 * Includes and handles registration/de-registration of the advanced list widget. Idea from John Gadbois.
			 *
			 * @return void
			 */
			public function pro_widgets_init() {
				unregister_widget( 'Tribe__Events__List_Widget' );
				register_widget( 'Tribe__Events__Pro__Advanced_List_Widget' );
				register_widget( 'Tribe__Events__Pro__Countdown_Widget' );
				register_widget( 'Tribe__Events__Pro__Mini_Calendar_Widget' );
				register_widget( 'Tribe__Events__Pro__Venue_Widget' );
			}

			/**
			 * Load textdomain for localization
			 *
			 * @return void
			 */
			public function loadTextDomain() {
				load_plugin_textdomain( 'tribe-events-calendar-pro', false, $this->pluginDir . 'lang/' );
			}

			/**
			 * Re-registers the custom post types for venues so they allow search from the frontend.
			 *
			 * @return void
			 */
			public function allow_cpt_search() {
				$tec = Tribe__Events__Main::instance();
				$venue_args = $tec->getVenuePostTypeArgs();
				$venue_args['exclude_from_search'] = false;
				register_post_type( Tribe__Events__Main::VENUE_POST_TYPE, apply_filters( 'tribe_events_register_venue_type_args', $venue_args ) );
			}

			/**
			 * Add meta links on the plugins page.
			 *
			 * @param array $links The current array of links to display.
			 * @param string $file The plugin to add meta links to.
			 *
			 * @return array The modified array of links to display.
			 */
			public function addMetaLinks( $links, $file ) {
				if ( $file == $this->pluginDir . 'events-calendar-pro.php' ) {
					$anchor = __( 'Support', 'tribe-events-calendar-pro' );
					$links[] = '<a href="http://m.tri.be/4z">' . $anchor . '</a>';
					$anchor = __( 'View All Add-Ons', 'tribe-events-calendar-pro' );
					$links[] = '<a href="http://m.tri.be/50">' . $anchor . '</a>';
				}

				return $links;
			}

			/**
			 * Add support for ugly links for ugly links with PRO views.
			 *
			 * @param string $eventUrl The current URL.
			 * @param string $type The type of endpoint/view whose link was requested.
			 * @param string $secondary More data that is necessary for generating the link.
			 *
			 * @return string The ugly-linked URL.
			 */
			public function ugly_link( $eventUrl, $type, $secondary ) {
				switch ( $type ) {
					case 'week':
						$eventUrl = add_query_arg( 'post_type', Tribe__Events__Main::POSTTYPE, home_url() );
						// if we're on an Event Cat, show the cat link, except for home.
						if ( $type !== 'home' && is_tax( Tribe__Events__Main::TAXONOMY ) ) {
							$eventUrl = add_query_arg( Tribe__Events__Main::TAXONOMY, get_query_var( 'term' ), $eventUrl );
						}
						$eventUrl = add_query_arg( array( 'eventDisplay' => $type ), $eventUrl );
						if ( $secondary ) {
							$eventUrl = add_query_arg( array( 'eventDate' => $secondary ), $eventUrl );
						}
						break;
					case 'photo':
					case 'map':
						$eventUrl = add_query_arg( array( 'eventDisplay' => $type ), $eventUrl );
						break;
					case 'all':
						remove_filter(
							'post_type_link', array(
							$this->permalink_editor,
							'filter_recurring_event_permalinks',
						), 10, 4
						);
						$post_id = $secondary ? $secondary : get_the_ID();
						$parent_id = wp_get_post_parent_id( $post_id );
						if ( ! empty( $parent_id ) ) {
							$post_id = $parent_id;
						}
						$eventUrl = add_query_arg( 'eventDisplay', 'all', get_permalink( $post_id ) );
						add_filter(
							'post_type_link', array(
							$this->permalink_editor,
							'filter_recurring_event_permalinks',
						), 10, 4
						);
						break;
					default:
						break;
				}

				return apply_filters( 'tribe_events_pro_ugly_link', $eventUrl, $type, $secondary );
			}

			/**
			 * filter Tribe__Events__Main::getLink for pro views
			 *
			 * @param  string $eventUrl
			 * @param  string $type
			 * @param  string $secondary
			 * @param  string $term
			 *
			 * @return string
			 */
			public function get_link( $eventUrl, $type, $secondary, $term ) {
				switch ( $type ) {
					case 'week':
						$eventUrl = trailingslashit( esc_url_raw( $eventUrl . $this->weekSlug ) );
						if ( ! empty( $secondary ) ) {
							$eventUrl = esc_url_raw( trailingslashit( $eventUrl ) . $secondary );
						}
						break;
					case 'photo':
						$eventUrl = trailingslashit( esc_url_raw( $eventUrl . $this->photoSlug ) );
						if ( ! empty( $secondary ) ) {
							$eventUrl = esc_url_raw( trailingslashit( $eventUrl ) . $secondary );
						}
						break;
					case 'map':
						$eventUrl = trailingslashit( esc_url_raw( $eventUrl . Tribe__Events__Pro__Geo_Loc::instance()->rewrite_slug ) );
						if ( ! empty( $secondary ) ) {
							$eventUrl = esc_url_raw( trailingslashit( $eventUrl ) . $secondary );
						}
						break;
					case 'all':
						remove_filter(
							'post_type_link', array(
							$this->permalink_editor,
							'filter_recurring_event_permalinks',
						), 10, 4
						);
						$post_id = $secondary ? $secondary : get_the_ID();
						$post_id = wp_get_post_parent_id( $post_id );
						$eventUrl = trailingslashit( get_permalink( $post_id ) );
						$eventUrl = trailingslashit( esc_url_raw( $eventUrl . 'all' ) );
						add_filter(
							'post_type_link', array(
							$this->permalink_editor,
							'filter_recurring_event_permalinks',
						), 10, 4
						);
						break;
					default:
						break;
				}

				return apply_filters( 'tribe_events_pro_get_link', $eventUrl, $type, $secondary, $term );
			}

			/**
			 * When showing All events for a recurring event, override the default link
			 *
			 * @param string $link Current page link
			 *
			 * @return string Recurrence compatible current page link
			 */
			public function get_all_link( $link ) {
				if ( ! tribe_is_showing_all() && ! isset( $_POST['tribe_post_parent'] ) ) {
					return $link;
				}

				return $this->get_link( null, 'all', null, null );
			}//end get_all_link

			/**
			 * When showing All events for a recurring event, override the default directional link to
			 * view "all" rather than "list"
			 *
			 * @param string $link Current page link
			 *
			 * @return string Recurrence compatible current page link
			 */
			public function get_all_dir_link( $link ) {
				if ( ! tribe_is_showing_all() && ! isset( $_POST['tribe_post_parent'] ) ) {
					return $link;
				}

				$link = preg_replace( '#tribe_event_display=list#', 'tribe_event_display=all', $link );

				return $link;
			}//end get_all_dir_link

			/**
			 * If an ajax request has come in with tribe_post_parent, make sure we limit results
			 * to by post_parent
			 *
			 * @param array $args Arguments for fetching events on the listview template
			 * @param array $posted_data POST data from listview ajax request
			 *
			 * @return array
			 */
			public function override_listview_get_event_args( $args, $posted_data ) {
				if ( empty( $posted_data['tribe_post_parent'] ) ) {
					return $args;
				}

				$args['post_parent'] = absint( $posted_data['tribe_post_parent'] );

				return $args;
			}//end override_listview_get_event_args

			/**
			 * overrides the "displaying" setting of the Tribe__Events__Main instance if we are displaying
			 * "all" recurring events"
			 *
			 * @param string $displaying The current eventDisplay value
			 * @param array $args get_event args used to fetch events that are visible in the ajax rendered listview
			 *
			 * @return string
			 */
			public function override_listview_display_setting( $displaying, $args ) {
				if ( empty( $args['post_parent'] ) ) {
					return $displaying;
				}

				return 'all';
			}//end override_listview_display_setting

			/**
			 * Add week view to the views selector in the tribe events bar.
			 *
			 * @param array $views The current array of views registered to the tribe bar.
			 *
			 * @return array The views registered with week view added.
			 */
			public function setup_weekview_in_bar( $views ) {
				$views[] = array(
					'displaying'     => 'week',
					'anchor'     => __( 'Week', 'tribe-events-calendar-pro' ),
					'event_bar_hook'       => 'tribe_events_week_before_template',
					'url'            => tribe_get_week_permalink(),
				);

				return $views;
			}

			/**
			 * Add photo view to the views selector in the tribe events bar.
			 *
			 * @param array $views The current array of views registered to the tribe bar.
			 *
			 * @return array The views registered with photo view added.
			 */
			public function setup_photoview_in_bar( $views ) {
				$views[] = array(
					'displaying'     => 'photo',
					'anchor'     => __( 'Photo', 'tribe-events-calendar-pro' ),
					'event_bar_hook'       => 'tribe_events_before_template',
					'url'            => tribe_get_photo_permalink(),
				);

				return $views;
			}

			/**
			 * Change the datepicker label, depending on what view the user is on.
			 *
			 * @param string $caption The current caption for the datepicker.
			 *
			 * @return string The new caption.
			 */
			public function setup_datepicker_label ( $caption ) {
				if ( tribe_is_week() ) {
					$caption = __( 'Week Of', 'tribe-events-calendar-pro' );
				}

				return $caption;
			}

			/**
			 * Echo the setting for hiding subsequent occurrences of recurring events to frontend.
			 *
			 * @return void
			 */
			public function add_recurring_occurance_setting_to_list () {
				if ( tribe_get_option( 'userToggleSubsequentRecurrences', true ) && ! tribe_is_showing_all() && ( tribe_is_upcoming() || tribe_is_past() || tribe_is_map() || tribe_is_photo() ) || apply_filters( 'tribe_events_display_user_toggle_subsequent_recurrences', false ) ) {
					echo tribe_recurring_instances_toggle();
				}
			}

			/**
			 * Returns he ratio of kilometers to miles.
			 *
			 * @return float The ratio.
			 */
			public function kms_to_miles_ratio() {
				return 0.621371;
			}

			/**
			 * Returns he ratio of miles to kilometers.
			 *
			 * @return float The ratio.
			 */
			public function miles_to_kms_ratio() {
				return 1.60934;
			}

			public function init_apm_filters() {
				new Tribe__Events__Pro__APM_Filters__APM_Filters();
				new Tribe__Events__Pro__APM_Filters__Date_Filter();
				new Tribe__Events__Pro__APM_Filters__Recur_Filter();
				new Tribe__Events__Pro__APM_Filters__Content_Filter();
				new Tribe__Events__Pro__APM_Filters__Title_Filter();
				new Tribe__Events__Pro__APM_Filters__Venue_Filter();
				new Tribe__Events__Pro__APM_Filters__Organizer_Filter();
			}


			/**
			 * plugin deactivation callback
			 * @see register_deactivation_hook()
			 *
			 * @param bool $network_deactivating
			 */
			public static function deactivate( $network_deactivating ) {
				if ( ! class_exists( 'Tribe__Events__Main' ) ) {
					return; // can't do anything since core isn't around
				}
				$deactivation = new Tribe__Events__Pro__Deactivation( $network_deactivating );
				add_action( 'shutdown', array( $deactivation, 'deactivate' ) );
			}

			/**
			 * The singleton function.
			 *
			 * @return Tribe__Events__Pro__Main The instance.
			 */
			public static function instance() {
				if ( ! isset( self::$instance ) ) {
					$className = __CLASS__;
					self::$instance = new $className;
				}

				return self::$instance;
			}


		} // end Class
	}
