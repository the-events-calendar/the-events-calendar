/* Receives dispatched actions and determines what happens to the state as a result. */
import TYPES from './action-types';

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

interface Setting {
	key: string;
	value: any;
}

interface State {
	settings: { [ key: string ]: any }; // This should be an object, not an array
	isSaving: boolean;
	error: any;
	visitedFields: [];
	completedTabs: [];
	skippedTabs: [];
}

const initialState = {
	settings: {},
	isSaving: false,
	error: null,
	visitedFields: [],
	completedTabs: [],
	skippedTabs: [],
};

const reducer = (
	state = initialState,
	{
		settings,
		setting,
		type,
		payload,
		error,
	}: {
		settings?: { [ key: string ]: any };
		setting?: Setting;
		type: string;
		payload?: any;
		error?: any;
	}
) => {
	switch ( type ) {
		case INITIALIZE:
			if ( state.settings && Object.keys( state.settings ).length > 0 ) {
				return state; // Prevent overwriting if already initialized
			}
			const { completedTabs = [], skippedTabs = [], visitedFields = [], ...otherSettings } = settings || {};
			return {
				...state,
				settings: otherSettings, // Populate settings without completedTabs and skippedTabs
				completedTabs, // Hydrate completedTabs into its separate property
				skippedTabs, // Hydrate skippedTabs into its separate property
				visitedFields, // Hydrate visitedFields into its separate property
			};

		case CREATE:
			return {
				...state,
				settings: {
					...state.settings,
					...( setting && setting.key ? { [ setting.key ]: setting.value } : {} ),
				},
			};

		case UPDATE:
			if ( settings ) {
				return {
					...state,
					settings: {
						...state.settings,
						...settings,
					},
				};
			}
			return state;

		case SAVE_SETTINGS_REQUEST:
			return { ...state, isSaving: true, error: null };

		case SAVE_SETTINGS_SUCCESS:
			return {
				...state,
				settings: { ...state.settings, ...payload },
				isSaving: false,
			};

		case SAVE_SETTINGS_ERROR:
			return { ...state, isSaving: false, error };

		case IS_SAVING:
			return {
				...state,
				isSaving: payload || false,
			};

		case SET_VISITED_FIELDS:
			return {
				...state,
				visitedFields: Array.from( new Set( [ ...( state.visitedFields || [] ), payload ] ) ),
			};

		case COMPLETE_TAB:
			return {
				...state,
				completedTabs: Array.from( new Set( [ ...( state.completedTabs || [] ), payload ] ) ),
				skippedTabs: state.skippedTabs.filter( ( tabId ) => tabId !== payload ),
			};

		case SKIP_TAB:
			// Only add to skippedTabs if not already completed
			if ( state.completedTabs.includes( payload ) ) {
				return state;
			}
			return {
				...state,
				skippedTabs: Array.from( new Set( [ ...( state.skippedTabs || [] ), payload ] ) ),
			};

		default:
			return state;
	}
};

export default reducer;
