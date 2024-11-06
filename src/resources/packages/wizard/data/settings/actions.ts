
/* Dispatch actions for the reducers to handle */
import TYPES from "./action-types";
const { INITIALIZE, CREATE, UPDATE, HYDRATE } = TYPES;

export function initializeSettings(settings) {
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

export function updateSettings(settings) {
	return {
		type: UPDATE,
		settings
	};
}

export const hydrate = settings => {
	return {
		type: HYDRATE,
		settings
	};
};
