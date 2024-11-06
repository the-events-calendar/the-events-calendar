/* Get data from the state/store */

export const getSettings = ( state ) => state.settings || [];

export const getSetting = ( state, id ) => state.settings.find( setting => setting.id === id );

export const getNextSetting = ( state ) => state.activeSetting + 1;

export const getPrevSetting = ( state ) => state.activeSetting - 1;

export const getActiveSetting = ( state ) => state.activeSetting || 0;
