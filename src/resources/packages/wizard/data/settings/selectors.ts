/* Get data from the state/store */

export const getSettings = ( state ) => state.settings || {};

export const getSetting = ( state, key ) => state.settings[key] || false;

export const getIsSaving = ( state ) => state.isSaving || false;

export const getVisitedFields = ( state ) => state.visitedFields || [];

export const getCompletedTabs = ( state ) => state.completedTabs || [];

export const getSkippedTabs = ( state ) => state.skippedTabs || [];
