/**
 * External dependencies
 */
import { createSelector } from 'reselect';

export const getWebsiteBlock = ( state ) => state.events.blocks.website;

export const getUrl = createSelector( [ getWebsiteBlock ], ( website ) => website.url );
