/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/organizers';

export const removeOrganizerInClassic = ( organizer ) => ( {
	type: types.REMOVE_CLASSIC_ORGANIZERS,
	payload: {
		organizer,
	},
} );

export const addOrganizerInClassic = ( organizer ) => ( {
	type: types.ADD_CLASSIC_ORGANIZERS,
	payload: {
		organizer,
	},
} );

export const addOrganizerInBlock = ( id, organizer ) => ( {
	type: types.ADD_BLOCK_ORGANIZER,
	payload: {
		id,
		organizer,
	},
} );

export const removeOrganizerInBlock = ( id, organizer ) => ( {
	type: types.REMOVE_BLOCK_ORGANIZER,
	payload: {
		id,
		organizer,
	},
} );
