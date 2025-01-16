import {ExposedEntry} from "../types/ExposedEntry";

export function exposeEntry(exposeName: string, path: string): ExposedEntry {
	if (!exposeName.startsWith('window.')) {
		exposeName = `__tyson_window.${exposeName}`;
	}

	return {
		import: path,
		library: {
			name: exposeName,
			type: 'window',
		},
	};
}

