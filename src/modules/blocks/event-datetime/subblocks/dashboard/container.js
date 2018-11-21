/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose, bindActionCreators } from 'redux';

/**
 * Internal dependencies
 */
import {
	actions as dateTimeActions,
	thunks as dateTimeThunks,
	selectors as dateTimeSelectors,
} from '@moderntribe/events/data/blocks/datetime';
import {
	actions as UIActions,
	selectors as UISelectors,
} from '@moderntribe/events/data/ui';
import { time } from '@moderntribe/common/utils';
import { withStore, withSaveData, withBlockCloser } from '@moderntribe/common/hoc';
import EventDateTimeDashboard from './template';

/**
 * Module Code
 */

const onSelectDay = ( dispatchProps ) => ( { from, to } ) => {
	dispatchProps.setDateRange( { from, to } );
};

const onStartTimePickerChange = ( dispatchProps ) => ( e ) => {
	const seconds = time.toSeconds( e.target.value, time.TIME_FORMAT_HH_MM );
	dispatchProps.setStartTime( seconds );
};

const onStartTimePickerClick = ( dispatchProps ) => ( value, onClose ) => {
	dispatchProps.setStartTime( value );
	onClose();
};

const onEndTimePickerChange = ( dispatchProps ) => ( e ) => {
	const seconds = time.toSeconds( e.target.value, time.TIME_FORMAT_HH_MM );
	dispatchProps.setEndTime( seconds );
};

const onEndTimePickerClick = ( dispatchProps ) => ( value, onClose ) => {
	dispatchProps.setEndTime( value );
	onClose();
};

const onMultiDayToggleChange = ( dispatchProps ) => ( isMultiDay ) => {
	dispatchProps.setMultiDay( isMultiDay );
};

const mapStateToProps = ( state ) => ( {
	allDay: dateTimeSelectors.getAllDay( state ),
	start: dateTimeSelectors.getStart( state ),
	end: dateTimeSelectors.getEnd( state ),
	isDashboardOpen: UISelectors.getDashboardDateTimeOpen( state ),
	multiDay: dateTimeSelectors.getMultiDay( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
	visibleMonth: UISelectors.getVisibleMonth( state ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	...bindActionCreators( dateTimeThunks, dispatch ),
	...bindActionCreators( dateTimeActions, dispatch ),
	...bindActionCreators( UIActions, dispatch ),

	setInitialState: ( props ) => {
		dispatch( dateTimeThunks.setInitialState( props ) );
		dispatch( UIActions.setInitialState( props ) );
	},
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => ( {
	...ownProps,
	...stateProps,
	...dispatchProps,
	onSelectDay: onSelectDay( dispatchProps ),
	onStartTimePickerChange: onStartTimePickerChange( dispatchProps ),
	onStartTimePickerClick: onStartTimePickerClick( dispatchProps ),
	onEndTimePickerChange: onEndTimePickerChange( dispatchProps ),
	onEndTimePickerClick: onEndTimePickerClick( dispatchProps ),
	onMultiDayToggleChange: onMultiDayToggleChange( dispatchProps ),
} );

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps, mergeProps ),
	withSaveData(),
	withBlockCloser,
)( EventDateTimeDashboard );
