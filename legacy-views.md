# Changelog legacy views removal

## Files Removed

* `src/functions/template-tags/widgets.php`
* `src/admin-views/widget-admin-list.php`
* `src/views/widgets/list-widget.php`
* `src/views/widgets/calendar-widget.php`
* `src/views/day.php`
* `src/views/day/content.php`
* `src/views/day/loop.php`
* `src/views/day/nav.php`
* `src/views/day/single-event.php`
* `src/views/day/title-bar.php`
* `src/views/list.php`
* `src/views/list/content.php`
* `src/views/list/loop.php`
* `src/views/list/nav.php`
* `src/views/list/single-event.php`
* `src/views/list/single-featured.php`
* `src/views/list/title-bar.php`
* `src/views/month.php`
* `src/views/month/content.php`
* `src/views/month/loop-grid.php`
* `src/views/month/mobile.php`
* `src/views/month/nav.php`
* `src/views/month/single-day.php`
* `src/views/month/single-event.php`
* `src/views/month/title-bar.php`
* `src/views/month/tooltip.php`

## Classes Removed

* `Tribe__Events__List_Widget`
* `TribeEventsListWidget`
* `Tribe__Events__Asset__Abstract_Asset`
* `Tribe__Events__Asset__Abstract_Events_Css`
* `Tribe__Events__Asset__Admin`
* `Tribe__Events__Asset__Admin_Menu`
* `Tribe__Events__Asset__Admin_Ui`
* `Tribe__Events__Asset__Ajax_Calendar`
* `Tribe__Events__Asset__Ajax_Dayview`
* `Tribe__Events__Asset__Ajax_List`
* `Tribe__Events__Asset__Bootstrap_Datepicker`
* `Tribe__Events__Asset__Calendar_Script`
* `Tribe__Events__Asset__Chosen`
* `Tribe__Events__Asset__Datepicker`
* `Tribe__Events__Asset__Dialog`
* `Tribe__Events__Asset__Dynamic`
* `Tribe__Events__Asset__Ecp_Plugins`
* `Tribe__Events__Asset__Events_Css`
* `Tribe__Events__Asset__Events_Css_Default`
* `Tribe__Events__Asset__Events_Css_Full`
* `Tribe__Events__Asset__Events_Css_Skeleton`
* `Tribe__Events__Asset__Factory`
* `Tribe__Events__Asset__Jquery_Placeholder`
* `Tribe__Events__Asset__Jquery_Resize`
* `Tribe__Events__Asset__PHP_Date_Formatter`
* `Tribe__Events__Asset__Settings`
* `Tribe__Events__Asset__Smoothness`
* `Tribe__Events__Asset__Tribe_Events_Bar`
* `Tribe__Events__Asset__Tribe_Select2`
* `Tribe__Events__Template__Day`
* `Tribe_Events_Day_Template`
* `Tribe__Events__Template__List`
* `Tribe_Events_List_Template`
* `Tribe__Events__Template__Month`
* `Tribe_Events_Month_Template`
* `Tribe__Template_Factory`
  * [ ] There are usages of this in Event Tickets
* `Tribe_Template_Factory`
* `TribeEventsQuery`
* `TribeEventsTemplates`
* `TribeRecurringEventCleanup`
* `Tribe__Events__Recurring_Event_Cleanup`
* `TribeEventsBar`
* `Tribe__Events__Bar`
  * [ ] `tec.bar` in Events Pro
