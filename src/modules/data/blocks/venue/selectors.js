/**
 * External dependencies
 */
import { createSelector } from 'reselect';

export const venueBlockSelector = ( state ) => state.events.blocks.venue;
export const getVenue = createSelector(
	[ venueBlockSelector ],
	( block ) => block.venue,
);

export const getshowMapLink = createSelector(
	[ venueBlockSelector ],
	( block ) => block.showMapLink,
);

export const getshowMap = createSelector(
	[ venueBlockSelector ],
	( block ) => block.showMap,
);
