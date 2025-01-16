export type WebPackRule = {
	test?: RegExp
	issuer?: RegExp,
	use?: string | string[] | {
		loader?: string
		options?: object
	}[],
	type: 'javascript/auto' |
		'javascript/dynamic' |
		'javascript/esm' |
		'json' |
		'webassembly/sync' |
		'webassembly/async' |
		'asset' |
		'asset/source' |
		'asset/resource' |
		'asset/inline' |
		'css/auto'
};
