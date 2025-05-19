const {path} = require('path');

/**
 * The default configuration coming from the @wordpress/scripts package.
 * Customized following the "Advanced Usage" section of the documentation:
 * See: https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/#advanced-usage
 */
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

const {
  createTECLegacyJs,
  createTECPostCss,
  createTECLegacyBlocksFrontendPostCss,
  createTECPackage,
  compileCustomEntryPoints,
  exposeEntry,
  doNotPrefixSVGIdsClasses,
  WindowAssignPropertiesPlugin,
	resolveExternalToGlobal,
} = require('@stellarwp/tyson');

/**
 * Compile a list of entry points to be compiled to the format used by WebPack to define multiple entry points.
 * This is akin to the compilation system used for multi-page applications.
 * See: https://webpack.js.org/concepts/entry-points/#multi-page-application
 */
const customEntryPoints = compileCustomEntryPoints({
  /**
   * All existing Javascript files will be compiled to ES6, most will not be changed at all,
   * minified and cleaned up.
   * This is mostly a pass-thru with the additional benefit that the compiled packages will be
   * exposed on the `window.tec.events` object.
   * E.g. the `src/resources/js/admin-ignored-events.js` file will be compiled to
   * `/build/js/admin-ignored-events.js` and exposed on `window.tec.events.adminIgnoredEvents`.
   */
  '/src/resources/js': createTECLegacyJs('tec.events'),

  /**
   * Compile, recursively, the PostCSS file using PostCSS nesting rules.
   * By default, the `@wordpress/scripts` configuration would compile files using the CSS
   * nesting syntax (https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_nesting) where
   * the `&` symbol indicates the parent element.
   * The PostCSS syntax followed in TEC files will instead use the `&` symbol to mean "this element".
   * Handling this correctly requires adding a PostCSS processor specific to the PostCSS files that
   * will handle the nesting correctly.
   */
  '/src/resources/postcss': createTECPostCss('tec.events'),

  /**
   * This deals with existing Blocks frontend styles being compiled separately.
   * The main function of this configuration schema is to ensure they are placed correctly.
   */
  '/src/styles': createTECLegacyBlocksFrontendPostCss('tec.events'),

  /**
   * This deals with packages written following modern module-based approaches.
   * These packages are usually not Blocks and require `@wordpress/scripts` to be explicitly
   * instructed about them to compile correctly.
   * To avoid having to list each package, here the configuration schema is used to recursively
   * pick them up and namespace them.
   */
  '/src/resources/packages': createTECPackage('tec.events'),
}, defaultConfig);

/**
 * Following are static entry points, to be included in the build non-recursively.
 * These are built following a modern module approach where the root `index.js` file
 * will include the whole module.
 */

/**
 * Blocks from `/src/modules/index.js` are built to `/build/app/main.js`.
 * The existing Block Editor code is not follow the `block.json` based convention expected by
 * `@wordpress/scripts` so here we explicitly point out the root index.
 */
customEntryPoints['app/main'] = exposeEntry('tec.events.app.main', __dirname + '/src/modules/index.js');

/**
 * Same as above, widgets are built like legacy blocks and are not following the `block.json` convention.
 */
customEntryPoints['app/widgets'] = exposeEntry('tec.events.app.widgets', __dirname + '/src/modules/widgets/index.js');

/**
 * Prepends a loader for SVG files that will be applied after the default one. Loaders are applied
 * in a LIFO queue in WebPack.
 * By default, `@wordpress/scripts` uses `@svgr/webpack` to handle SVG files and, together with it,
 * the default SVGO (package `svgo/svgo-loader`) configuration that includes the `prefixIds` plugin.
 * To avoid `id` and `class` attribute conflicts, the `prefixIds` plugin would prefix all `id` and
 * `class` attributes in SVG tags with a generated prefix. This would break TEC classes (already
 * namespaced) so here we prepend a rule to handle SVG files in the `src/modules` directory by
 * disabling the `prefixIds` plugin.
 */
doNotPrefixSVGIdsClasses(defaultConfig);

/**
 * Finally the customizations are merged with the default WebPack configuration.
 */
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
		externals:[
			...(defaultConfig?.externals || []),
			resolveExternalToGlobal('@tec/common', 'window.tec.common'),
		],
  },
};
