const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const {readdirSync, statSync, existsSync} = require('fs');
const {dirname, basename, extname} = require('path');
const {
	TECLegacyJs,
	TECPostCss,
	TECLegacyBlocksFrontendPostCss,
	TECPackage,
	compileCustomEntryPoints,
	exposeEntry,
	doNotPrefixSVGIdsClasses,
	WindowAssignPropertiesPlugin
} = require('@stellarwp/tyson');

const customEntryPoints = compileCustomEntryPoints({
	'/src/resources/js': TECLegacyJs,
	'/src/resources/postcss': TECPostCss,
	'/src/styles': TECLegacyBlocksFrontendPostCss,
	'/src/resources/packages': TECPackage,
}, defaultConfig);
// Blocks from `/src/modules/index.js` are built to `/build/app/main.js`.
customEntryPoints['app/main'] = exposeEntry('tec.app.main', __dirname + '/src/modules/index.js');
customEntryPoints['app/widgets'] = exposeEntry('tec.app.widgets', __dirname + '/src/modules/widgets/index.js');

doNotPrefixSVGIdsClasses(defaultConfig);

module.exports = {
	...defaultConfig,
	...{
		entry: (buildType) => {
			const defaultEntryPoints = defaultConfig.entry(buildType);
			return {
				...defaultEntryPoints, ...customEntryPoints,
			};
		},
		output: {
			...defaultConfig.output,
			...{
				enabledLibraryTypes: ['window'],
			},
		},
		plugins: [
			...defaultConfig.plugins,
			new WindowAssignPropertiesPlugin(),
		],
	},
};

// @todo what to do with images moved/copied to /build/images?
// images from the `src/resources/images` directory that are referenced in files (e.g. PCSS)
// that are compiled are optimized and moved to the `build/images` directory. There is "duplication"
// where the original image (from `src/resources/images`) is shipped when only the build one (from
// `build/images` is actually used.
// @todo detect images in `src/resources/images` that are used outside of compiled files.

// @todo what to do with -rtl styles?
// I _think_ they should be handled at the Assets level, loading the RTL styles on top of the existing
// styles when `is_rtl` is true.
