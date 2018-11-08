/**
 * External dependencies
 */
import { createSelector } from 'reselect';

export const classicSelector = ( state ) => state.events.blocks.classic;

export const detailsTitleSelector = createSelector(
	[ classicSelector ],
	( values ) => values.detailsTitle,
);

export const organizerTitleSelector = createSelector(
	[ classicSelector ],
	( values ) => values.organizerTitle,
);
