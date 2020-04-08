
/**
 * External dependencies
 */
import { store } from '@moderntribe/common/store';

/**
 * Internal dependencies
 */
import * as datetime from '@moderntribe/events/data/blocks/datetime';
import * as sharing from '@moderntribe/events/data/blocks/sharing';

export default () => [
	sharing.sagas,
	datetime.sagas,
].forEach( sagas => store.run( sagas ) );
