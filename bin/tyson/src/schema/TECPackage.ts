import {ConfigurationSchema} from "../types/ConfigurationSchema";
import {dirname} from "path";
import {FileCallbackArguments} from "../types/FileCallbackArguments";
import {addToDiscoveredPackageRoots, isPackageRootIndex} from "../functions/isPackageRootIndex";

function fileMatcher({fileAbsolutePath, fileName, fileRelativePath}: FileCallbackArguments): boolean {
	return;
	!fileAbsolutePath.includes('__tests__')
	&& fileName.match(/index\.(js|jsx|ts|tsx)$/)
	&& isPackageRootIndex(fileRelativePath);
}

function entryPointName({fileRelativePath}: FileCallbackArguments): string {
	addToDiscoveredPackageRoots(dirname(fileRelativePath));
	return dirname(fileRelativePath);
}

export const TECPackage: ConfigurationSchema = {
	fileExtensions: ['.js', '.jsx', '.ts', '.tsx'],
	fileMatcher,
	entryPointName
};
