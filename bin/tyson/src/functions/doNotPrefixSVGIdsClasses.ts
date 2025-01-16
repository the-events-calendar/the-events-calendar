import {prependRuleToRuleInConfig} from "./prependRuleToRuleInConfig";
import {ruleUsesLoader} from "./ruleUsesLoader";
import {WebPackConfiguration} from "../types/WebPackConfiguration";

/**
 * Prepends a loader for SVG files that will be applied after the default one. Loaders are applied
 * in a LIFO queue in WebPack.
 * By default `@wordpress/scripts` uses `@svgr/webpack` to handle SVG files and, together with it,
 * the default SVGO (package `svgo/svgo-loader`) configuration that includes the `prefixIds` plugin.
 * To avoid `id` and `class` attribute conflicts, the `prefixIds` plugin would prefix all `id` and
 * `class` attributes in SVG tags with a generated prefix. This would break TEC classes (already
 * namespaced) so here we prepend a rule to handle SVG files in the `src/modules` directory by
 * disabling the `prefixIds` plugin.
 *
 * @param {Object} config The WebPack configuration to update.
 */
export function doNotPrefixSVGIdsClasses(config: WebPackConfiguration): void {
	/*
	 * Prepends a loader for SVG files that will be applied after the default one. Loaders are applied
	 * in a LIFO queue in WebPack.
	 * By default `@wordpress/scripts` uses `@svgr/webpack` to handle SVG files and, together with it,
	 * the default SVGO (package `svgo/svgo-loader`) configuration that includes the `prefixIds` plugin.
	 * To avoid `id` and `class` attribute conflicts, the `prefixIds` plugin would prefix all `id` and
	 * `class` attributes in SVG tags with a generated prefix. This would break TEC classes (already
	 * namespaced) so here we prepend a rule to handle SVG files in the `src/modules` directory by
	 * disabling the `prefixIds` plugin.
	 */
	prependRuleToRuleInConfig(config, {
		test: /\/src\/modules\/.*?\.svg$/,
		issuer: /\.(j|t)sx?$/,
		use: [
			{
				loader: '@svgr/webpack',
				options: {
					svgoConfig: {
						plugins: [
							{
								name: 'prefixIds',
								params: {
									prefixIds: false,
									prefixClassNames: false,
								},
							},
						],
					},
				},
			},
			{
				loader: 'url-loader',
			},
		],
		type: 'javascript/auto',
	}, (rule) => ruleUsesLoader(rule, '@svgr/webpack'));
}

