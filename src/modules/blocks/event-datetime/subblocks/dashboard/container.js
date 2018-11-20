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

const onSelectDay = ( stateProps, dispatchProps ) => ( { from, to } ) => {
	const { start, end } = stateProps;
	const { setDates } = dispatchProps;
	setDates( { start, end, from, to } );
};

const onStartTimePickerChange = ( stateProps, dispatchProps ) => ( e ) => {
	const seconds = time.toSeconds( e.target.value );
	dispatchProps.setStartTime( seconds );
};

const onStartTimePickerClick = ( stateProps, dispatchProps ) => ( value, onClose ) => {
	dispatchProps.setStartTime( value );
	onClose();
};

const onEndTimePickerChange = ( stateProps, dispatchProps ) => ( e ) => {
	const seconds = time.toSeconds( e.target.value );
	dispatchProps.setEndTime( seconds );
};

const onEndTimePickerClick = ( stateProps, dispatchProps ) => ( value, onClose ) => {
	dispatchProps.setEndTime( value );
	onClose();
};

const onMultiDayToggleChange = ( stateProps, dispatchProps ) => ( isMultiDay ) => {
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
	onSelectDay: onSelectDay( stateProps, dispatchProps ),
	onStartTimePickerChange: onStartTimePickerChange( stateProps, dispatchProps ),
	onStartTimePickerClick: onStartTimePickerClick( stateProps, dispatchProps ),
	onEndTimePickerChange: onEndTimePickerChange( stateProps, dispatchProps ),
	onEndTimePickerClick: onEndTimePickerClick( stateProps, dispatchProps ),
	onMultiDayToggleChange: onMultiDayToggleChange( stateProps, dispatchProps ),
} );

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps, mergeProps ),
	withSaveData(),
	withBlockCloser,
)( EventDateTimeDashboard );
