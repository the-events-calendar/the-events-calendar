/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/organizers';

export const setOrganizersInClassic = ( organizers ) => ( {
	type: types.SET_CLASSIC_ORGANIZERS,
	payload: {
		organizers,
	},
} );

export const removeOrganizerInClassic = ( id ) => ( {
	type: types.REMOVE_CLASSIC_ORGANIZERS,
	payload: {
		id,
	},
} );

export const addOrganizerInClassic = ( organizer ) => ( {
	type: types.ADD_CLASSIC_ORGANIZERS,
	payload: {
		organizer,
	},
} );

export const addOrganizerInBlock = ( id, organizer ) => ( {
	type: types.ADD_ORGANIZER_BLOCK,
	payload: {
		id,
		organizer,
	},
} );

export const removeOrganizerInBlock = ( id, organizer ) => ( {
	type: types.REMOVE_ORGANIZER_BLOCK,
	payload: {
		id,
		organizer,
	},
} );
