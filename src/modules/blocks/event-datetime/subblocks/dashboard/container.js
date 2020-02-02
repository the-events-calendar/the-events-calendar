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
	selectors as dateTimeSelectors,
} from '@moderntribe/events/data/blocks/datetime';
import {
	defaultStartMoment,
	defaultEndMoment,
} from '@moderntribe/events/data/blocks/datetime/reducer';
import {
	selectors as UISelectors,
} from '@moderntribe/events/data/ui';
import { moment as momentUtil } from '@moderntribe/common/utils';
import { withStore, withSaveData } from '@moderntribe/common/hoc';
import EventDateTimeDashboard from './template';

/**
 * Module Code
 */

const onSelectDay = ( dispatchProps, ownProps ) => ( { from, to } ) => {
	dispatchProps.setDateRange( { from, to }, { setAttributes: ownProps.setAttributes } );
};

const onStartTimePickerBlur = ( dispatchProps, ownProps ) => ( e ) => {
	let startTimeMoment = momentUtil.toMoment( e.target.value, momentUtil.TIME_FORMAT, false );
	if ( ! startTimeMoment.isValid() ) {
		startTimeMoment = defaultStartMoment;
	}
	const seconds = momentUtil.totalSeconds( startTimeMoment );
	dispatchProps.setStartTime( seconds, { setAttributes: ownProps.setAttributes } );
};

const onStartTimePickerChange = ( dispatchProps ) => ( e ) => (
	dispatchProps.setStartTimeInput( e.target.value )
);

const onStartTimePickerClick = ( dispatchProps, ownProps ) => ( value, onClose ) => {
	dispatchProps.setStartTime( value, { setAttributes: ownProps.setAttributes } );
	onClose();
};

const onEndTimePickerBlur = ( dispatchProps, ownProps ) => ( e ) => {
	let endTimeMoment = momentUtil.toMoment( e.target.value, momentUtil.TIME_FORMAT, false );
	if ( ! endTimeMoment.isValid() ) {
		endTimeMoment = defaultEndMoment;
	}
	const seconds = momentUtil.totalSeconds( endTimeMoment );
	dispatchProps.setEndTime( seconds, { setAttributes: ownProps.setAttributes } );
};

const onEndTimePickerChange = ( dispatchProps ) => ( e ) => (
	dispatchProps.setEndTimeInput( e.target.value )
);

const onEndTimePickerClick = ( dispatchProps, ownProps ) => ( value, onClose ) => {
	dispatchProps.setEndTime( value, { setAttributes: ownProps.setAttributes } );
	onClose();
};

const onMultiDayToggleChange = ( dispatchProps, ownProps ) => ( isMultiDay ) => {
	dispatchProps.setMultiDay( isMultiDay, { setAttributes: ownProps.setAttributes } );
};

const mapStateToProps = ( state ) => ( {
	allDay: dateTimeSelectors.getAllDay( state ),
	start: dateTimeSelectors.getStart( state ),
	end: dateTimeSelectors.getEnd( state ),
	startTimeInput: dateTimeSelectors.getStartTimeInput( state ),
	endTimeInput: dateTimeSelectors.getEndTimeInput( state ),
	multiDay: dateTimeSelectors.getMultiDay( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
	visibleMonth: UISelectors.getVisibleMonth( state ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	...bindActionCreators( dateTimeActions, dispatch ),
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => ( {
	...ownProps,
	...stateProps,
	...dispatchProps,
	onSelectDay: onSelectDay( dispatchProps, ownProps ),
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
)( EventDateTimeDashboard );
