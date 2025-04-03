/**
 * Internal dependencies
 */
import * as organizers from '@moderntribe/events/data/blocks/organizers';
import * as venues from '@moderntribe/events/data/blocks/venue';
import { subscribe as blocksSubscribe } from '@moderntribe/events/data/blocks';

export default () => {
	organizers.subscribe();
	venues.subscribe();
	blocksSubscribe();
};
