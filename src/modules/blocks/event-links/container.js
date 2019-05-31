/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose, bindActionCreators } from 'redux';

/**
 * Internal dependencies
 */
import EventLinks from './template';
import { actions, selectors } from '@moderntribe/events/data/blocks/sharing';
import { withStore, withSaveData } from '@moderntribe/common/hoc';

/**
 * Module Code
 */

const mapStateToProps = ( state ) => ( {
	iCalLabel: selectors.iCalLabelSelector( state ),
	hasiCal: selectors.hasIcalSelector( state ),
	googleCalendarLabel: selectors.googleCalendarLabelSelector( state ),
	hasGoogleCalendar: selectors.hasGooglecalendarLabel( state ),
} );

const mapDispatchToProps = ( dispatch ) => bindActionCreators( actions, dispatch );

export default compose(
	withStore(),
	connect(
		mapStateToProps,
		mapDispatchToProps,
	),
	withSaveData(),
)( EventLinks );
