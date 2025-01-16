export function buildExternalName(namespace: string, name: string, dropFrags: string[] = []) {
	if (!namespace) {
		throw new Error('Namespace cannot be empty');
	}

	if (!name) {
		throw new Error('Name cannot be empty');
	}

	// From `/app/feature/turbo-name-v6.0.0-deluxe-edition` to `app.feature.turboNameV600DeluxeEdition`.
	return namespace + '.' + name.split('/').filter(frag => !dropFrags.includes(frag)).map(frag => frag.replace(/[\._-](\w)/g, match => match[1].toUpperCase())).join('.');
}
