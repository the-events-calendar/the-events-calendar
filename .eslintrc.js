const eslintConfig = require( '@wordpress/scripts/config/.eslintrc.js' );

module.exports = {
	...eslintConfig,
	overrides: [
		...eslintConfig.overrides,
	],
	globals: {
		...eslintConfig.globals,
		wp: true,
		jQuery: true,
		React: true,
		JSX: true,
		adminpage: true,
		ajaxurl: true,
		DateFormatter: true,
		google: true,
		moment: true,
		pagenow: true,
		Qs: true,
		tec_debug: true,
		tribe: true,
		tribe_debug: true,
		tribe_dynamic_help_text: true,
		tribe_ev: true,
		tribe_events_bar_action: true,
		tribe_js_config: true,
		tribe_timezone_update: true,
		tribe_tmpl: true,
		tribe_upgrade: true,
		TribeCalendar: true,
		tribeDateFormat: true,
		tribeEventsSingleMap: true,
		TribeList: true,
		tribeUtils: true,
		typenow: true,
		TribeEventsAdminNoticeInstall: true
	},
};
