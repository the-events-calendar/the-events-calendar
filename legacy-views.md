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


## Functions/Methods Removed

* `tribe_get_list_widget_events`
* `Tribe__Events__Main::register_list_widget`
* `Tribe__Events__Main::init_day_view` - [BTRIA-620]
* `Tribe__Events__Main::setDisplay`
  * [ ] Investigate Tribe__Events__Main->displaying
* `Tribe__Events__Main::eventQueryVars`
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
  * [ ] Community Events usees this method
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

