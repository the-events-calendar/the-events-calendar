import {ConfigurationSchema} from "../types/ConfigurationSchema";
import {WebPackConfiguration} from "../types/WebPackConfiguration";
import {FileCallbackArguments} from "../types/FileCallbackArguments";


function fileMatcher({fileName}: FileCallbackArguments): boolean {
	return !fileName.startsWith('_');
}

function entryPointName({fileRelativePath}: FileCallbackArguments): string {
	return 'css/' + fileRelativePath.replace('.pcss', '');
}

/**
 * PostCSS files in the `src/modules` directory use PostCSS nesting, where `&` indicates "this".
 * By default WordPress scripts would use new CSS nesting syntax where `&` indicates the parent.
 * We add here the `postcss-nested` plugin to allow the use of `&` to mean "this".
 * In webpack loaders are applied in LIFO order: this will prepare the PostCSS for the default `postcss-loader`.
 */
function modifyConfig(config: WebPackConfiguration): void {
	config.module.rules.push(
		{
			test: /src\/modules\/.*?\.pcss$/,
			use: [
				{
					loader: 'postcss-loader',
					options: {
						postcssOptions: {
							plugins: [
								'postcss-nested',
							],
						},
					},
				},
			],
		}
	);
}


export const TECPostCss: ConfigurationSchema = {
	fileExtensions: ['.pcss'],
	fileMatcher,
	entryPointName,
	modifyConfig
}
