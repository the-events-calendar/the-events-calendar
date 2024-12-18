import * as selectors from "./selectors";
import * as actions from "./actions";
import reducer from "./reducer";
import * as resolvers from "./resolvers";
import { controls } from "@wordpress/data-controls";

export { SETTINGS_STORE_KEY } from "./constants";
export const SETTINGS_STORE_CONFIG = {
  selectors,
  actions,
  reducer,
  resolvers,
  controls
};