* `Tribe__Events__Backcompat`
* `Tribe\Events\Views\V2\V1_Compat`
* `Tribe__Events__Admin__Front_Page_View`
* `Tribe__Events__Admin__Notices__Base_Notice`
* `Tribe__Events__Admin__Notices__Notice_Interface`
* `Tribe__Events__Admin__Organizer_Chooser_Meta_Box`
* `Tribe__Events__Advanced_Functions__Register_Meta`
* `Tribe__Events__Aggregator__Record__Facebook`
* `Tribe__Events__Customizer__Front_Page_View`
* `Tribe__Events__Customizer__Text`
* `Tribe__Events__Google_Data_Markup`
* `Tribe__Events__Google_Data_Markup__Event`
* `Tribe__Events__Importer__Admin_Page`
* `Tribe__Events__Importer__Options`
* `Tribe__Events__Importer__Plugin`
* `Tribe__Events__Meta_Factory`
* `Tribe__Events__PUE__Checker`
* `Tribe__Events__PUE__Plugin_Info`
* `Tribe__Events__PUE__Utility`
* `Tribe_Amalgamator`
* `Tribe_Events_Single_Event_Template`
* `Tribe_Meta_Factory`
* `Tribe_PU_PluginInfo`
* `Tribe_Register_Meta`
* `TribeAppShop`
* `TribeDateUtils`
* `TribeEvents`
* `TribeEvents_EmbeddedMaps`
* `TribeEventsAdminList`
* `TribeEventsAPI`
* `TribeEventsCache`
* `TribeEventsCacheListener`
* `TribeEventsImporter_AdminPage`
* `TribeEventsImporter_ColumnMapper`
* `TribeEventsImporter_FileImporter`
* `TribeEventsImporter_FileImporter_Events`
* `TribeEventsImporter_FileImporter_Organizers`
* `TribeEventsImporter_FileImporter_Venues`
* `TribeEventsImporter_FileReader`
* `TribeEventsImporter_FileUploader`
* `TribeEventsImporter_Plugin`
* `TribeEventsOptionsException`
* `TribeEventsPostException`
* `TribeEventsSupport`
* `TribeEventsUpdate`
* `TribeEventsViewHelpers`
* `TribeField`
* `TribeiCal`
* `TribePluginUpdateEngineChecker`
* `TribePluginUpdateUtility`
* `TribeSettings`
* `TribeSettingsTab`
* `TribeValidate`

## Functions/Methods Removed

* `tribe_get_list_widget_events`
* `Tribe__Events__Main::register_list_widget`
* `Tribe__Events__Main::init_day_view` - [BTRIA-620]
* `Tribe__Events__Main::setDisplay`
  * [ ] Investigate `Tribe__Events__Main->displaying`
* `Tribe__Events__Main::eventQueryVars`
* `Tribe__Events__Main::ecpActive`
* `Tribe__Events__Main::dateHelper`
* `Tribe__Events__Main::dateToTimeStamp`
* `Tribe__Events__Main::defaultValueReplaceEnabled`
* `Tribe__Events__Main::addHelpAdminMenuItem`
* `Tribe__Events__Main::getNotices`
* `Tribe__Events__Main::removeNotice`
* `Tribe__Events__Main::isNotice`
* `Tribe__Events__Main::setNotice`
* `Tribe__Events__Main::renderDebug`
* `Tribe__Events__Main::debug`
* `Tribe__Events__Main::truncate`
* `Tribe__Events__Main::saveAllTabsHidden`
* `Tribe__Events__Main::doNetworkSettingTab`
* `Tribe__Events__Main::addNetworkOptionsPage`
* `Tribe__Events__Main::setNetworkOptions`
* `Tribe__Events__Main::getNetworkOption`
* `Tribe__Events__Main::getNetworkOptions`
* `Tribe__Events__Main::setOption`
* `Tribe__Events__Main::setOptions`
* `Tribe__Events__Main::getOption`
* `Tribe__Events__Main::getOptions`
* `Tribe__Events__Main::getTagRewriteSlug`
* `Tribe__Events__Main::getTaxRewriteSlug`
* `Tribe__Events__Main::doHelpTab`
* `Tribe__Events__Main::doSettingTabs`
* `Tribe__Events__Main::array_insert_before_key`
* `Tribe__Events__Main::array_insert_after_key`
* `Tribe__Events__Main::add_post_type_to_edit_term_link`
* `Tribe__Events__Main::prepare_to_fix_tagcloud_links`
* `Tribe__Events__Main::saved_organizers_dropdown`
* `Tribe__Events__Main::saved_venues_dropdown`
* `Tribe__Events__Main::set_meta_factory_global`
* `Tribe__Events__Main::initOptions`
* `Tribe__Events__Main::loadTextDomain`
* `Tribe__Events__Main::common`
* `Tribe__Events__Main::issue_noindex`
* `Tribe__Events__Main::displayEventOrganizerDropdown`
* `Tribe__Events__Main::displayEventVenueDropdown`
* `Tribe__Events__Main::checkAddOnCompatibility`
* `Tribe__Events__Main::maybe_delay_activation_if_outdated_common`
* `Tribe__Events__Main::is_delayed_activation`
* `Tribe__Events__Main::getDateStringShortened`
* `Tribe__Events__Main::get_event_link`
* `Tribe__Events__Main::get_closest_event`
* `Tribe__Events__Main::setPostExceptionThrown`
* `Tribe__Events__Main::getPostExceptionThrown`
* `Tribe__Events__Main::manage_preview_metapost`
* `Tribe__Events__Main::setDashicon`
* `Tribe__Events__Main::printLocalizedAdmin`
* `Tribe__Events__Main::localizeAdmin`
* `Tribe__Events__Main::asset_fixes`
* `Tribe__Events__Main::add_admin_assets`
* `Tribe__Events__Main::loadStyle`
* `Tribe__Events__Main::enqueue_wp_admin_menu_style`
* `Tribe__Events__Main::nextMonth`
  * [ ] Pro makes use of this method
