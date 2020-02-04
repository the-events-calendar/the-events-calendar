
/**
 * External dependencies
 */
import { store } from '@moderntribe/common/store';

/**
 * Internal dependencies
 */
import * as datetime from '@moderntribe/events/data/blocks/datetime';
import * as price from '@moderntribe/events/data/blocks/price';

export default () => [
	price.sagas,
	datetime.sagas,
].forEach( sagas => store.run( sagas ) );
