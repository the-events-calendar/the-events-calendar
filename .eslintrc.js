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

				/**
				 * Documentation alignment nitpickers — not crucial.
				 * Turning these off is fine; doesn’t affect behavior.
				 */
				'jsdoc/require-returns-description': 'off', // 🟡 Optional. Priority: 3 (fine to leave off)
				'jsdoc/require-param-type': 'off', // 🟡 Optional. Priority: 4 (fine to leave off until JS→TS someday)
				'jsdoc/check-line-alignment': 'off', // ✅ Keep off. Priority: 2 (only affects formatting, low ROI)
				/**
				 * Another WP-specific DOM safety rule. Off is fine for now if legacy DOM manipulations exist.
				 */
				'@wordpress/no-global-active-element': 'off', // 🟡 Optional. Priority: 4

				/**
				 * Console logs are fine in dev, but should be warned (not disabled).
				 * Use "warn" instead of "off".
				 */
				'no-console': 'off', // 🔧 Change to 'warn'. Priority: 7 (keep awareness, allow dev logs)
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
