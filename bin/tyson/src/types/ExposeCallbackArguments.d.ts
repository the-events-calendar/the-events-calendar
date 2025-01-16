import {FileCallbackArguments} from "./FileCallbackArguments";

export type ExposeCallbackArguments = FileCallbackArguments & {
	entryPointName: string
}
