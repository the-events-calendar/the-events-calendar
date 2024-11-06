import {
	STORE_KEY as SETTINGS_STORE_KEY,
	STORE_CONFIG as productsConfig
  } from "./settings";
  import { registerStore } from "@wordpress/data";

  registerStore(SETTINGS_STORE_KEY, settingsConfig);

  export { SETTINGS_STORE_KEY };
