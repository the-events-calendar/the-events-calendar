
/**
 * External dependencies
 */
import { store } from '@moderntribe/common/store';

/**
 * Internal dependencies
 */
import * as datetime from '@moderntribe/events/data/blocks/datetime';
import * as website from '@moderntribe/events/data/blocks/website';
import * as classic from '@moderntribe/events/data/blocks/classic';

export default () => [
	website.sagas,
	classic.sagas,
	datetime.sagas,
].forEach( sagas => store.run( sagas ) );
