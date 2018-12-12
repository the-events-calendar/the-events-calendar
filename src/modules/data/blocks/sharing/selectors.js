/**
 * External dependencies
 */
import { createSelector } from 'reselect';

export const sharingSelector = ( state ) => state.events.blocks.sharing;

export const googleCalendarLabelSelector = createSelector(
	[ sharingSelector ],
	( values ) => values.googleCalendarLabel,
);

export const hasGooglecalendarLabel = createSelector(
	[ sharingSelector ],
	( values ) => values.hasGoogleCalendar,
);

export const iCalLabelSelector = createSelector(
	[ sharingSelector ],
	( values ) => values.iCalLabel,
);

export const hasIcalSelector = createSelector(
	[ sharingSelector ],
	( values ) => values.hasiCal,
);
