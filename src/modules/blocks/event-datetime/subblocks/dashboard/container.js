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
	defaultStartMoment,
	defaultEndMoment,
} from '@moderntribe/events/data/blocks/datetime/reducer';
import {
	actions as UIActions,
	selectors as UISelectors,
} from '@moderntribe/events/data/ui';
import { moment as momentUtil } from '@moderntribe/common/utils';
import { withStore, withSaveData, withBlockCloser } from '@moderntribe/common/hoc';
import EventDateTimeDashboard from './template';

/**
 * Module Code
 */

const onSelectDay = ( dispatchProps ) => ( { from, to } ) => {
	dispatchProps.setDateRange( { from, to } );
};

const onStartTimePickerBlur = ( dispatchProps ) => ( e ) => {
	let startTimeMoment = momentUtil.toMoment( e.target.value, momentUtil.TIME_FORMAT, false );
	if ( ! startTimeMoment.isValid() ) {
		startTimeMoment = defaultStartMoment;
	}
	const seconds = momentUtil.totalSeconds( startTimeMoment );
	dispatchProps.setStartTime( seconds );
};

const onStartTimePickerChange = ( dispatchProps ) => ( e ) => (
	dispatchProps.setStartTimeInput( e.target.value )
);

const onStartTimePickerClick = ( dispatchProps ) => ( value, onClose ) => {
	dispatchProps.setStartTime( value );
	onClose();
};

const onEndTimePickerBlur = ( dispatchProps ) => ( e ) => {
	let endTimeMoment = momentUtil.toMoment( e.target.value, momentUtil.TIME_FORMAT, false );
	if ( ! endTimeMoment.isValid() ) {
		endTimeMoment = defaultEndMoment;
	}
	const seconds = momentUtil.totalSeconds( endTimeMoment );
	dispatchProps.setEndTime( seconds );
};

const onEndTimePickerChange = ( dispatchProps ) => ( e ) => (
	dispatchProps.setEndTimeInput( e.target.value )
);

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
	startTimeInput: dateTimeSelectors.getStartTimeInput( state ),
	endTimeInput: dateTimeSelectors.getEndTimeInput( state ),
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
	onStartTimePickerBlur: onStartTimePickerBlur( dispatchProps ),
	onStartTimePickerChange: onStartTimePickerChange( dispatchProps ),
	onStartTimePickerClick: onStartTimePickerClick( dispatchProps ),
	onEndTimePickerBlur: onEndTimePickerBlur( dispatchProps ),
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
