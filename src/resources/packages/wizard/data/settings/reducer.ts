/* Receives dispatched actions and determines what happens to the state as a result. */
import React from "react";
import TYPES from "./action-types";

const { INITIALIZE, CREATE, UPDATE, DELETE, HYDRATE } = TYPES;

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
	{ settings: incomingSettings, setting, key, type, activeSetting }: { settings?: Setting[], setting?: Setting, key?: string, type: string, activeSetting?: number }
) => {
	switch (type) {
	case INITIALIZE:
		return { settings: incomingSettings };
	case CREATE:
		return { settings: [...state.settings, setting] };
	case UPDATE:
		return {
			settings: state.settings
				.filter(existing => setting && existing.key !== setting.key)
				.concat(setting ? [setting] : [])
		};
	case HYDRATE:
		return { settings: incomingSettings };
	default:
		return state;
	}
};

export default reducer;
