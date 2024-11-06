
/* Dispatch actions for the reducers to handle */
import TYPES from "./action-types";
const { INITIALIZE, UPDATE, CREATE, DELETE, HYDRATE, SET_ACTIVE_TAB } = TYPES;

export function createSettings(settings) {
	return {
		type: INITIALIZE,
		settings
	};
}

export function createSetting(setting) {
	return {
		type: CREATE,
		setting
	};
}

export function updateSetting(setting) {
	return {
		type: UPDATE,
		setting
	};
}

export const hydrate = settings => {
	return {
		type: HYDRATE,
		settings
	};
};

export function setActiveSetting(activeSetting) {
	return {
		type: SET_ACTIVE_TAB,
		activeSetting
	};
}
