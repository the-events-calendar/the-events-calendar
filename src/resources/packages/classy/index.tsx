import { addFilter } from '@wordpress/hooks';
import renderFields from "./functions/renderFields";

// Hook on the Classy fields rendering logic to render the fields.
addFilter('tec.classy.render', 'tec.classy.events', renderFields);
