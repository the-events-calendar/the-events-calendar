/* Receives dispatched actions and determines what happens to the state as a result. */
import TYPES from "./action-types";

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

interface Setting {
	key: string;
	value: any;
}

interface State {
	settings: { [key: string]: any };// This should be an object, not an array
	isSaving: boolean;
	error: any;
}

const initialState = {
	settings: {},
	isSaving: false,
	error: null,
};

const reducer = (
	state = initialState,
	{ settings, setting, type, payload, error }: { settings?: { [key: string]: any }, setting?: Setting, type: string, payload?: any, error?: any }
) => {
switch (type) {
	case INITIALIZE:
		return { settings: settings || {} };

	case CREATE:
		return {
			...state,
			settings: {
			...state.settings,
			...(setting && setting.key ? { [setting.key]: setting.value } : {}),
			},
		};

	case UPDATE:
		if (settings) {
			return {
			...state,
			isSaving: true,// Set isSaving to true when an update starts
			settings: {
				...state.settings,
				...settings,// Spread the new settings to update them
			},
			};
		}
		return state;

	case HYDRATE:
		return { settings: settings || {} };

	case SAVE_SETTINGS_REQUEST:
		return { ...state, isSaving: true, error: null };

	case SAVE_SETTINGS_SUCCESS:
		return {
			...state,
			settings: { ...state.settings, ...payload }, // Merge successful update into settings
			isSaving: false
		};


	case SAVE_SETTINGS_ERROR:
		return { ...state, isSaving: false, error };

	case IS_SAVING:
		return { ...state, isSaving: payload };

	default:
		return state;
}
};

export default reducer;