* `Tribe__Events__Main::previousMonth`
  * [ ] Pro makes use of this method
* `Tribe__Events__Main::get_closest_event_where`
* `Tribe__Events__Main::setup_listview_in_bar`
* `Tribe__Events__Main::setup_gridview_in_bar`
* `Tribe__Events__Main::setup_dayview_in_bar`
* `Tribe__Events__Main::setup_date_search_in_bar`
* `Tribe__Events__Main::remove_hidden_views`
* `Tribe__Events__Main::setup_keyword_search_in_bar`
* `Tribe__Events__Main::OrganizerMetaBox`
* `Tribe__Events__Main::VenueMetaBox`
* `Tribe__Events__Main::EventsChooserBox`
* `Tribe__Events__Main::add_new_organizer`
* `Tribe__Events__Main::normalize_organizer_submission`
* `Tribe__Events__Main::get_i18n_strings_for_domains`
* `Tribe__Events__Main::googleMapLink`
* `Tribe__Events__Main::googleCalendarLink`
* `Tribe__Events__Main::fullAddress`
* `Tribe__Events__Main::fullAddressString`
* `Tribe__Events__Main::get_i18n_strings`
* `Tribe__Events__Main::monthNames`
* `Tribe__Events__Main::default_view`
  * [ ] Pro makes use of this method
* `Tribe__Events__Main::redirect_past_upcoming_view_urls`
* `Tribe__Events__Main::getPostTypes`
  * [ ] Community Events makes use, replace with `Tribe__Main::get_post_types()`
* `Tribe__Events__Main::getOrganizerPostTypeArgs`
* `Tribe__Events__Main::getVenuePostTypeArgs`
  * [ ] Pro makes use of this method, replace with `Tribe__Events__Venue::instance()->post_type_args`
* `Tribe__Events__Main::disable_pro`
* `Tribe__Events__Main::template_redirect`
* `Tribe__Events__Main::handle_submit_bar_redirect`
* `Tribe__Events__Main::print_noindex_meta`
  * [ ] Confirm why we might have removed this from our Views.
* `Tribe__Events__Query::init`
* `Tribe__Events__Query::parse_query`
  * [ ] `WP_Query->tribe_is_event`
  * [ ] `WP_Query->tribe_is_multi_posttype`
  * [ ] `WP_Query->eventDisplay`
  * [ ] `WP_Query->tribe_is_event_category`
  * [ ] `WP_Query->tribe_is_event_venue`
  * [ ] `WP_Query->tribe_is_event_organizer`
  * [ ] `WP_Query->tribe_is_event_query`
  * [ ] `WP_Query->tribe_is_past`
* `Tribe__Events__Query::pre_get_posts`
  * [ ] `WP_Query->tribe_suppress_query_filters`
