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
import { moment } from '@moderntribe/common/utils';
import { withStore, withSaveData, withBlockCloser } from '@moderntribe/common/hoc';
import EventDateTime from './template';

/**
 * Module Code
 */

const onClose = ( dispatchProps ) => ( e ) => {
	const { setDateInputVisibility, closeDashboardDateTime } = dispatchProps;
	setDateInputVisibility( false );
	closeDashboardDateTime();
};

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

const onTimeZoneVisibilityChange = ( dispatch ) => ( checked ) => (
	dispatch( dateTimeActions.setTimeZoneVisibility( checked ) )
);

const onDateTimeLabelClick = ( dispatch ) => () => {
	dispatch( dateTimeActions.setDateInputVisibility( true ) );
	dispatch( UIActions.openDashboardDateTime() );
};

const mapStateToProps = ( state ) => ( {
	isDashboardOpen: UISelectors.getDashboardDateTimeOpen( state ),
	isOpen: UISelectors.getDashboardDateTimeOpen( state ),
	visibleMonth: UISelectors.getVisibleMonth( state ),
	isEditable: dateTimeSelectors.isEditable( state ),
	start: dateTimeSelectors.getStart( state ),
	end: dateTimeSelectors.getEnd( state ),
	naturalLanguageLabel: dateTimeSelectors.getNaturalLanguageLabel( state ),
	multiDay: dateTimeSelectors.getMultiDay( state ),
	allDay: dateTimeSelectors.getAllDay( state ),
	separatorDate: dateTimeSelectors.getDateSeparator( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
	showTimeZone: dateTimeSelectors.getTimeZoneVisibility( state ),
	timeZone: dateTimeSelectors.getTimeZone( state ),
	timeZoneLabel: dateTimeSelectors.getTimeZoneLabel( state ),
	showDateInput: dateTimeSelectors.getDateInputVisibility( state ),
	cost: priceSelectors.getPrice( state ),
	currencySymbol: priceSelectors.getSymbol( state ),
	currencyPosition: priceSelectors.getPosition( state ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	...bindActionCreators( dateTimeActions, dispatch ),
	...bindActionCreators( dateTimeThunks, dispatch ),
	...bindActionCreators( UIActions, dispatch ),
	...bindActionCreators( priceActions, dispatch ),
	setInitialState: ( props ) => {
		dispatch( priceActions.setInitialState( props ) );
		dispatch( dateTimeThunks.setInitialState( props ) );
		dispatch( UIActions.setInitialState( props ) );
	},
	onTimeZoneVisibilityChange: onTimeZoneVisibilityChange( dispatch ),
	onDateTimeLabelClick: onDateTimeLabelClick( dispatch ),
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => ( {
	...ownProps,
	...stateProps,
	...dispatchProps,
	onClose: onClose( dispatchProps ),
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
)( EventDateTime );
