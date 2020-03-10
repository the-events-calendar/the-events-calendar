
/**
 * External dependencies
 */
import { store } from '@moderntribe/common/store';

/**
 * Internal dependencies
 */
import * as datetime from '@moderntribe/events/data/blocks/datetime';
import * as price from '@moderntribe/events/data/blocks/price';
import * as venue from '@moderntribe/events/data/blocks/venue';
import * as website from '@moderntribe/events/data/blocks/website';
import * as classic from '@moderntribe/events/data/blocks/classic';

export default () => [
	price.sagas,
	venue.sagas,
	website.sagas,
	classic.sagas,
	datetime.sagas,
].forEach( sagas => store.run( sagas ) );
