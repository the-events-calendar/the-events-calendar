import {ConfigurationSchema} from "../types/ConfigurationSchema";
import {FileCallbackArguments} from "../types/FileCallbackArguments";
import {ExposeCallbackArguments} from "../types/ExposeCallbackArguments";
import {buildExternalName} from "../functions/buildExternalName";

function fileMatcher({fileName, fileRelativePath}: FileCallbackArguments): boolean {
	return !(fileName.endsWith('.min.js') || fileRelativePath.includes('__tests__'))
}

function entryPointName({fileRelativePath}: FileCallbackArguments): string {
	return 'js/' + fileRelativePath.replace('.js', '');
}

function expose({entryPointName, fileAbsolutePath}: ExposeCallbackArguments): string | false {
	// From 'js/customizer-views-v2-live-preview' to  'tec.customizerViewsV2LivePreview'.
	// From 'js/tec-update-6.0.0-notice' to 'tec.tecUpdate600Notice'.
	return fileAbsolutePath.match(/frontend\.js$/) ?
		false
		: buildExternalName('tec', entryPointName, ['js']);
}

export const TECLegacyJs: ConfigurationSchema = {
		fileExtensions: ['.js'],
		fileMatcher,
		entryPointName,
		expose
	}
;
