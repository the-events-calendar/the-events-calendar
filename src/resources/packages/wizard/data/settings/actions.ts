
/* Dispatch actions for the reducers to handle */
import TYPES from "./action-types";
import { API_ENDPOINT } from "./constants";
import { apiFetch } from '@wordpress/data';


const {
	CREATE,
	HYDRATE,
	INITIALIZE,
	IS_SAVING,
	SAVE_SETTINGS_ERROR,
	SAVE_SETTINGS_REQUEST,
	SAVE_SETTINGS_SUCCESS,
	UPDATE,
} = TYPES;

interface Settings {
	[key: string]: any;
}

interface Setting {
	[key: string]: any;
}

interface Action {
	type: string;
	settings?: Settings;
	setting?: Setting;
	payload?: any;
	error?: any;
}

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

export const updateSettings = settings => {
    return{
      type: UPDATE,
      settings,
    };
};

export const hydrate = settings => {
	return {
		type: HYDRATE,
		settings
	};
};

export const setSaving = (isSaving) => {
	return {
		type: IS_SAVING,
		isSaving
	};
};
