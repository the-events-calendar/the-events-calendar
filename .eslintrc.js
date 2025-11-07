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
				camelcase: [
					'error',
					{
						properties: 'never',
						ignoreDestructuring: true,
						ignoreImports: true,
						ignoreGlobals: true,
						allow: [
							'^tribe_',
							'^TRIBE_',
							'[a-z]+_[a-z]+',
						],
					},
				],
				'no-var': 'off',
				eqeqeq: 'off',
				'no-bitwise': 'off',
				'no-unused-vars': [
					'error',
					{
						args: 'none', // Don't check unused function arguments.
						vars: 'all',
						varsIgnorePattern: '^_',
						argsIgnorePattern: '^_',
					},
				],
				'jsdoc/require-returns-description': 'off',
				'jsdoc/require-param-type': 'off',
				'jsdoc/check-line-alignment': 'off',
				'no-unused-expressions': 'off',
				'no-shadow': 'off',
				'prefer-const': 'off',
				'no-redeclare': 'off',
				'react-hooks/rules-of-hooks': 'off',
				'@wordpress/no-unused-vars-before-return': 'off',
				'@wordpress/no-global-active-element': 'off',
				'no-console': 'off',
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
