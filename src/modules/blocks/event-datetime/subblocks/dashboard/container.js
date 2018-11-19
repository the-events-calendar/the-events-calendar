/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose, bindActionCreators } from 'redux';

/**
 * Internal dependencies
 */
import {
	thunks as dateTimeThunks,
	selectors as dateTimeSelectors,
} from '@moderntribe/events/data/blocks/datetime';
import {
	actions as UIActions,
	selectors as UISelectors,
} from '@moderntribe/events/data/ui';
import { moment } from '@moderntribe/common/utils';
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
	const { start, end } = stateProps;
	const { setStartTime } = dispatchProps;
	const [ hour, minute ] = e.target.value.split( ':' );

	const startMoment = moment.toMoment( start );
	const max = moment.toMoment( end ).clone().subtract( 1, 'minutes' );

	const startMomentCopy = startMoment.clone();
	startMomentCopy.set( 'hour', parseInt( hour, 10 ) );
	startMomentCopy.set( 'minute', parseInt( minute, 10 ) );
	startMomentCopy.set( 'second', 0 );

	if ( startMomentCopy.isAfter( max ) ) {
		return;
	}

	const seconds = startMomentCopy.diff( startMoment.clone().startOf( 'day' ), 'seconds' );
	setStartTime( { start, seconds } );
};

const onStartTimePickerClick = ( stateProps, dispatchProps ) => ( value, onClose ) => {
	const { start, end } = stateProps;
	const { setStartTime, setAllDay } = dispatchProps;

	const isAllDay = value === 'all-day';

	if ( ! isAllDay ) {
		setStartTime( { start, seconds: value } );
	}

	setAllDay( { start, end, isAllDay } );
	onClose();
};

const onEndTimePickerChange = ( stateProps, dispatchProps ) => ( e ) => {
	const { start, end } = stateProps;
	const { setEndTime } = dispatchProps;
	const [ hour, minute ] = e.target.value.split( ':' );

	const endMoment = moment.toMoment( end );
	const min = moment.toMoment( start ).clone().add( 1, 'minutes' );

	const endMomentCopy = endMoment.clone();
	endMomentCopy.set( 'hour', parseInt( hour, 10 ) );
	endMomentCopy.set( 'minute', parseInt( minute, 10 ) );
	endMomentCopy.set( 'second', 0 );

	if ( endMomentCopy.isBefore( min ) ) {
		return;
	}

	const seconds = endMomentCopy.diff( endMoment.clone().startOf( 'day' ), 'seconds' );
	setEndTime( { end, seconds } );
};

const onEndTimePickerClick = ( stateProps, dispatchProps ) => ( value, onClose ) => {
	const { start, end } = stateProps;
	const { setEndTime, setAllDay } = dispatchProps;

	const isAllDay = value === 'all-day';

	if ( ! isAllDay ) {
		setEndTime( { end, seconds: value } );
	}

	setAllDay( { start, end, isAllDay } );
	onClose();
};

const onMultiDayToggleChange = ( stateProps, dispatchProps ) => ( isMultiDay ) => {
	const { start, end } = stateProps;
	dispatchProps.setMultiDay( { start, end, isMultiDay } );
};

const mapStateToProps = ( state ) => ( {
	allDay: dateTimeSelectors.getAllDay( state ),
	isDashboardOpen: UISelectors.getDashboardDateTimeOpen( state ),
	multiDay: dateTimeSelectors.getMultiDay( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
	visibleMonth: UISelectors.getVisibleMonth( state ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	...bindActionCreators( UIActions, dispatch ),
	...bindActionCreators( dateTimeThunks, dispatch ),
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => ( {
	...ownProps,
	...stateProps,
	...dispatchProps,
	onEndTimePickerChange: onEndTimePickerChange( stateProps, dispatchProps ),
	onEndTimePickerClick: onEndTimePickerClick( stateProps, dispatchProps ),
	onMultiDayToggleChange: onMultiDayToggleChange( stateProps, dispatchProps ),
	onSelectDay: onSelectDay( stateProps, dispatchProps ),
	onStartTimePickerChange: onStartTimePickerChange( stateProps, dispatchProps ),
	onStartTimePickerClick: onStartTimePickerClick( stateProps, dispatchProps ),
} );

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps, mergeProps ),
	withSaveData(),
	withBlockCloser,
)( EventDateTimeDashboard );
