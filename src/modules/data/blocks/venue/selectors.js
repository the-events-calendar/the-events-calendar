/**
 * External dependencies
 */
import { get } from 'lodash';
import { createSelector } from 'reselect';
import { mapsAPI } from '@moderntribe/common/utils/globals';

export const getMapEmbed = () => get( mapsAPI(), 'embed', true );
export const venueBlockSelector = ( state ) => state.events.blocks.venue.blocks.core;

export const getVenueByClientId = ( state, props ) => state.events.blocks.venue.blocks.byId[ props.clientId ];

export const getVenuesById = ( state ) => state.events.blocks.venue.blocks.byId;

export const getVenuesInBlock = ( state ) => state.events.blocks.venue.blocks.allIds;

export const getshowMapLink = createSelector( [ venueBlockSelector ], ( block ) => block.showMapLink );

export const getshowMap = createSelector(
	[ venueBlockSelector, getMapEmbed ],
	( block, embed ) => embed && block.showMap
);