* `Tribe__Events__Query::default_page_on_front`
* `Tribe__Events__Query::multi_type_posts_fields`
* `Tribe__Events__Query::posts_join`
* `Tribe__Events__Query::posts_fields`
* `Tribe__Events__Query::posts_results`
* `Tribe__Events__Query::posts_where`
* `Tribe__Events__Query::posts_orderby_venue_organizer`
* `Tribe__Events__Query::posts_join_venue_organizer`
* `Tribe__Events__Query::posts_distinct`
* `Tribe__Events__Query::posts_orderby`
* `Tribe__Events__Query::set_orderby`
* `Tribe__Events__Query::set_order`
* `Tribe__Events__Query::getHideFromUpcomingEvents`
* `Tribe__Events__Query::getEventCounts`
* `Tribe__Events__Query::last_found_events`
* `Tribe__Events__Query::postmeta_table`
* `Tribe__Events__Query::can_inject_date_field`
* `Tribe__Events__Query::should_remove_date_filters`
* `Tribe\Events\Views\V2\Widgets\Service_Provider::unregister_list_widget`
* `tribe_is_ajax_view_request`
* `tribe_include_view_list`
* `tribe_events_month_has_events_filtered`
* `tribe_events_the_month_single_event_classes`
* `tribe_events_the_month_day_classes`
* `tribe_events_get_current_month_day`
* `tribe_events_get_current_week`
* `tribe_events_the_month_day`
* `tribe_events_have_month_days`
* `tribe_show_month`
* `tribe_get_dropdown_link_prefix`
* `tribe_events_get_filters`
  * [ ] Pro make use of this
* `tribe_events_get_views`
  * [ ] Pro make use of this
* `Tribe__Events__Template_Factory::asset_package`
* `Tribe__Events__Template_Factory::setup_meta`
* `Tribe__Events__Template_Factory::get_asset_factory_instance`
* `Tribe__Events__Template_Factory::handle_asset_package_request`
* `Tribe__Events__Template_Factory::handle_asset_package_request`
* `Tribe__Events__Template_Factory::handle_asset_package_request`
* `Tribe__Events__Template_Factory::handle_asset_package_request`
* `Tribe__Events__Template__Single_Event::setup_meta`
* `tribe_initialize_view`
* `Tribe__Events__Templates::init`
* `Tribe__Events__Templates::templateChooser`
* `Tribe__Events__Templates::instantiate_template_class`
* `Tribe__Events__Templates::maybeSpoofQuery`
* `Tribe__Events__Templates::maybe_modify_global_post_title`
* `Tribe__Events__Templates::modify_global_post_title`
* `Tribe__Events__Templates::restore_global_post_title`
* `Tribe__Events__Templates::spoof_the_post`
* `Tribe__Events__Templates::setup_ecp_template`
* `Tribe__Events__Templates::load_ecp_comments_page_template`
* `Tribe__Events__Templates::load_ecp_into_page_template`
* `Tribe__Events__Templates::setup_ecp_template`
* `Tribe__Events__Templates::spoof_the_post`
* `Tribe__Events__Templates::showInLoops`
* `Tribe__Events__Templates::theme_body_class`
  * [ ] Community Events uses this method
* `Tribe__Events__Templates::needs_compatibility_fix`
* `Tribe__Events__Templates::wpHeadFinished`
* `Tribe__Events__Templates::remove_singular_body_class`
* `Tribe__Events__Templates::add_singular_body_class`
* `Tribe__Events__Templates::template_body_class`
* `Tribe__Events__Templates::get_current_page_template`
* `Tribe__Events__Templates::restoreQuery`
* `Tribe__Events__Templates::spoof_the_post`

## Hooks Removed

* `tribe_events_list_widget_before_the_event_image`
* `tribe_events_list_widget_thumbnail_size`
* `tribe_events_list_widget_featured_image_link`
* `tribe_events_list_widget_after_the_event_image`
* `tribe_events_list_widget_before_the_event_title`
* `tribe_events_list_widget_after_the_event_title`
* `tribe_events_list_widget_before_the_meta`
* `tribe_events_list_widget_after_the_meta`

