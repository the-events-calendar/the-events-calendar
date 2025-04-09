import { registerStore } from '@wordpress/data';
import { SETTINGS_STORE_KEY, SETTINGS_STORE_CONFIG } from './settings';

registerStore( SETTINGS_STORE_KEY, SETTINGS_STORE_CONFIG );

import { MODAL_STORE_KEY, MODAL_STORE_CONFIG } from './modal';

registerStore( MODAL_STORE_KEY, MODAL_STORE_CONFIG );

export { SETTINGS_STORE_KEY, MODAL_STORE_KEY };
