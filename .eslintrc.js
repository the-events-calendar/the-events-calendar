const eslintConfig = require( '@wordpress/scripts/config/.eslintrc.js' );

module.exports = {
	...eslintConfig,

	ignorePatterns: [
		'node_modules/**',
		'vendor/**',
		'**/*.min.js',
	],

	overrides: [
		...eslintConfig.overrides,

		// Disable dependency false positives in our internal JS modules.
		{
			files: [ 'src/**/*.js', 'common/**/*.js' ],
			rules: {
				'import/no-extraneous-dependencies': 'off',
			},
			settings: {
				'import/resolver': {
					node: {
						paths: [ 'src', 'common' ],
						extensions: [ '.js', '.jsx' ],
					},
				},
			},
		},

		// Relax rules for legacy files with WordPress naming conventions.
		{
			files: [ 'src/resources/js/**/*.js' ],
			globals: {
				// Browser APIs not available in this context.
				requestAnimationFrame: 'readonly',
				MutationObserver: 'readonly',
			},
			rules: {
				/**
				 * Allows snake_case for legacy WP/TEC vars.
				 * This is reasonable — TEC & WP globals use underscores.
				 */
				camelcase: [
					'error',
					{
						properties: 'never',
						ignoreDestructuring: true,
						ignoreImports: true,
						ignoreGlobals: true,
						allow: [ '^tribe_', '^TRIBE_', '[a-z]+_[a-z]+' ],
					},
				], // ✅ Keep as-is. Priority: 7 (reasonable flexibility for legacy WP code)

				/**
				 * Flags unused vars. Current config allows _var ignores.
				 * That’s fine for intentionally unused vars (React hooks, etc.)
				 */
				'no-unused-vars': [
					'error',
					{
						args: 'none',
						vars: 'all',
						varsIgnorePattern: '^_',
						argsIgnorePattern: '^_',
					},
				], // ✅ Keep. Priority: 8 (standard practice, already WP-like)
				'no-console': 'warn',
			},
		},
		{
			files: [ 'src/resources/packages/**/*.{ts,tsx}' ],
			rules: {
				camelcase: [
					'error',
					{
						properties: 'never',
						ignoreDestructuring: true,
						ignoreImports: true,
						ignoreGlobals: true,
						allow: [ '^tribe_', '^TRIBE_', '^wp_', 'timezone_string', 'date_format', 'start_of_week' ],
					},
				],
			},
		},
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
		jest: true,
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
		TribeEventsAdminNoticeInstall: true,
		// WordPress admin globals
		inlineEditTax: true,
		// jQuery UI globals
		$dpDiv: true,
		$el: true,
		// Underscore/Lodash
		_: true,
		// Additional tribe globals
		tribe_events_linked_posts: true,
		tribe_l10n_datatables: true,
		tribe_ignore_events: true,
		tribe_customizer_controls: true,
		tribe_events_customizer_live_preview_js_config: true,
		tribe_events_event_editor: true,
		tribe_datepicker_opts: true,
	},
};
