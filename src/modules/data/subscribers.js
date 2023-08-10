/**
 * Internal dependencies
 */
import * as organizers from '@moderntribe/events/data/blocks/organizers';
import { subscribe as blocksSubscribe } from '@moderntribe/events/data/blocks';

export default () => {
	organizers.subscribe();
	blocksSubscribe();
};
