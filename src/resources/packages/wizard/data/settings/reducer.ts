/* Receives dispatched actions and determines what happens to the state as a result. */
import React from "react";
import TYPES from "./action-types";

const { INITIALIZE, CREATE, UPDATE, HYDRATE } = TYPES;

interface Setting {
  	key: string;
	value: any;
}

interface State {
  settings: Setting[];
}

const initialState: State = { settings: [] };

const reducer = (
	state = initialState,
	{ settings: incomingSettings, setting, type }: { settings?: Setting[], setting?: Setting, type: string }
) => {
	switch (type) {
	case INITIALIZE:
		return { settings: incomingSettings };
	case CREATE:
		return { settings: [...state.settings, setting] };
	case UPDATE:
		return {
			...state,
			settings: {
				...state.settings,
				[setting.key]: setting.value,  // Update the specific setting key with its new value
			}
		};
	case HYDRATE:
		return { settings: incomingSettings };
	default:
		return state;
	}
};

export default reducer;
