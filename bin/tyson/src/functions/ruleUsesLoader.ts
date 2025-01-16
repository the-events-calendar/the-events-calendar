import {WebPackRule} from "../types/WebPackRule";

/**
 * Returns whether an object following the `module.rules` WebPack schema configuration format uses a loader or not.
 *
 * The loader could be still unresolved (e.g. `some-loader`) or resolved to an absolute path
 * (e.g. `/home/User/some-loader/dist/index.js`). For this reason the comparison is not a strict ones,
 * but a `loader.includes(candidate)` one.
 *
 * @param {Object} rule      A rule in the `module.rules` WebPack schema configuration format to check.
 * @param {string} loader    The name of a loader to check.
 *
 * @returns {boolean} Whether the specified rule uses the specified loader or not.
 */
export function ruleUsesLoader(rule: WebPackRule, loader: string): boolean {
	if (!rule.use) {
		// Not all rules will define a `use` property, so we can simply return false here.
		return false;
	}

	// The rule.use property is a string.
	if (typeof rule.use === 'string' && rule.use.includes(loader)) {
		return true;
	}

	if (!Array.isArray(rule.use)) {
		// If it's not an array, we cannot continue searching for our loader, so we can return false here.
		return false;
	}

	for (let i = 0; i < rule.use.length; i++) {
		const use = rule.use[i];

		if (typeof use === 'string') {
			if (use.includes(loader)) {
				return true;
			}

			continue;
		}

		if (typeof use === 'object') {
			if (use?.loader?.includes(loader)) {
				return true;
			}
			continue;
		}
	}

	return false;
}
