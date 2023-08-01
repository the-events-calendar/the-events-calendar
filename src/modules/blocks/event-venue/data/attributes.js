/**
 * Block Attributes
 */
const blockAttributes = {
	venue: {
		type: 'number',
		default: null,
	},
	venues: {
		type: 'array',
		source: 'meta',
		meta: '_EventVenueID',
		default: [],
	},
	showMapLink: {
		type: 'boolean',
		default: true,
	},
	showMap: {
		type: 'boolean',
		default: true,
	},
};

export default blockAttributes;
