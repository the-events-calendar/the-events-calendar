import {FileCallbackArguments} from "./FileCallbackArguments";
import {ExposeCallbackArguments} from './ExposeCallbackArguments';
import {WebPackConfiguration} from "./WebPackConfiguration";

export type ConfigurationSchema = {
	fileExtensions: string[],
	fileMatcher?: (arguments: FileCallbackArguments) => boolean,
	entryPointName?: (arguments: FileCallbackArguments) => string,
	expose?: (arguments: ExposeCallbackArguments) => string | false,
	recursive?: boolean,
	modifyConfig?: (config: WebPackConfiguration) => void
};
