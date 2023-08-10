/**
 * External dependencies
 */
import { get } from 'lodash';
import { createSelector } from 'reselect';
import { mapsAPI } from '@moderntribe/common/utils/globals';

export const getMapEmbed = () => get( mapsAPI(), 'embed', true );
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
	[ venueBlockSelector, getMapEmbed ],
	( block, embed ) => embed && block.showMap,
);
