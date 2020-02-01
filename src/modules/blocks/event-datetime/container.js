/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import {
	thunks as dateTimeThunks,
} from '@moderntribe/events/data/blocks/datetime';
import {
	actions as UIActions,
} from '@moderntribe/events/data/ui';
import { withStore, withBlockCloser } from '@moderntribe/common/hoc';
import EventDateTime from './template';

/**
 * Module Code
 */

const mapDispatchToProps = ( dispatch ) => ( {
	setInitialState: ( props ) => {
		// dispatch( priceActions.setInitialState( props ) );
		dispatch( dateTimeThunks.setInitialState( props ) );
		dispatch( UIActions.setInitialState( props ) );
	},
} );

export default compose(
	withStore(),
	connect( null, mapDispatchToProps ),
	withBlockCloser,
)( EventDateTime );
