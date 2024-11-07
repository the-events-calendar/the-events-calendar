import { fetch } from "../controls";
import { hydrate } from "./actions";
import { getResourcePath } from "./utils";

export function* getSettings() {
	const settings = yield fetch(getResourcePath());

	if (settings) {
		return hydrate(settings);
	}

	return;
}
