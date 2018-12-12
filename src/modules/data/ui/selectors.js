/**
 * External dependencies
 */
import { createSelector } from 'reselect';

export const getUI = ( state ) => state.events.ui;

export const getDashboardDateTimeOpen = createSelector(
	[ getUI ],
	( ui ) => ui.dashboardDateTimeOpen
);

export const getDashboardPriceOpen = createSelector(
	[ getUI ],
	( ui ) => ui.dashboardPriceOpen
);

export const getVisibleMonth = createSelector(
	[ getUI ],
	( ui ) => ui.visibleMonth
);
