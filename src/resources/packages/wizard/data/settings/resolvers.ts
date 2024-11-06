/* linked to selectors, resolvers allow for automatically resolving data for the initial slice of state the selector is retrieving for */

import { apiFetch, dispatch, select } from "@wordpress/data-controls";
import { hydrate } from "./actions";

const resolvers = {
	  "tec/v1/settings": async (select) => {
	const settings = await apiFetch({ path: "tec/v1/settings" });
	dispatch("the-events-calendar/settings", hydrate(settings));
  }
};

export default resolvers;
