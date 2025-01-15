const {validate} = require('schema-utils');

const schema = {
	type: 'object',
	properties: {
		lineStart: {
			type: 'string',
			description: 'The line prefix that will be used to start the line in the compiled file.',
		},
	},
	additionalProperties: false,
};

class WindowAssignPropertiesPlugin {
	static defaultOptions = {
		name: 'WindowAssignPropertiesPlugin',
		lineStart: '/******/        '
	}

	constructor(options = {}) {
		validate(options, schema, {
			name: WindowAssignPropertiesPlugin.name,
			baseDataPath: 'options',
		});
		this.options = {...WindowAssignPropertiesPlugin.defaultOptions, ...options};
	}

	apply(compiler) {
		const {RawSource} = require('webpack-sources');
		const pluginName = WindowAssignPropertiesPlugin.name;
		const lineStart = this.options.lineStart;

		compiler.hooks.compilation.tap(pluginName, (compilation) => {
			compilation.hooks.processAssets.tap(
				{name: pluginName, stage: compiler.webpack.Compilation.PROCESS_ASSETS_STAGE_ADDITION},
				(assets) => {
					Object.entries(assets).forEach(([pathname, source]) => {
						if (!pathname.match(/(t|j)sx?$/)) {
							return;
						}

						const updatedSource = source.source().replace(/window\["__tyson_window\.(?<path>[^\]]*?)"]/gi, function(match, path) {
							// From `acme.product.feature.package` to `['acme', 'product', 'feature', 'package']`.
							const pathFrags = path.split('.');

							// To `['acme']['product']['feature']['package']`.
							const arrayPath = pathFrags.map(pathFrag => `['${pathFrag}']`).join('');

							/*
							 * window['acme'] = window['acme'] || {};
							 * window['acme']['product'] = window['acme']['product'] || {};
							 * window['acme']['product']['feature'] = window['acme']['product']['feature'] || {};
							 * window['acme']['product']['feature']['package'] = __webpack_exports__;
							 */
							const assignments = pathFrags.slice(0, -1).map((value, index, array) => {
								const windowPath = array.slice(0, index + 1).map(p => `['${p}']`).join('');
								return `window${windowPath} = window${windowPath} || {};`;
							}).join(`\n${lineStart}`);

							return `${assignments}\n${lineStart}window${arrayPath}`;
						});
						console.log('updatedSource: ', updatedSource.substring(-200));
						assets[pathname] = new RawSource(updatedSource);
					});
				},
			);
		});
	}
}

module.exports = WindowAssignPropertiesPlugin;
