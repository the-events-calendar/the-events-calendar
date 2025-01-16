import {WebPackRule} from "./WebPackRule";

export type WebPackConfiguration = {
	module: {
		rules: WebPackRule[]
	}
}
