import {ConfigurationSchema} from "../types/ConfigurationSchema";
import {basename, dirname} from "path";
import {FileCallbackArguments} from "../types/FileCallbackArguments";

function fileMatcher({fileName}: FileCallbackArguments): boolean {
	return fileName === 'frontend.pcss';
}

function entryPointName({fileRelativePath}: FileCallbackArguments): string {
	return 'app/' + basename(dirname(fileRelativePath)) + '/frontend';
}

export const TECLegacyBlocksFrontendPostCss: ConfigurationSchema = {
	fileExtensions: ['.pcss'],
	fileMatcher,
	entryPointName
}
