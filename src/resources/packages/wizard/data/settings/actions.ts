/* Dispatch actions for the reducers to handle */
import TYPES from './action-types';
import { API_ENDPOINT } from './constants';
import { apiFetch } from '@wordpress/data';

const {
	CREATE,
	INITIALIZE,
	IS_SAVING,
	SAVE_SETTINGS_ERROR,
	SAVE_SETTINGS_REQUEST,
	SAVE_SETTINGS_SUCCESS,
	UPDATE,
	SET_VISITED_FIELDS,
	SKIP_TAB,
	COMPLETE_TAB,
} = TYPES;

interface Settings {
	[ key: string ]: any;
}

interface Setting {
	[ key: string ]: any;
}

interface Action {
	type: string;
	settings?: Settings;
	setting?: Setting;
	payload?: any;
	error?: any;
}

export function initializeSettings( settings ) {
	return {
		type: INITIALIZE,
		settings,
	};
}

export function createSetting( setting ) {
	return {
		type: CREATE,
		setting,
	};
}

export const updateSettings = ( settings ) => {
	return {
		type: UPDATE,
		settings,
	};
};

export const setSaving = ( isSaving ) => {
	return {
		type: IS_SAVING,
		isSaving,
	};
};

export const setVisitedField = ( visitedFieldId ) => {
	return {
		type: SET_VISITED_FIELDS,
		payload: visitedFieldId,
	};
};

export const skipTab = ( tabId ) => {
	return {
		type: SKIP_TAB,
		payload: tabId,
	};
};

export const completeTab = ( tabId ) => {
	return {
		type: COMPLETE_TAB,
		payload: tabId,
	};
};
