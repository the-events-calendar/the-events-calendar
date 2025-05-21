/**
 * External dependencies
 */
import { store } from '@moderntribe/common/store';

/**
 * Internal dependencies
 */
import * as datetime from '@moderntribe/events/data/blocks/datetime';

export default () => [ datetime.sagas ].forEach( ( sagas ) => store.run( sagas ) );
