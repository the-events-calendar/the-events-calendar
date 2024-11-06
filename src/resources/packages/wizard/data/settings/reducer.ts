/* Receives dispatched actions and determines what happens to the state as a result. */
import TYPES from "./action-types";

const { INITIALIZE, CREATE, UPDATE, HYDRATE } = TYPES;

interface Setting {
  key: string;
  value: any;
}

interface State {
  settings: { [key: string]: any };  // This should be an object, not an array
}

const initialState: State = { settings: {} };  // Start with an empty object for settings

const reducer = (
  state = initialState,
  { settings, setting, type }: { settings?: { [key: string]: any }, setting?: Setting, type: string }
) => {
  switch (type) {
    case INITIALIZE:
      return { settings: settings || {} };  // Initialize with settings object
    case CREATE:
      return {
        ...state,
        settings: {
          ...state.settings,
          ...(setting && setting.key ? { [setting.key]: setting.value } : {}),  // Add the new setting
        },
      };
    case UPDATE:
      if (settings) {
        return {
          ...state,
          settings: {
            ...state.settings,
            ...settings,  // Spread the new settings to update them
          },
        };
      }
      return state;
    case HYDRATE:
      return { settings: settings || {} };
    default:
      return state;
  }
};

export default reducer;
