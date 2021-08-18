/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose } from 'redux';

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
import { moment as momentUtil } from '@moderntribe/common/utils';
import { withStore } from '@moderntribe/common/hoc';
import EventDateTimeDashboard from './template';

/**
 * Module Code
 */

const mapStateToProps = ( state ) => ( {
	allDay: dateTimeSelectors.getAllDay( state ),
	start: dateTimeSelectors.getStart( state ),
	end: dateTimeSelectors.getEnd( state ),
	startTimeInput: dateTimeSelectors.getStartTimeInput( state ),
	endTimeInput: dateTimeSelectors.getEndTimeInput( state ),
	multiDay: dateTimeSelectors.getMultiDay( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
} );

const mapDispatchToProps = ( dispatch, ownProps ) => {
	const meta = { setAttributes: ownProps.setAttributes };

	return {
		onSelectDay: ( { from, to } ) => (
			dispatch( dateTimeActions.setDateRange( { from, to }, meta ) )
		),
		onStartTimePickerBlur: ( e ) => {
			let startTimeMoment = momentUtil.toMoment( e.target.value, momentUtil.TIME_FORMAT, false );
			if ( ! startTimeMoment.isValid() ) {
				startTimeMoment = defaultStartMoment;
			}
			const seconds = momentUtil.totalSeconds( startTimeMoment );
			dispatch( dateTimeActions.setStartTime( seconds, meta ) );
		},
		onStartTimePickerChange: ( e ) => (
			dispatch( dateTimeActions.setStartTimeInput( e.target.value ) )
		),
		onStartTimePickerClick: ( value, onClose ) => {
			dispatch( dateTimeActions.setStartTime( value, meta ) );
			onClose();
		},
		onEndTimePickerBlur: ( e ) => {
			let endTimeMoment = momentUtil.toMoment( e.target.value, momentUtil.TIME_FORMAT, false );
			if ( ! endTimeMoment.isValid() ) {
				endTimeMoment = defaultEndMoment;
			}
			const seconds = momentUtil.totalSeconds( endTimeMoment );
			dispatch( dateTimeActions.setEndTime( seconds, meta ) );
		},
		onEndTimePickerChange: ( e ) => (
			dispatch( dateTimeActions.setEndTimeInput( e.target.value ) )
		),
		onEndTimePickerClick: ( value, onClose ) => {
			dispatch( dateTimeActions.setEndTime( value, meta ) );
			onClose();
		},
		onMultiDayToggleChange: ( isMultiDay ) => (
			dispatch( dateTimeActions.setMultiDay( isMultiDay, meta ) )
		),
	};
};

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps ),
)( EventDateTimeDashboard );
