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
import {
	selectors as priceSelectors,
	actions as priceActions,
} from '@moderntribe/events/data/blocks/price';
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
	// const { start, end } = stateProps;
	// const { setStartTime } = dispatchProps;
	// const [ hour, minute ] = e.target.value.split( ':' );

	// const startMoment = moment.toMoment( start );
	// const max = moment.toMoment( end ).clone().subtract( 1, 'minutes' );

	// const startMomentCopy = startMoment.clone();
	// startMomentCopy.set( 'hour', parseInt( hour, 10 ) );
	// startMomentCopy.set( 'minute', parseInt( minute, 10 ) );
	// startMomentCopy.set( 'second', 0 );

	// // if ( startMomentCopy.isAfter( max ) ) {
	// // 	return;
	// // }

	// const seconds = startMomentCopy.diff( startMoment.clone().startOf( 'day' ), 'seconds' );
	// setStartTime( { start, seconds } );
};

const onStartTimePickerClick = ( stateProps, dispatchProps ) => ( value, onClose ) => {
	// const { start, end } = stateProps;
	// const { setStartTime, setAllDay } = dispatchProps;

	// const isAllDay = value === 'all-day';

	// if ( ! isAllDay ) {
	// 	setStartTime( { start, seconds: value } );
	// }

	// setAllDay( { start, end, isAllDay } );
	dispatchProps.setStartTime( value );
	onClose();
};

const onEndTimePickerChange = ( stateProps, dispatchProps ) => ( e ) => {
	const seconds = time.toSeconds( e.target.value );
	dispatchProps.setEndTime( seconds );

	// const { start, end } = stateProps;
	// const { setEndTime } = dispatchProps;

	// const endMoment = moment.toMoment( end );
	// const min = moment.toMoment( start ).clone().add( 1, 'minutes' );

	// const endMomentCopy = endMoment.clone();
	// endMomentCopy.set( 'hour', parseInt( hour, 10 ) );
	// endMomentCopy.set( 'minute', parseInt( minute, 10 ) );
	// endMomentCopy.set( 'second', 0 );

	// const seconds = endMomentCopy.diff( endMoment.clone().startOf( 'day' ), 'seconds' );
	// setEndTime( { end, seconds } );
};

const onEndTimePickerClick = ( stateProps, dispatchProps ) => ( value, onClose ) => {
	// const { start, end } = stateProps;
	// const { setEndTime, setAllDay } = dispatchProps;

	// const isAllDay = value === 'all-day';

	// if ( ! isAllDay ) {
	// 	setEndTime( { end, seconds: value } );
	// }

	// setAllDay( { start, end, isAllDay } );
	dispatchProps.setEndTime( value );
	onClose();
};

const onMultiDayToggleChange = ( stateProps, dispatchProps ) => ( isMultiDay ) => {
	dispatchProps.setMultiDay( isMultiDay );
};

const mapStateToProps = ( state ) => ( {
	allDay: dateTimeSelectors.getAllDay( state ),
	end: dateTimeSelectors.getEnd( state ),
	isDashboardOpen: UISelectors.getDashboardDateTimeOpen( state ),
	multiDay: dateTimeSelectors.getMultiDay( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
	start: dateTimeSelectors.getStart( state ),
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
