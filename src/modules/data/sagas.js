
/**
 * External dependencies
 */
import { store } from '@moderntribe/common/store';

/**
 * Internal dependencies
 */
import * as datetime from '@moderntribe/events/data/blocks/datetime';
import * as classic from '@moderntribe/events/data/blocks/classic';

export default () => [
	classic.sagas,
	datetime.sagas,
].forEach( sagas => store.run( sagas ) );
