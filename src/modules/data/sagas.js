
/**
 * External dependencies
 */
import { store } from '@moderntribe/common/store';

/**
 * Internal dependencies
 */
import * as datetime from '@moderntribe/events/data/blocks/datetime';
import * as price from '@moderntribe/events/data/blocks/price';
import * as sharing from '@moderntribe/events/data/blocks/sharing';
import * as classic from '@moderntribe/events/data/blocks/classic';

export default () => [
	price.sagas,
	sharing.sagas,
	classic.sagas,
	datetime.sagas,
].forEach( sagas => store.run( sagas ) );
