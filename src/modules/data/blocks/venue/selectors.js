/**
 * External dependencies
 */
import {difference, get} from 'lodash';
import { createSelector } from 'reselect';
import { mapsAPI } from '@moderntribe/common/utils/globals';

export const getMapEmbed = () => get( mapsAPI(), 'embed', true );
export const venueBlockSelector = ( state ) => state.events.blocks.venue.blocks.core;

export const getVenuesInClassic = ( state ) => state.events.blocks.venue.classic;

export const getVenueByClientId = ( state, props ) =>	state.events.blocks.venue.blocks.byId[ props.clientId ];

export const getVenuesById = ( state ) => state.events.blocks.venue.blocks.byId;

export const getVenuesInBlock = ( state ) => state.events.blocks.venue.blocks.allIds;

export const getMappedVenues = createSelector(
	[ getVenuesInClassic, getVenuesInBlock ],
	( classic, blocks ) => {
		return classic.map( ( id ) => ( {
			id,
			block: difference( [ id ], blocks ).length === 0,
		} ) );
	},
);

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
