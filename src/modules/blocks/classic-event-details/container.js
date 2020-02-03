/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose, bindActionCreators } from 'redux';

/**
 * Internal dependencies
 */
import { globals } from '@moderntribe/common/utils';
import {
	actions as dateTimeActions,
	thunks as dateTimeThunks,
	selectors as dateTimeSelectors,
} from '@moderntribe/events/data/blocks/datetime';
import { DEFAULT_STATE as DATETIME_DEFAULT_STATE } from '@moderntribe/events/data/blocks/datetime/reducer';
import {
	actions as priceActions,
	selectors as priceSelectors,
} from '@moderntribe/events/data/blocks/price';
import {
	actions as websiteActions,
	selectors as websiteSelectors,
} from '@moderntribe/events/data/blocks/website';
import {
	actions as classicActions,
	selectors as classicSelectors,
} from '@moderntribe/events/data/blocks/classic';

import {
	actions as organizersActions,
	selectors as organizerSelectors,
} from '@moderntribe/events/data/blocks/organizers';
import { actions as UIActions } from '@moderntribe/events/data/ui';
import { withStore, withSaveData } from '@moderntribe/common/hoc';
import ClassicEventDetails from './template';
import dateTimeBlock from '@moderntribe/events/blocks/event-datetime';

/**
 * Module Code
 */

const mapStateToProps = ( state ) => ( {
	start: dateTimeSelectors.getStart( state ),
	end: dateTimeSelectors.getEnd( state ),
	multiDay: dateTimeSelectors.getMultiDay( state ),
	allDay: dateTimeSelectors.getAllDay( state ),
	separatorDate: dateTimeSelectors.getDateSeparator( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
	timezone: dateTimeSelectors.getTimeZone( state ),
	cost: priceSelectors.getPrice( state ),
	currencyPosition: priceSelectors.getPosition( state ),
	currencySymbol: priceSelectors.getSymbol( state ),
	url: websiteSelectors.getUrl( state ),
	detailsTitle: classicSelectors.detailsTitleSelector( state ),
	organizerTitle: classicSelectors.organizerTitleSelector( state ),
	organizers: organizerSelectors.getOrganizersInClassic( state ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	...bindActionCreators( dateTimeActions, dispatch ),
	...bindActionCreators( dateTimeThunks, dispatch ),
	...bindActionCreators( UIActions, dispatch ),
	...bindActionCreators( priceActions, dispatch ),
	...bindActionCreators( websiteActions, dispatch ),
	...bindActionCreators( classicActions, dispatch ),
	dispatch,
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => {
	const { dispatch, ...restDispatchProps } = dispatchProps;

	return {
		...ownProps,
		...stateProps,
		...restDispatchProps,
		setInitialState: ( props ) => {
			/**
			 * @todo: this method causes many problems. need to remove and hydrate initial state properly.
			 */
			const { get } = props;

			dispatch( classicActions.setInitialState( props ) );
			dispatch( organizersActions.setOrganizersInClassic( get( 'organizers', [] ) ) );

			// if current state start is not the same as default state, do not update
			// otherwise, update start state if attribute value is not the same as default state.
			const start = get( 'start', DATETIME_DEFAULT_STATE.start );
			if (
				stateProps.start === DATETIME_DEFAULT_STATE.start
					&& start !== DATETIME_DEFAULT_STATE.start
			) {
				dispatch( dateTimeActions.setStartDateTime( start ) );
			}

			// if current state end is not the same as default state, do not update
			// otherwise, update end state if attribute value is not the same as default state.
			const end = get( 'end', DATETIME_DEFAULT_STATE.end );
			if (
				stateProps.end === DATETIME_DEFAULT_STATE.end
					&& end !== DATETIME_DEFAULT_STATE.end
			) {
				dispatch( dateTimeActions.setStartDateTime( end ) );
			}

			// if current state allDay is not the same as default state, do not update
			// otherwise, update allDay state if attribute value is not the same as default state.
			const allDay = get( 'allDay', DATETIME_DEFAULT_STATE.allDay );
			if (
				stateProps.allDay === DATETIME_DEFAULT_STATE.allDay
					&& allDay !== DATETIME_DEFAULT_STATE.allDay
			) {
				dispatch( dateTimeActions.setAllDay( allDay ) );
			}
		},
		toggleDashboardDateTime: () => {
			// there may be a better way to do this, but for now there's no way to access context
			// outside of the provider.
			const blocks = globals.wpCoreEditor.getBlocks();

			const filteredBlocks = blocks.filter( ( block ) => {
				return block.name === `tribe/${ dateTimeBlock.id }`;
			} );

			if ( ! filteredBlocks.length ) {
				return;
			}

			const dateTimeButton = document
				.querySelector( `[data-block="${ filteredBlocks[0].clientId }"]` )
				.getElementsByClassName( 'tribe-editor__subtitle__headline-button' )[0];

			if ( ! dateTimeButton ) {
				return;
			}

			// simulate click event on date time button to open dashboard of first date time block
			dateTimeButton.click();
		},
	};
};

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps, mergeProps ),
	withSaveData(),
)( ClassicEventDetails );
